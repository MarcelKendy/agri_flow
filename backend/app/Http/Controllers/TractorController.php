<?php

namespace App\Http\Controllers;

use App\Models\Tractor;
use Illuminate\Http\Request;

class TractorController extends Controller
{
    public function getTractor($tractor)
    {
        $tractor = Tractor::withCount('fieldRecords')->findOrFail($tractor);
        return response()->json($tractor);
    }

    public function getTractors(Request $request)
    {
        $tractors = Tractor::withCount('fieldRecords')->get();
        return response()->json($tractors);
    }

    public function addTractor(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $tractor = Tractor::create($request->all());
        $tractor->loadCount('fieldRecords');
        return response()->json($tractor);
    }

    public function editTractor(Request $request, Tractor $tractor)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $tractor->update($request->all());
        $tractor->loadCount('fieldRecords');
        return response()->json($tractor);
    }

    public function deleteTractor(Tractor $tractor)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if ($tractor->fieldRecords()->exists()) {
            return response()->json(['error' => 'Não é possível excluir um trator que é utilizado em uma ficha de campo'], 409);
        }
        $tractor->delete();
        return response()->json($tractor);
    }
}
