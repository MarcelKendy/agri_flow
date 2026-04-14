<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\Request;

class CropController extends Controller
{
    public function getCrop($crop)
    {
        $crop = Crop::findOrFail($crop);
        return response()->json($crop);
    }

    public function getCrops(Request $request)
    {
        $crops = Crop::get();      
        return response()->json($crops);
    }

    public function addCrop(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $crop = Crop::create($request->all());
        return response()->json($crop);
    }

    public function editCrop(Request $request, Crop $crop)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $crop->update($request->all());
        return response()->json($crop);
    }

    public function deleteCrop(Crop $crop)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $crop->delete();
        return response()->json($crop);
    }

}
