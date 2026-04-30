<?php

namespace App\Http\Controllers;

use App\Models\Implement;
use Illuminate\Http\Request;

class ImplementController extends Controller
{
    public function getImplement($implement)
    {
        $implement = Implement::withCount('fieldRecords')->findOrFail($implement);
        return response()->json($implement);
    }

    public function getImplements(Request $request)
    {
        $implements = Implement::withCount('fieldRecords')->get();
        return response()->json($implements);
    }

    public function addImplement(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $implement = Implement::create($request->all());
        $implement->loadCount('fieldRecords');
        return response()->json($implement);
    }

    public function editImplement(Request $request, Implement $implement)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $implement->update($request->all());
        $implement->loadCount('fieldRecords');
        return response()->json($implement);
    }

    public function deleteImplement(Implement $implement)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if ($implement->fieldRecords()->exists()) {
            return response()->json(['error' => 'Não é possível excluir um implemento que é utilizado em uma ficha de campo'], 409);
        }
        $implement->delete();
        return response()->json($implement);
    }
}