<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCriticalLog extends Model
{
    use HasFactory;

    protected $table = 'users_critical_logs';

    protected $fillable = [
        'user_id',
        'user_ip',
        'type',
        'table',
        'column',
        'register_id',
        'old_value',
        'new_value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
