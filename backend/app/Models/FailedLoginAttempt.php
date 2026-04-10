<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    use HasFactory;

    protected $table = 'failed_login_attempts';

    protected $fillable = [
        'login', 
        'user_id', 
        'user_status', 
        'ip_access'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
