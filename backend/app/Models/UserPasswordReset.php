<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPasswordReset extends Model
{
    use HasFactory;

    protected $table = 'users_password_resets';

    protected $fillable = [
        'uid',
        'user_id',
        'token',
        'ip_access',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
        
    public function adminLog()
    {
        return $this->hasOne(UserCriticalLog::class, 'register_id', 'id')->where('table', 'users_password_resets');
    }

}
