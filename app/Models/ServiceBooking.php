<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vehicle_id', 'customer_id', 'mechanic_id', 'requested_service', 'preferred_date', 'status'])]
class ServiceBooking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const SERVICE_TYPES = [
        'Oil Change',
        'Brake Inspection',
        'Tire Rotation',
        'Battery Check',
        'General Inspection',
        'Diagnostic',
        'Other',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preferred_date' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class, 'booking_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'booking_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class)->latest();
    }

    /**
     * Move the booking to a new status and record it in the timeline.
     */
    public function transitionTo(string $status, ?User $changedBy = null, ?string $note = null): void
    {
        $this->update(['status' => $status]);

        $this->statusHistories()->create([
            'status' => $status,
            'changed_by' => $changedBy?->id,
            'note' => $note,
        ]);
    }
}
