<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class Amenity extends Model
{
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }
}
