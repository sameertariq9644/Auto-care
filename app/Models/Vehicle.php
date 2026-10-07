<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'make', 'model', 'year', 'plate_number', 'vin', 'services', 'image_path', 'qr_uuid', 'qr_code_path'])]
class Vehicle extends Model
{
    use HasFactory;

    /**
     * Public URL of the uploaded vehicle photo, or null so views fall back to the placeholder
     * illustration. Root-relative so it works whichever host the app is served from.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? '/storage/'.ltrim($this->image_path, '/')
            : null);
    }

    /**
     * How the vehicle is named everywhere in the app, e.g. "Toyota Corolla (Year 2021)".
     * The year is labelled so it can't be mistaken for part of the model name.
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => ucfirst($this->make).' '.ucfirst($this->model)." (Year {$this->year})");
    }

    protected function qrCodeUrl(): Attribute
    {
        return Attribute::get(fn () => $this->qr_code_path
            ? Storage::disk('public')->url($this->qr_code_path)
            : null);
    }

    protected function casts(): array
    {
        return [
            'services' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function latestServiceRecord(): HasOne
    {
        return $this->hasOne(ServiceRecord::class)->latestOfMany('serviced_at');
    }

    public function nextBooking(): HasOne
    {
        return $this->hasOne(ServiceBooking::class)
            ->whereIn('status', [ServiceBooking::STATUS_PENDING, ServiceBooking::STATUS_CONFIRMED])
            ->where('preferred_date', '>=', now())
            ->oldestOfMany('preferred_date');
    }
}
