<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vehicle_id', 'booking_id', 'mechanic_id', 'description', 'labor_cost', 'serviced_at'])]
class ServiceRecord extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'serviced_at' => 'datetime',
            'labor_cost' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class, 'booking_id');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceRecordItem::class);
    }
}
