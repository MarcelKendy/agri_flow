<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [        
        'name', 
        'default_group'
    ];

    protected $casts = [        
        'name' => 'string',
        'default_group' => 'integer'
    ];

}
