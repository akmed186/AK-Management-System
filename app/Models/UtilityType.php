<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['utility_name', 'unit_of_measure', 'rate_per_unit'])]
class UtilityType extends Model
{
    public function utilityBills(): HasMany
    {
        return $this->hasMany(UtilityBill::class);
    }
}
