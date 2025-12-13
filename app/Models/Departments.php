<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id_department';

    protected $fillable = [
        'name_department',
        'degree_level',
        'id_faculty',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculties::class, 'id_faculty', 'id_faculty');
    }

    public function location()
    {
        return $this->hasMany(Locations::class, 'id_department', 'id_department');
    }
}
