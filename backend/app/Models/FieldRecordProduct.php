<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldRecordProduct extends Model
{
    protected $fillable = [
        'field_record_id',
        'product_id',
        'dosage',
    ];

    public function fieldRecord()
    {
        return $this->belongsTo(FieldRecord::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
