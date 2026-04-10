<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCriticalLog;
use App\Models\UserPasswordsLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    function decryptPassword($encrypted_password)
    {
        $key = 'My$uperSecretKey3267567890AyCjEF';
        $iv = 'MyInitVector3267';
        return openssl_decrypt(base64_decode($encrypted_password), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    public function getUsers(Request $request)
    {
        if ($request->input('manage')) {
            $users = User::with('group')->orderByDesc('level')->orderByDesc('status')->get();
        } else {
            $users = User::with('group')->where('status', 1)->orderBy('name')->get();
        }        
        $users->each->makeHidden(['password']);
        return response()->json($users);
    }

    public function getUsersPhotos(Request $request)
    {
        $users = User::select('id', 'photo')->whereIn('id', $request->input('users_ids'))->get();
        return response()->json($users);
    }

    public function getUser(Request $request, $id)
    {
        $select = (array) $request->input('select', []);
        $select = array_filter($select, fn ($col) => $col !== 'password');
        if (!empty($select)) {
            if (!in_array('id', $select)) {
                $select[] = 'id';
            }
            $user = User::select($select)->find($id);
        } else {
            $user = User::with('group')->find($id);
        }
        if (!$user) {
            return response()->json([]);
        }
        $user->makeHidden(['password']);
        return response()->json($user);
    }

    public function getUserPasswordExpire(Request $request)
    {
        $user_password_log = UserPasswordsLog::select('created_at')->where('user_id', $request->user_id)->orderByDesc('created_at')->first();
        if (!$user_password_log) {
            return response()->json(['message' => 'Sem registro de mudança de senha.'], 404);
        }
        $expiration_date = Carbon::parse($user_password_log->created_at)->addDays(45);
        $now = Carbon::now();
        if ($now->greaterThanOrEqualTo($expiration_date)) {
            return response()->json(['expired' => true]);
        }
        $days_left = $now->diffInDays($expiration_date);
        if ($days_left >= 1) {
            return response()->json(['days' => $days_left, 'hours' => 0, 'minutes' => 0, 'text' => 'Sua senha expira em ' . ($days_left == 1 ? '1 dia' : $days_left . ' dias')]);
        }
        $hours_left = $now->copy()->addDays($days_left)->diffInHours($expiration_date);
        $minutes_left = $now->copy()->addDays($days_left)->addHours($hours_left)->diffInMinutes($expiration_date);
        if ($hours_left + $minutes_left <= 0) {
            return response()->json(['days' => 0, 'hours' => 0, 'minutes' => 0, 'text' => 'Sua senha expirou, logout automático.']);
        }
        $hours_left_text = ($hours_left > 0 ? ($hours_left > 1 ? $hours_left . ' horas' : '1 hora') : '');
        $minutes_left_text = ($minutes_left > 0 ? ($minutes_left > 1 ? $minutes_left . ' minutos' : '1 minuto') : '');
        $text = $hours_left_text;
        $text = strlen($minutes_left_text) > 0 ? (strlen($text) > 0 ? $text . ' e ' : '') . $minutes_left_text : $text;
        return response()->json(['days' => 0, 'hours' => $hours_left, 'minutes' => $minutes_left, 'text' => 'Sua senha expira em ' . $text]);
    }

    public function removeProfilePhoto(User $user)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2 && $auth_user->id != $user->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $photo_directory = 'public/profile_photos/' . $user->id;
        Storage::deleteDirectory($photo_directory);
        $user->update(['photo' => null]);
        return response()->noContent();
    }

    public function addUser(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $request['email'] .= '@sicoobcredisg.com.br';
        if (User::where('cpf', $request['cpf'])->exists() || User::where('email', $request['email'])->exists()) {
            return response()->json(['message' => 'Já existe um usuário cadastrado com esses dados.'], 400);
        }
        $user = new User();
        $user->name = $request['name'];
        $user->cpf = $request['cpf'];
        $user->email = $request['email'];
        $user->sp = $request['sp'];
        $user->group_id = $request['group_id'];
        $user->status = 1;
        $user->password = password_hash($this->decryptPassword($request['password']), PASSWORD_DEFAULT);        
        $user->save();
        UserPasswordsLog::create(['user_id' => $user->id, 'password' => $user->password]);        
        $user->refresh()->load(['group']);
        UserCriticalLog::create([
            'user_id'     => $auth_user->id,
            'user_ip'     => $request->ip(),
            'type'        => 0,
            'table'       => 'users',
            'column'      => '*',
            'register_id' => $user->id,
            'old_value'   => '',
            'new_value'   => json_encode($user->makeHidden(['password'])->toArray() ?? '', JSON_UNESCAPED_UNICODE)
        ]);
        unset($user->password);
        return response()->json($user);
    }

    public function editProfile(Request $request)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2 && $auth_user->id != $request->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (isset($request->email) && !empty($request->email)) {
            $request['email'] .= '@sicoobcredisg.com.br';
        } else {
            unset($request['email']);
        }
        if (isset($request->password) && !empty($request->password) && strlen($request->password) > 0) {
            $last_five_passwords = UserPasswordsLog::where('user_id', $request->id)->orderBy('created_at', 'desc')->limit(5)->pluck('password');
            foreach ($last_five_passwords as $old_password_hash) {
                if (password_verify($request->password, $old_password_hash)) {
                    return response()->json(['message' => 'Essa senha está entre suas 5 últimas, escolha uma nova senha.'], 400);
                }
            }
            $request['password'] = password_hash($request->password, PASSWORD_DEFAULT);
            UserPasswordsLog::create(['user_id' => $request->id, 'password' => $request['password']]);
        } else {
            unset($request['password']);
        }
        if ($request->hasFile('attachment')) {
            $photo_directory = 'public/profile_photos/' . $request->id;
            if (Storage::exists($photo_directory)) {
                $files = Storage::files($photo_directory);
                foreach ($files as $file) {
                    Storage::delete($file);
                }
            }
            $photo = $request->file('attachment');
            $request['photo'] = str_replace('public/', '', $photo->storeAs($photo_directory, $photo->getClientOriginalName()));
        } else {
            unset($request['photo']);
        }
        $user = User::find($request->id);        
        $user->fill($request->all());
        if (!$user->isDirty()) {
            return response()->noContent();
        }
        $changed_keys = array_keys($user->getDirty());
        $user_old = $user->getOriginal();
        unset($user_old['password']);
        $user->save();
        $user_new = $user->makeHidden(['password'])->toArray();
        if (count($changed_keys) === 1) {
            $field = $changed_keys[0];
            UserCriticalLog::create([
                'user_id'     => $auth_user->id,
                'user_ip'     => $request->ip(),
                'type'        => 1,
                'table'       => 'users',
                'column'      => $field,
                'register_id' => $user->id,
                'old_value'   => $user_old[$field] ?? '',
                'new_value'   => $user_new[$field] ?? ''
            ]);
        } else {
            UserCriticalLog::create([
                'user_id'     => $auth_user->id,
                'user_ip'     => $request->ip(),
                'type'        => 1,
                'table'       => 'users',
                'column'      => '*',
                'register_id' => $user->id,
                'old_value'   => json_encode($user_old ?? '', JSON_UNESCAPED_UNICODE),
                'new_value'   => json_encode($user_new ?? '', JSON_UNESCAPED_UNICODE)
            ]);
        }
        $user->load('group');
        return response()->json(
            [
                'id' => $user->id,
                'first_name' => substr($user->name, 0, strpos($user->name, ' ')),
                'full_name' => $user->name,
                'cpf' => $user->cpf,
                'email' => $user->email,
                'sp' => intval($user->sp),
                'group_id' => intval($user->group_id),
                'group' => $user->group->name,
                'configs' => json_decode($user->configs),
                'photo' => $user->photo,
                'note' => $user->note,
                'level' => $user->level
            ]
        );
    }

    public function editUser(Request $request, User $user)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2 && $auth_user->id != $request->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (isset($request->email) && !empty($request->email)) {
            $request['email'] .= '@sicoobcredisg.com.br';
        } else {
            unset($request['email']);
        }
        if (isset($request->password) && !empty($request->password) && strlen($request->password) > 0) {
            $last_five_passwords = UserPasswordsLog::where('user_id', $request->id)->orderBy('created_at', 'desc')->limit(5)->pluck('password');
            foreach ($last_five_passwords as $old_password_hash) {
                if (password_verify($request->password, $old_password_hash)) {
                    return response()->json(['message' => 'Essa senha está entre suas 5 últimas, escolha uma nova senha.'], 400);
                }
            }
            $request['password'] = password_hash($request->password, PASSWORD_DEFAULT);
            UserPasswordsLog::create(['user_id' => $user->id, 'password' => $request['password']]);
        } else {
            unset($request['password']);
        }
        if (isset($request->configs) && !empty($request->configs)) {
            $request['configs'] = json_encode($request->configs);
        }
        $user->fill($request->all());
        if (!$user->isDirty()) {
            return response()->noContent(); 
        }
        $changed_keys = array_keys($user->getDirty());
        $user_old = $user->getOriginal();
        unset($user_old['password']);
        $user->save();
        $user_new = $user->makeHidden(['password'])->toArray();
        if (count($changed_keys) === 1) {
            $field = $changed_keys[0];
            UserCriticalLog::create([
                'user_id'     => $auth_user->id,
                'user_ip'     => $request->ip(),
                'type'        => 1,
                'table'       => 'users',
                'column'      => $field,
                'register_id' => $user->id,
                'old_value'   => $user_old[$field] ?? '',
                'new_value'   => $user_new[$field] ?? ''
            ]);
        } else {
            UserCriticalLog::create([
                'user_id'     => $auth_user->id,
                'user_ip'     => $request->ip(),
                'type'        => 1,
                'table'       => 'users',
                'column'      => '*',
                'register_id' => $user->id,
                'old_value'   => json_encode($user_old ?? '', JSON_UNESCAPED_UNICODE),
                'new_value'   => json_encode($user_new ?? '', JSON_UNESCAPED_UNICODE)
            ]);
        }
        return response()->json(
            [
                'id' => $user->id,
                'first_name' => substr($user->name, 0, strpos($user->name, ' ')),
                'full_name' => $user->name,
                'cpf' => $user->cpf,
                'email' => $user->email,
                'sp' => intval($user->sp),
                'group_id' => intval($user->group_id),
                'group' => $user->group->name,
                'configs' => json_decode($user->configs),
                'photo' => $user->photo,
                'note' => $user->note,
                'level' => $user->level
            ]
        );
    }

    public function resetPassword(Request $request, User $user)
    {
        $auth_user = auth()->user();
        if ($auth_user->level < 2 && $auth_user->id != $request->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $password_hash = password_hash($request->password, PASSWORD_DEFAULT);
        $old_password = $user->password;
        $user->update(['password' => $password_hash]);
        UserPasswordsLog::create(['user_id' => $user->id, 'password' => $password_hash]);
        UserCriticalLog::create([
            'user_id'     => $auth_user->id,
            'user_ip'     => $request->ip(),
            'type'        => 1,
            'table'       => 'users',
            'column'      => 'password',
            'register_id' => $user->id,
            'old_value'   => $old_password,
            'new_value'   => $password_hash
        ]);
        return response()->json($user);
    }
}
