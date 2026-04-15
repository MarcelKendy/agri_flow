<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Implement;
use Illuminate\Http\Request;

class ImplementController extends Controller
{
    public function getImplement($implement)
    {
        $implement = Implement::findOrFail($implement);
        return response()->json($implement);
    }

    public function getImplements(Request $request)
    {
        $implements = Implement::get();      
        return response()->json($implements);
    }

    public function addImplement(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $implement = Implement::create($request->all());
        return response()->json($implement);
    }

    public function editImplement(Request $request, Implement $implement)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $implement->update($request->all());
        return response()->json($implement);
    }

    public function deleteImplement(Implement $implement)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $implement->delete();
        return response()->json($implement);
    }

}
