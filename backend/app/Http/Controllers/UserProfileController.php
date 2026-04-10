<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActiveSession;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{

    public function getActiveSession(Request $request)
    {
        $active_session = ActiveSession::select('created_at')->where('user_id', $request->input('user_id'))->whereDate('created_at', Carbon::now()->startOfDay()->format('Y-m-d'))->first();
        return response()->json($active_session);
    }

}
