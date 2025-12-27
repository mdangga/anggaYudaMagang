<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locations extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'id_location';

    protected $fillable = [
        'student_name',
        'nim',
        'name_location',
        'description',
        'contact',
        'longitude',
        'latitude',
        'approved_at',
        'id_category',
        'id_department',
    ];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
        'approved_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class, 'id_category', 'id_category');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'id_department', 'id_department');
    }

    public function images()
    {
        return $this->hasMany(Images::class, 'id_location', 'id_location');
    }
}
