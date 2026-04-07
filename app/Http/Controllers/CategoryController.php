<?php


namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(10);
        return view('pages/categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages/categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:categories|max:255']);
        Category::create($request->all());
        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function edit(Category $category)
    {
        return view('pages/categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
{
    $request->validate(['name' => 'required|unique:categories,name,' . $category->id]);
    $category->update(['name' => $request->name]);

    return response()->json(['success' => true, 'message' => 'Category updated successfully!']);
}

public function destroy(Category $category)
{
    $category->delete();
    return response()->json(['success' => true, 'message' => 'Category deleted successfully!']);
}
}
