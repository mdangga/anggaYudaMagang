<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'location';
    protected $primaryKey = 'id_location';

    protected $fillable = [
        'name_location',
        'description',
        'longitude',
        'latitude',
        'image_path',
        'id_category',
    ];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category', 'id_category');
    }
}
