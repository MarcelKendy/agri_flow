<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldRecord extends Model
{
    protected $fillable = [
        'service',
        'date',
        'planting_id',
        'tractor_id',
        'implement_id',
        'product_id',
        'dosage',
        'notes',
        'status'
    ];

    public function planting() {
        return $this->belongsTo(Planting::class)->with(['crop', 'pivot']);
    }

    public function tractor() {
        return $this->belongsTo(Tractor::class);
    }

    public function implement() {
        return $this->belongsTo(Implement::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}