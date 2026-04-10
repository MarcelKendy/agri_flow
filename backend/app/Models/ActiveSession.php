<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ActiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'token', 
        'user_id'
    ];

    protected $casts = [
        'token', 
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
