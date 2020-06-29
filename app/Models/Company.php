<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $fillable = [
        'owner_id',
        'country_id',
        'cars_counter',
        'staff_counter',
        'title',
        'logotype_path',
        'created_at',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class, 'company_id', 'id');
    }
}
