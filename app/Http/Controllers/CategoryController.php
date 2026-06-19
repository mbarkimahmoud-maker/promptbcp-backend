<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::withCount('prompts')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->only(['name', 'description']));

        return response()->json([
            'message'  => 'Catégorie créée avec succès',
            'category' => $category,
        ], 201);
    }
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}