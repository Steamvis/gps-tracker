<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'owner_id' => 'integer'
    ];

    public static function updateCarsCounter(int $companyID): void
    {
        $company               = Company::find($companyID);
        $company->cars_counter = $company->cars->count();
        $company->save();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'company_id', 'id');
    }
}
