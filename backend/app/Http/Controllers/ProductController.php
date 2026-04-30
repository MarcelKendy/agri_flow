<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProduct($product)
    {
        $product = Product::withCount('fieldRecordProducts')->findOrFail($product);
        return response()->json($product);
    }

    public function getProducts(Request $request)
    {
        $products = Product::withCount('fieldRecordProducts')->get();
        return response()->json($products);
    }

    public function addProduct(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $product = Product::create($request->all());
        $product->loadCount('fieldRecordProducts');
        return response()->json($product);
    }

    public function editProduct(Request $request, Product $product)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $product->update($request->all());
        $product->loadCount('fieldRecordProducts');
        return response()->json($product);
    }

    public function deleteProduct(Product $product)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if ($product->fieldRecordProducts()->exists()) {
            return response()->json([
                'error' => 'Não é possível excluir um produto que é utilizado em uma ficha de campo'
            ], 409);
        }
        $product->delete();
        return response()->json($product);
    }
}