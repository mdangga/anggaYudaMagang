<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\storeCategoryRequest;
use App\Http\Requests\Category\updateCategoryRequest;
use App\Models\Categories;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categories::withCount(['locations'])->paginate(10);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function getStatCategories()
    {
        $categories = Categories::withCount(['locations'])->get();

        return response()->json($categories);
    }

    public function create()
    {
        return Inertia::render('Categories/Add');
    }

    public function store(storeCategoryRequest $request)
    {
        $validated = $request->validated();

        Categories::create($validated);

        return redirect()->route('category.index')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $category = Categories::findOrFail($id);

        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(updateCategoryRequest $request, $id)
    {
        $validated = $request->validated();

        $category = Categories::findOrFail($id);
        $category->update($validated);

        return redirect()->route('category.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Categories::findOrFail($id);
        $category->delete();

        return redirect()->route('category.index')->with('success', 'Category deleted successfully.');
    }
}
