<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'type',
        'status'
    ];

    protected $casts = [
        'name' => 'string'
    ];

    public function campaignProducts()
    {
        return $this->hasManyThrough(CampaignProduct::class, Campaign::class);
    }
    

}
