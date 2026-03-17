<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'availability'=>'required|in:available,unavailable',
            'image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

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
            'availability'=>'sometimes|in:available,unavailable',
            'image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }

            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem->update($data);
        return $menuItem;
    }

    public function destroy(MenuItem $menuItem) {
        if ($menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
