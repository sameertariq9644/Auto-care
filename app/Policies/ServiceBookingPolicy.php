<?php

namespace App\Policies;

use App\Models\ServiceBooking;
use App\Models\User;

class ServiceBookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('customer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceBooking $serviceBooking): bool
    {
        return $user->id === $serviceBooking->customer_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('customer');
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, ServiceBooking $serviceBooking): bool
    {
        return $user->id === $serviceBooking->customer_id
            && in_array($serviceBooking->status, [ServiceBooking::STATUS_PENDING, ServiceBooking::STATUS_CONFIRMED], true);
    }
}
