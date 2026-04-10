<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tractor extends Model
{
    protected $fillable = [
        'name'
    ];

    public function fieldRecords() {
        return $this->hasMany(FieldRecord::class);
    }
}