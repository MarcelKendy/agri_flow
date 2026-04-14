<?php

namespace App\Http\Controllers;

use App\Classes\Jwt;
use App\Http\Controllers\Controller;
use App\Mail\UserLoginOtpMail;
use App\Mail\UserPasswordResetMail;
use App\Models\User;
use App\Models\ActiveSession;
use App\Models\FailedLoginAttempt;
use App\Models\UserCriticalLog;
use App\Models\UserLoginOtp;
use App\Models\UserPasswordReset;
use App\Models\UserPasswordsLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    private function generateOtp($length = 6, $numeric = false) {
        if ($numeric) {
            $otp = str_pad(random_int(0, ((10 ** $length) - 1)), $length, '0', STR_PAD_LEFT);
        } else {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otp = '';    
            for ($i = 0; $i < $length; $i++) {
                $otp .= $characters[random_int(0, strlen($characters) - 1)];
            }    
        }        
        return $otp;
    }    

    function decryptPassword($encrypted_password) {
        $key = 'My$uperSecretKey3434567890AyCjEF'; 
        $iv = 'MyInitVector3434'; 
        return openssl_decrypt(base64_decode($encrypted_password), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    public function sendOtp(Request $request)
    {
        $user = User::select('id', 'name', 'email', 'password', 'status')->where('email', $request->email)->first();
        if (!$user || !password_verify($this->decryptPassword($request->password), $user->password)) {
            if (isset($user) && !empty($user) && isset($user->id) && !empty($user->id)) {
                $user_id = $user->id;
                $user_status = $user->status;
            } else {
                $user_id = 0;
                $user_status = 0;
            }            
            FailedLoginAttempt::create([ "login" => $request->email, "user_id" => $user_id, "user_status" => $user_status, "ip_access" => $request->ip() ]);
            $failed_attempts_count = FailedLoginAttempt::where('login', $request->email)->where('created_at', '>=', Carbon::now()->subMinutes(10))->count();
            if ($failed_attempts_count >= 5) {
                if ($user_id > 0 && $user->status == 1) {
                    $user->status = 2;
                    $user->save();    
                }
                return response()->json([ 'message' => 'Usuário bloqueado, redefina sua senha.' ], 404);
            } 
            return response()->json([ 'message' => 'Credenciais inválidas, ('.$failed_attempts_count.'/5) tentativas.' ], 404);
        }        
        if ($user->status != 1) {            
            FailedLoginAttempt::create([ "login" => $request->email, "user_id" => $user->id, "user_status" => $user->status == 0 ? 3 : 4, "ip_access" => $request->ip() ]);
            $message = $user->status == 0 ? 'Usuário desativado, contate um administrador.' : 'Usuário bloqueado, redefina sua senha.';
            return response()->json([ 'message' =>  $message ], 400);
        }          
        if (!UserPasswordsLog::where('user_id', $user->id)->where('created_at', '>=', Carbon::now()->subDays(45))->exists()) {
            FailedLoginAttempt::create([ "login" => $request->email, "user_id" => $user->id, "user_status" => 5, "ip_access" => $request->ip() ]);
            return response()->json([ 'message' => 'Senha expirada (> 45 dias), redefina para entrar.' ], 400);
        }
        $otp = $this->generateOtp();        
        UserLoginOtp::where('user_id', $user->id)->where('status', 0)->update([ "status" => 1 ]);
        UserLoginOtp::create(['user_id' => $user->id, 'otp' => $otp, 'ip_access' => $request->ip()]);
        unset($user['password']);
        unset($user['status']);
        try {
            Mail::to($user->email)->send(new UserLoginOtpMail([ "name" => $user->name, "otp" => $otp ]));
        } catch (\Exception $e) {
            $user->otp = $otp;
        } 
        return response()->json($user);
    }

    public function resendOtp(Request $request)
    {        
        $user_otp = UserLoginOtp::select('otp')
        ->where('user_id', $request->id)->where('status', 0)->orderByDesc('created_at')->first();
        if (!$user_otp) {
            return response()->json([ 'message' => 'Ocorreu um erro, solicite um novo código.' ], 404);
        }
        try {
            Mail::to($request->email)->send(new UserLoginOtpMail([ "name" => $request->name, "otp" => $user_otp->otp ]));         
        } catch (\Exception $e) {
            return response()->json([ 'message' => 'Falha ao enviar e-mail, tente mais tarde.' ], 400);
        }
        return response()->json([ 'message' => 'E-mail reenviado, cheque a caixa de spam.' ]);
    }

    public function checkOtpNLogin(Request $request)
    {
        $user_login_otp = UserLoginOtp::where('otp', $request->otp)->where('user_id', $request->id)
        ->where('status', 0)->where('created_at', '>', Carbon::now()->subMinutes(30))
        ->orderByDesc('created_at')->first();    
        if (!$user_login_otp) {                                  
            return response()->json([ 'message' => 'Código incorreto, expirado ou usado.' ], 404);
        }
        $user_login_otp->status = 2;
        $user_login_otp->ip_access = $request->ip();
        $user_login_otp->save();
        $user = User::find($request->id);
        if (!$user || $user->status != 1) {
            return response()->json([ 'message' => 'Usuário não encontrado ou desativado.' ], 404);
        }
        $token = Jwt::create($user);
        ActiveSession::create(['token' => $token, 'user_id' => $user->id]);
        return response()->json(['token' => $token, 'user' => [
            'id' => $user->id,
            'first_name' => substr($user->name, 0, strpos($user->name, ' ')),
            'full_name' => $user->name,
            'cpf' => $user->cpf,
            'email' => $user->email,
            'configs' => json_decode($user->configs),
            'photo' => $user->photo,
            'note' => $user->note,
            'level' => $user->level
        ]]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !password_verify($this->decryptPassword($request->password), $user->password)) {
            return response()->json([ 'message' => 'Credenciais inválidas.' ], 404);
        }        
        if ($user->status != 1) {
            return response()->json([ 'message' => 'Usuário desativado, contate um administrador.' ], 400);
        }     
        $token = Jwt::create($user);
        ActiveSession::create(['token' => $token, 'user_id' => $user->id]);
        return response()->json(['token' => $token, 'user' => [
            'id' => $user->id,
            'first_name' => substr($user->name, 0, strpos($user->name, ' ')),
            'full_name' => $user->name,
            'cpf' => $user->cpf,
            'email' => $user->email,
            'configs' => json_decode($user->configs),
            'photo' => $user->photo,
            'note' => $user->note,
            'level' => $user->level
        ]]);
    }

    public function signin(Request $request)
    {
        if (User::where('cpf', $request['cpf'])->exists() || User::where('email', $request['email'])->exists()) {
            return response()->json([ 'message' => 'Já existe um usuário cadastrado com esses dados.' ], 400);
        }    
        $user = new User();
        $user->name = $request['name'];
        $user->cpf = $request['cpf'];
        $user->email = $request['email'];
        $user->status = 0;
        $user->password = password_hash($this->decryptPassword($request['password']), PASSWORD_DEFAULT);
        $user->save();
        UserPasswordsLog::create([ 'user_id' => $user->id, 'password' => $user->password ]);
        return response()->json([ 'message' => 'Sucesso! Aguarde um administrador validar seu cadastro.' ]);
    }

    public function sendPasswordResetMail(Request $request)
    {
        $user = User::select('id', 'status', 'email', 'name')->where('email', $request['email'])->first();
        if ($user && $user->status != 0) {
            UserPasswordReset::where('user_id', $user->id)->where('status', 0)->update(["status" => 1]);
            $seed = $user->email . round(microtime(true) * 1000) . bin2hex(random_bytes(16));
            $plain_token = base64_encode($seed);
            $hashed_token = password_hash($plain_token, PASSWORD_ARGON2ID);
            $user_password_reset = UserPasswordReset::create(["user_id" => $user->id, "uid" => \Illuminate\Support\Str::uuid()->toString(), "token" => $hashed_token, "ip_access" => $request->ip(), "status" => 0]);
            try {
                Mail::to($user->email)->send(new UserPasswordResetMail(["name" => $user->name, "token" => $plain_token, "uid" => $user_password_reset->uid]));
            } catch (\Exception $e) {
                return response()->json(['message' => 'Falha ao enviar e-mail, tente novamente mais tarde.'], 400);
            }
            if (isset($request['user_id']) && $request['user_id'] > 0) {
                UserCriticalLog::create([
                    'user_id'     => $request['user_id'],
                    'user_ip'     => $request->ip(),
                    'type'        => 0,
                    'table'       => 'users_password_resets',
                    'column'      => '*',
                    'register_id' => $user_password_reset->id,
                    'old_value'   => '',
                    'new_value'   => json_encode($user_password_reset->makeHidden(['token'])->toArray() ?? '', JSON_UNESCAPED_UNICODE)
                ]);
            }
        }
        return response()->json(['message' => 'Se o usuário existir e estiver apto, o e-mail foi enviado'], 200);
    }

    public function verifyPasswordResetToken(Request $request)
    {        
        $user_password_reset = UserPasswordReset::select('token', 'status', 'created_at')->where('uid', $request->uid)->first();
        if (!$user_password_reset || !password_verify($request->token, $user_password_reset->token)) {
            return response()->json(['message' => 'Token inválido. Por favor, verifique o link ou tente novamente.'], 404);
        }
        if ($user_password_reset->status == 1) {
            return response()->json(['message' => 'O token de redefinição foi substituído por um mais recente. Procure por ele ou solicite uma nova redefinição de senha.'], 400);
        }
        if ($user_password_reset->status == 2) {
            return response()->json(['message' => 'O token de redefinição já foi usado. Por favor, solicite uma nova redefinição de senha.'], 400);
        }
        if ($user_password_reset->created_at < now()->subMinutes(30)) {
            return response()->json(['message' => 'O token de redefinição expirou. Por favor, solicite uma nova redefinição de senha.'], 400);
        }        
        return response()->json(['uid' => $request->uid, 'token' => $request->token]);        
    }

    public function resetPasswordNLogin(Request $request)
    {
        $user_password_reset = UserPasswordReset::where('uid', $request->uid)->first();                   
        if (!$user_password_reset || !password_verify($request->token, $user_password_reset->token)) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if ($user_password_reset->status == 1) {
            return response()->json(['message' => 'O token de redefinição foi substituído por um mais recente. Procure por ele ou solicite uma nova redefinição de senha.'], 400);
        }
        if ($user_password_reset->status == 2) {
            return response()->json(['message' => 'O token de redefinição já foi usado. Por favor, solicite uma nova redefinição de senha.'], 400);
        }
        if ($user_password_reset->created_at < now()->subMinutes(30)) {
            return response()->json(['message' => 'O token de redefinição expirou. Por favor, solicite uma nova redefinição de senha.'], 400);
        }                 
        $user = User::findOrFail($user_password_reset->user_id);
        $last_five_passwords = UserPasswordsLog::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(5)->pluck('password');
        $request['password'] = $this->decryptPassword($request->password);
        foreach ($last_five_passwords as $old_password_hash) {
            if (password_verify($request->password, $old_password_hash)) {
                return response()->json(['message' => 'Essa senha está entre suas 5 últimas, escolha uma nova senha.'], 402);
            }
        }
        $password_hash = password_hash($request->password, PASSWORD_DEFAULT);
        $user->update(['password' => $password_hash, 'status' => 1]);
        $user_password_reset->update(['status' => 2]);
        UserPasswordsLog::create(['user_id' => $user->id, 'password' => $password_hash]);
        $token = Jwt::create($user);
        ActiveSession::create(['token' => $token, 'user_id' => $user->id]);
        return response()->json(['token' => $token, 'user' => [
            'id' => $user->id,
            'first_name' => substr($user->name, 0, strpos($user->name, ' ')),
            'full_name' => $user->name,
            'cpf' => $user->cpf,
            'email' => $user->email,
            'configs' => json_decode($user->configs),
            'photo' => $user->photo,
            'note' => $user->note,
            'level' => $user->level
        ]]);
    }

    public function validateSession(Request $request)
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $token = str_replace("Bearer ", "", $authorization);
        $user = $request->input('user');
        $session = ActiveSession::where('token', $token)->where('user_id', $user['id'])->with('user')->first();
        if ((!empty($session) && !empty($user)) && $this->checkUserIntegrity($user, $session->toArray()['user'])) {
            return Jwt::validateToken($token);
        }
        return response()->json('Invalid session data', 401);
    }

    private function checkUserIntegrity($client, $server) 
    {
        if (isset($client['first_name'])) {
            $client['name'] = $client['full_name'];
            unset($client['first_name']);
            unset($client['full_name']);
        }
        foreach ($client as $key => $value) {
            if (isset($server[$key])) {
                if (gettype($value) == 'array') {
                    if (gettype($server[$key]) != 'string') {
                        return false;
                    }
                    $server_array = (array)json_decode($server[$key]);
                    foreach ($value as $key_ => $val) {
                        if (isset($server_array[$key_]) && $val != $server_array[$key_]) {
                            return false;
                        }
                    }
                } else if ($value != $server[$key]) {
                    return false;
                }                
            }            
        }
        return true;
    }

    public function deleteSession(Request $request) {
        return ActiveSession::where('user_id', $request->user_id)->where('token', $request->token)->delete();
    }
    
}
