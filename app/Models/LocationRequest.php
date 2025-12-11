<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationRequest extends Model
{
    protected $table = 'location_request';
    protected $primaryKey = 'id_request';

    protected $fillable = [
        'student_name',
        'nim',
        'name_location',
        'description',
        'longitude',
        'latitude',
        'image_path',
        'id_category',
        'approved_at',
    ];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
        'approved_at' => 'datetime',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category', 'id_category');
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function approve()
    {
        $this->approved_at = now();
        $this->save();
    }
}
