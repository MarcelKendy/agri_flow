<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'active_ingredient',
        'unit',
        'action_mode',
        'compatibility_restrictions',
        'nitrogen',
        'phosphorus',
        'potassium',
        'calcium',
        'magnesium',
        'sulfur',
        'boron',
        'copper',
        'manganese',
        'zinc',
        'iron',
        'molybdenum',
        'silicon'
    ];

    public function fieldRecords() {
        return $this->hasMany(FieldRecord::class);
    }
}