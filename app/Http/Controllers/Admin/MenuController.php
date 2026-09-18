<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    /**
     * Menu tree for one location (including inactive items).
     */
    public function index(Request $request): JsonResponse
    {
        $location = $request->validate(['location' => ['required', Rule::in(array_keys(Menu::LOCATIONS))]])['location'];

        return response()->json([
            'locations' => Menu::LOCATIONS,
            'menus' => Menu::query()
                ->where('location', $location)
                ->whereNull('parent_id')
                ->ordered()
                ->with('children')
                ->get(),
        ]);
    }

    /**
     * Create a menu item at the end of its siblings.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) Menu::query()
            ->where('location', $data['location'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order') + 1;

        return response()->json(Menu::query()->create($data), 201);
    }

    /**
     * Update a menu item.
     */
    public function update(StoreMenuRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['parent_id']) && $menu->children()->exists()) {
            throw ValidationException::withMessages(['parent_id' => 'যে মেনুর সাব-মেনু আছে তাকে অন্য মেনুর নিচে রাখা যাবে না।']);
        }

        $menu->update(collect($data)->except('sort_order')->all());

        return response()->json($menu);
    }

    /**
     * Delete a menu item and its children.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Persist a new ordering for sibling items.
     */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:menus,id'],
        ])['ids'];

        foreach (array_values($ids) as $position => $id) {
            Menu::query()->whereKey($id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
