<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'id_category';

    protected $fillable = [
        'name_category',
    ];

    public function locationRequests()
    {
        return $this->hasMany(LocationRequest::class, 'id_category', 'id_category');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'id_category', 'id_category');
    }
}
