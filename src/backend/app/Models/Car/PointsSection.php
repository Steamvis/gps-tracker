<?php

namespace App\Models\Car;

use Illuminate\Database\Eloquent\Model;

class PointsSection extends Model
{
    protected $table = 'points_section';

    public $timestamps = false;

    protected $fillable = [
        'section_id',
        'point_id'
    ];
}
