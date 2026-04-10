<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function getGroup($group)
    {
        $group = Group::findOrFail($group);
        return response()->json($group);
    }

    public function getGroups(Request $request)
    {
        if ($request->input('manage')) {
            $groups = Group::get();
        } else {
            $groups = Group::where('default_group', 0)->get();            
        }        
        return response()->json($groups);
    }

    public function addGroup(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $group = Group::create($request->all());
        return response()->json($group);
    }

    public function editGroup(Request $request, Group $group)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $group->update($request->all());
        return response()->json($group);
    }

    public function deleteGroup(Group $group)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 1) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $users_in_group = User::where('group_id', $group->id)->get();    
        if ($users_in_group->isNotEmpty()) {
            $default_group = Group::where('default_group', 1)->first();    
            if (!$default_group) {
                $default_group = Group::create([ "name" => "Padrão", "default_group" => 1]);
            } 
            foreach ($users_in_group as $user) {
                $user->group_id = $default_group->id;
                $user->save();
            }
        }
        $group->delete();    
        return response()->json($group);
    }    

}
