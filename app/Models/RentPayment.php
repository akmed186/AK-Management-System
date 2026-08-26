<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rental_id', 'amount_paid', 'payment_date', 'payment_method', 'status', 'transaction_reference'])]
class RentPayment extends Model
{
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
