<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FieldRecord;
use App\Models\FieldRecordProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class FieldRecordController extends Controller
{
    public function getFieldRecord($field_record)
    {
        $field_record = FieldRecord::with(['planting', 'tractor', 'implement', 'products'])->findOrFail($field_record);
        return response()->json($field_record);
    }

    public function getFieldRecords(Request $request)
    {
        $field_records = FieldRecord::with(['planting', 'tractor', 'implement', 'products'])->orderBy('date')->get();
        return response()->json($field_records);
    }

    public function addFieldRecord(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $field_record = FieldRecord::create($request->except('products'));
        if ($request->has('products') && is_array($request->products) && count($request->products) > 0) {
            foreach ($request->products as $product) {
                if (
                    $field_record->id &&
                    isset($product['product_id']) && !empty($product['product_id']) &&
                    isset($product['dosage']) && !empty($product['dosage'])
                ) {
                    FieldRecordProduct::create([
                        'field_record_id' => $field_record->id,
                        'product_id' => $product['product_id'],
                        'dosage' => $product['dosage'],
                    ]);
                    Product::where('id', $product['product_id'])->update(['last_dosage' => $product['dosage']]);
                }
            }
        }
        return response()->json($field_record->load(['planting', 'tractor', 'implement', 'products']));
    }

    public function editFieldRecord(Request $request, FieldRecord $field_record)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $field_record->update($request->except('products'));
        if ($request->has('products') && is_array($request->products)) {
            $existing_products = FieldRecordProduct::where('field_record_id', $field_record->id)->get();
            $request_product_ids = [];
            foreach ($request->products as $product) {
                if (
                    $field_record->id &&
                    isset($product['product_id']) && !empty($product['product_id']) &&
                    isset($product['dosage']) && !empty($product['dosage'])
                ) {
                    $request_product_ids[] = $product['product_id'];
                    $field_record_product = $existing_products->firstWhere('product_id', $product['product_id']);
                    if ($field_record_product) {
                        $field_record_product->update(['dosage' => $product['dosage']]);
                    } else {
                        FieldRecordProduct::create([
                            'field_record_id' => $field_record->id,
                            'product_id' => $product['product_id'],
                            'dosage' => $product['dosage'],
                        ]);
                    }
                    Product::where('id', $product['product_id'])->update(['last_dosage' => $product['dosage']]);
                }
            }
            foreach ($existing_products as $existing_product) {
                if (!in_array($existing_product->product_id, $request_product_ids)) {
                    $existing_product->delete();
                }
            }
        }
        return response()->json($field_record->fresh()->load(['planting', 'tractor', 'implement', 'products']));
    }

    public function deleteFieldRecord(FieldRecord $field_record)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $field_record->load(['planting', 'tractor', 'implement', 'products']);
        $field_record->products()->delete();
        $field_record->delete();
        return response()->json($field_record);
    }
}
