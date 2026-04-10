<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /categories - ambil kategori default + milik user
    public function index(Request $request)
    {
        $categories = Category::where('is_default', true)
            ->orWhere('user_id', $request->user()->id)
            ->get();

        return response()->json($categories);
    }

    // POST /categories - buat kategori custom
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

    // DELETE /categories/{id} - hapus kategori milik user (tidak bisa hapus default)
    public function destroy(Request $request, $id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}