<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'first_name', 'last_name', 'email', 'phone_number', 'emergency_contact_name', 'emergency_contact_phone'])]
class Tenant extends Model
{
    use HasFactory;

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function currentRental(): HasOne
    {
        return $this->hasOne(Rental::class)->where('lease_status', 'active')->latestOfMany();
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
