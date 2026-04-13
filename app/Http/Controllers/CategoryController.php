<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::whereNull('user_id')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string',
            'type' => 'required|in:income,expense',
        ]);

        $category = Category::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'is_default' => false,
        ]);

        return response()->json($category, 201);
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
