<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;    

    protected $fillable = [
        'user_id', 
        'user_ip',
        'user_agent',
        'device',
        'browser',
        'os',
        'controller_method',
        'request_url',
        'request_method',
        'request_parameters',
        'request_headers'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
