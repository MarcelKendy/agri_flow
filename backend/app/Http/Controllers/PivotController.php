<?php

namespace App\Http\Controllers;

use App\Models\Pivot;
use Illuminate\Http\Request;

class PivotController extends Controller
{
    public function getPivot($pivot)
    {
        $pivot = Pivot::withCount('plantings')->findOrFail($pivot);
        return response()->json($pivot);
    }

    public function getPivots(Request $request)
    {
        $pivots = Pivot::withCount('plantings')->get();
        return response()->json($pivots);
    }

    public function addPivot(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $pivot = Pivot::create($request->all());
        $pivot->loadCount('plantings');
        return response()->json($pivot);
    }

    public function editPivot(Request $request, Pivot $pivot)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $pivot->update($request->all());
        $pivot->loadCount('plantings');
        return response()->json($pivot);
    }

    public function deletePivot(Pivot $pivot)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if ($pivot->plantings()->exists()) {
            return response()->json(['error' => 'Não é possível excluir um pivô que é utilizado em um plantio'], 409);
        }
        $pivot->delete();
        return response()->json($pivot);
    }
}