<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Group;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name', 
        'cpf', 
        'email', 
        'sp',
        'group_id',
        'photo',
        'password',
        'configs',
        'level',
        'status',
        'note'
    ];

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function getFirstNameAttribute()
    {
        return substr($this->name, 0, strpos($this->name, ' '));
    }
}
