<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculties extends Model
{
    protected $table = 'faculties';
    protected $primaryKey = 'id_faculty';

    protected $fillable = [
        'name_faculty',
    ];

    public function departments()
    {
        return $this->hasMany(Departments::class, 'id_faculty', 'id_faculty');
    }
}
