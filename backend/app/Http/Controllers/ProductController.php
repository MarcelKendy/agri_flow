<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProduct($product)
    {
        $product = Product::findOrFail($product);
        return response()->json($product);
    }

    public function getProducts(Request $request)
    {
        if ($request->input('manage')) {
            $products = Product::orderBy('name')->get();
        } else {
            $products = Product::where('status', 1)->orderBy('name')->get();
        }        
        return response()->json($products);
    }

    public function addProduct(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $product = Product::create($request->all());
        return response()->json($product);
    }

    public function editProduct(Request $request, Product $product)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $product->update($request->all());
        return response()->json($product);
    }

    public function deleteProduct(Product $product)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $product->delete();
        return response()->json($product);
    }

}
