<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function importProducts(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        if (!is_array($request->all())) {
            return response()->json([
                'error' => 'Formato inválido, esperado um array de produtos'
            ], 422);
        }

        $created_products = [];

        DB::transaction(function () use ($request, &$created_products) {
            foreach ($request->all() as $index => $data) {

                $validator = Validator::make($data, [
                    'name' => 'required|string|min:3',
                    'category' => 'required|string',
                    'unit' => 'required|in:0,1',

                    'active_ingredient' => 'nullable|string',
                    'action_mode' => 'nullable|string',
                    'compatibility_restrictions' => 'nullable|string',

                    'nitrogen' => 'nullable|numeric|min:0|max:100',
                    'phosphorus' => 'nullable|numeric|min:0|max:100',
                    'potassium' => 'nullable|numeric|min:0|max:100',
                    'calcium' => 'nullable|numeric|min:0|max:100',
                    'magnesium' => 'nullable|numeric|min:0|max:100',
                    'sulfur' => 'nullable|numeric|min:0|max:100',
                    'boron' => 'nullable|numeric|min:0|max:100',
                    'copper' => 'nullable|numeric|min:0|max:100',
                    'manganese' => 'nullable|numeric|min:0|max:100',
                    'zinc' => 'nullable|numeric|min:0|max:100',
                    'iron' => 'nullable|numeric|min:0|max:100',
                    'molybdenum' => 'nullable|numeric|min:0|max:100',
                    'silicon' => 'nullable|numeric|min:0|max:100',
                ]);

                if ($validator->fails()) {
                    throw new \Exception(
                        'Erro no item ' . ($index + 1) . ': ' .
                            $validator->errors()->first()
                    );
                }

                $product = Product::create($validator->validated());
                $product->loadCount('fieldRecordProducts');

                $created_products[] = $product;
            }
        });

        return response()->json($created_products);
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
