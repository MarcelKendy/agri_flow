<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pivot extends Model
{
    protected $fillable = [
        'name'
    ];

    public function plantings() {
        return $this->hasMany(Planting::class);
    }
}