<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPasswordsLog extends Model
{
    use HasFactory;

    protected $table = 'users_passwords_log';

    protected $fillable = [
        'user_id', 
        'password'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
