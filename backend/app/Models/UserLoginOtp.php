<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginOtp extends Model
{
    use HasFactory;

    protected $table = 'users_login_otps';

    protected $fillable = [
        'user_id',
        'ip_access',
        'otp',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
