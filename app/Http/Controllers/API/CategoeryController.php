<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoeryStore;
use App\Models\Categoery;
use Illuminate\Http\Request;

class CategoeryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Categoery::all();
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoeryStore $request)
    {
        $cate = Categoery::create([$request->all()]);
        return response()->json($cate, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoery $categoery)
    {
        $request->validate([
            'type' => 'sometimes|string|unique:categories,type'
        ]);

        $categoery->update($request->all());
        return response()->json($categoery);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoery $category)
    {
        $category->delete();
        return response()->json(null, 204);

    }
}
