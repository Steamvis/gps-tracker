<?php

namespace App\Models\Car;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\URL;

class CarMark extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'name',
        'mark_image_path'
    ];

    public function getImageAttribute(): string
    {
        return URL::asset($this->attributes['mark_image_path']);
    }

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
