<?php

namespace App\Http\Controllers;

use App\Models\Categoery;
use Illuminate\Http\Request;

class CategoeryContrller extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|categoeries,name'
        ]);


        $categoery = Categoery::create([
            'name' => $request->name,
        ]);




        return response()->json(['message' => 'add to categoery successfully'], 200);


    }
}
