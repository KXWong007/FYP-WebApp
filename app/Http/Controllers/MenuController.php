<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MenuExport;
use PDF;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::query();

        // Apply category filter if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Apply search filter if provided
        if ($request->has('search')) {
            $query->where('dishName', 'like', '%' . $request->search . '%');
        }

        $menus = $query->get();
        return view('menu', compact('menus'));
    }

    public function list()
    {
        $menus = Menu::all();
        return response()->json(['data' => $menus]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dishName' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability' => 'required|boolean'
        ]);

        // Generate unique dishId
        $dishId = 'DISH' . str_pad(Menu::count() + 1, 4, '0', STR_PAD_LEFT);

        // Handle image upload
        $imagePath = $request->file('image')->store('menu-images', 'public');

        $menu = Menu::create([
            'dishId' => $dishId,
            'dishName' => $request->dishName,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'cuisine' => $request->cuisine,
            'availableArea' => $request->availableArea,
            'price' => $request->price,
            'availableTime' => $request->availableTime,
            'availability' => $request->availability,
            'description' => $request->description,
            'image' => $imagePath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => $menu
        ]);
    }

    public function update(Request $request, $dishId)
    {
        try {
            $menu = Menu::where('dishId', $dishId)->firstOrFail();
            
            $validatedData = $request->validate([
                'dishName' => 'required',
                'category' => 'required',
                'subcategory' => 'required',
                'price' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'availableArea' => 'required|array',
                'availableTime' => 'required',
                'description' => 'required',
                // Add other validation rules as needed
            ]);

            // Convert availableArea array to string for storage
            if (is_array($request->availableArea)) {
                $validatedData['availableArea'] = implode(',', $request->availableArea);
            }

            // Handle image upload if a new image is provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                    Storage::disk('public')->delete($menu->image);
                }
                
                // Store new image
                $imagePath = $request->file('image')->store('menu-images', 'public');
                $validatedData['image'] = $imagePath;
            }

            $menu->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error updating menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($dishId)
    {
        try {
            // Add logging to debug
            \Log::info('Attempting to delete menu item with ID: ' . $dishId);
            
            $menu = Menu::where('dishId', $dishId)->firstOrFail();
            
            // Delete the image file if it exists
            if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
            }
            
            $menu->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting menu item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error deleting menu item: ' . $e->getMessage()
            ], 500);
        }
    }


    public function edit($dishId)
    {
        try {
            $menu = Menu::where('dishId', $dishId)->firstOrFail();
            
            // Convert availableArea to array if it's a string
            if (is_string($menu->availableArea)) {
                $menu->availableArea = array_map('trim', explode(',', $menu->availableArea));
            }
            
            return response()->json([
                'success' => true,
                'menu' => $menu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error fetching menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkDishId($dishId)
    {
        $exists = Menu::where('dishId', $dishId)->exists();
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This Dish ID is already taken' : 'Dish ID is available'
        ]);
    }

    public function customerMenu()
    {
        $menus = Menu::select('dishId', 'dishName', 'category', 'subcategory', 'cuisine', 'availability', 'price', 'image', 'description', 'availableTime', 'availableArea')
            ->get()
            ->map(function ($menu) {
                if ($menu->image) {
                    // Use asset() helper with your production URL
                    $menu->image = asset('storage/' . $menu->image);
                }
                return $menu;
            });
        
        return response()->json($menus, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function getDistinctMenuOptions()
    {
        $availableAreas = Menu::distinct()->pluck('availableArea');
        $categories = Menu::distinct()->pluck('category');
        $subcategories = Menu::distinct()->pluck('subcategory');
        $cuisines = Menu::distinct()->pluck('cuisine');

        return response()->json([
            'availableAreas' => $availableAreas,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'cuisines' => $cuisines,
        ]);
    }
}

