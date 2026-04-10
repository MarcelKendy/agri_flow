<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Implement extends Model
{
    protected $fillable = [
        'name'
    ];

    public function fieldRecords() {
        return $this->hasMany(FieldRecord::class);
    }
}
