<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['room_id', 'utility_type_id', 'billing_month', 'consumption_units', 'total_amount', 'due_date', 'status'])]
class UtilityBill extends Model
{
    protected function casts(): array
    {
        return [
            'billing_month' => 'date',
            'due_date' => 'date',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function utilityType(): BelongsTo
    {
        return $this->belongsTo(UtilityType::class);
    }
}
