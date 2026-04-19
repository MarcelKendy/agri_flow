<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FieldRecord;
use Illuminate\Http\Request;

class FieldRecordController extends Controller
{
    public function getFieldRecord($field_record)
    {
        $field_record = FieldRecord::with(['planting', 'tractor', 'implement', 'product'])->findOrFail($field_record);
        return response()->json($field_record);
    }

    public function getFieldRecords(Request $request)
    {
        $field_records = FieldRecord::with(['planting', 'tractor', 'implement', 'product'])->orderBy('date')->get();
        return response()->json($field_records);
    }

    public function addFieldRecord(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $field_record = FieldRecord::create($request->all());
        return response()->json($field_record->load(['planting', 'tractor', 'implement', 'product']));
    }

    public function editFieldRecord(Request $request, FieldRecord $field_record)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $field_record->update($request->all());
        return response()->json($field_record->fresh()->load(['planting', 'tractor', 'implement', 'product']));
    }

    public function deleteFieldRecord(FieldRecord $field_record)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $field_record->load(['planting', 'tractor', 'implement', 'product']);
        $field_record->delete();
        return response()->json($field_record);
    }
}