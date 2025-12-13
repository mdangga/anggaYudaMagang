<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    protected $table = 'images';
    protected $primaryKey = 'id_image';

    protected $fillable = [
        'image_path',
        'alt_text',
        'id_location',
    ];

    public function location()
    {
        return $this->belongsTo(Locations::class, 'id_location', 'id_location');
    }
}
