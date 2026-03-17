<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    private function authenticatedUser(): User
    {
        /** @var User $user */
        $user = auth('api')->user();

        return $user;
    }

    private function ensureAdmin(): ?JsonResponse
    {
        if ($this->authenticatedUser()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return null;
    }

    public function index() {
        return MenuItem::query()->orderBy('name')->get();
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name'=>'required|string',
            'description'=>'nullable|string',
            'price'=>'required|numeric',
            'availability'=>'required|in:available,unavailable'
        ]);

        return MenuItem::create($data);
    }

    public function show(MenuItem $menuItem) {
        return $menuItem;
    }

    public function update(Request $request, MenuItem $menuItem) {
        $data = $request->validate([
            'name'=>'sometimes|string',
            'description'=>'nullable|string',
            'price'=>'sometimes|numeric',
            'availability'=>'sometimes|in:available,unavailable'
        ]);

        $menuItem->update($data);
        return $menuItem;
    }

    public function destroy(MenuItem $menuItem) {
        $menuItem->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
