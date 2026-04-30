<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Planting;
use Illuminate\Http\Request;

class PlantingController extends Controller
{
    public function getPlanting($planting)
    {
        $planting = Planting::with(['crop', 'pivot', 'fieldRecords'])->findOrFail($planting);
        return response()->json($planting);
    }

    public function getPlantings(Request $request)
    {
        $plantings = Planting::with(['crop', 'pivot', 'fieldRecords'])->orderByDesc('id')->get();
        return response()->json($plantings);
    }

    public function addPlanting(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $planting = Planting::create($request->all());
        return response()->json($planting->load(['crop', 'pivot', 'fieldRecords']));
    }

    public function editPlanting(Request $request, Planting $planting)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $planting->update($request->all());
        return response()->json($planting->fresh()->load(['crop', 'pivot', 'fieldRecords']));
    }

    public function deletePlanting(Planting $planting)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $planting->delete();
        return response()->json($planting);
    }
}