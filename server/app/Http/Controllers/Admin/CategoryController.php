<?php

namespace App\Http\Controllers\Admin;
use App\Models\Category;

use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     
        $categories = Category::all();

        return view('admin.categories.index',compact('categories'));
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
                // $categories = Category::all();

        return view('admin.categories.create');
        // return view('admin.categories.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:255|unique:categories,name',
        ]);

        
        Category::create([
            'name'=>$request->name,
        ]);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $category = Category::findOrFail($id);
        return view('admin.categories.edit',compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $category = Category::findOrFail($id);
        ///ai
        $request->validate([
            'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('categories')->ignore($category->id),
        ],
]);
        $category->update([
            'name'=>$request->name,
        ]);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
