<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    private function authenticatedUser(): User
    {
        /** @var User $user */
        $user = auth('api')->user();

        return $user;
    }

    private function forbidUnless(string ...$roles): ?JsonResponse
    {
        if (! in_array($this->authenticatedUser()->role, $roles, true)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return null;
    }

    public function placeOrder(Request $request){
        $data = $request->validate([
            'items'=>'required|array',
            'items.*.menu_item_id'=>'required|exists:menu_items,id',
            'items.*.quantity'=>'required|integer|min:1',
        ]);

        $total = 0;
        foreach($data['items'] as $i){
            $menuItem = MenuItem::find($i['menu_item_id']);
            if (! $menuItem || $menuItem->availability !== 'available') {
                return response()->json(['error' => 'One or more menu items are unavailable.'], 422);
            }

            $total += $menuItem->price * $i['quantity'];
        }

        $order = Order::create([
            'user_id'=>$this->authenticatedUser()->id,
            'status'=>'pending',
            'total_price'=>$total
        ]);

        foreach($data['items'] as $i){
            $menuItem = MenuItem::find($i['menu_item_id']);
            OrderItem::create([
                'order_id'=>$order->id,
                'menu_item_id'=>$menuItem->id,
                'quantity'=>$i['quantity'],
                'price'=>$menuItem->price
            ]);
        }

        return response()->json($order->load('orderItems.menuItem'), 201);
    }

    public function updateStatus(Request $request, Order $order){
        $data = $request->validate([
            'status'=>'required|in:pending,preparing,completed'
        ]);

        $order->update(['status'=>$data['status']]);
        return $order->load('orderItems.menuItem', 'user');
    }

    public function index(){
        $user = $this->authenticatedUser();

        if (in_array($user->role, ['admin', 'staff'], true)) {
            return Order::with('orderItems.menuItem', 'user')->latest()->get();
        }

        return Order::where('user_id', $user->id)
            ->with('orderItems.menuItem', 'user')
            ->latest()
            ->get();
    }
}