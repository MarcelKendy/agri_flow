<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tractor;
use Illuminate\Http\Request;

class TractorController extends Controller
{
    public function getTractor($tractor)
    {
        $tractor = Tractor::findOrFail($tractor);
        return response()->json($tractor);
    }

    public function getTractors(Request $request)
    {
        $tractors = Tractor::get();      
        return response()->json($tractors);
    }

    public function addTractor(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $tractor = Tractor::create($request->all());
        return response()->json($tractor);
    }

    public function editTractor(Request $request, Tractor $tractor)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $tractor->update($request->all());
        return response()->json($tractor);
    }

    public function deleteTractor(Tractor $tractor)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $tractor->delete();
        return response()->json($tractor);
    }

}
