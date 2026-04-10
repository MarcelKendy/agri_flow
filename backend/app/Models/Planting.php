<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planting extends Model
{
    protected $fillable = [
        'name',
        'crop_id',
        'pivot_id',
        'size_ha',
        'date',
        'variety',
        'status'
    ];

    public function crop() {
        return $this->belongsTo(Crop::class);
    }

    public function pivot() {
        return $this->belongsTo(Pivot::class);
    }

    public function fieldRecords() {
        return $this->hasMany(FieldRecord::class);
    }
}