<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileWeb extends Model
{
    protected $table = 'profile_web';
    protected $primaryKey = 'id';

    protected $fillable = [
        'app_name',
        'logo_path',
        'description'
    ];
}
