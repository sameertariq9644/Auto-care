<?php

namespace App\Http\Requests;

use App\Models\ServiceBooking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceBooking::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required',
                Rule::exists('vehicles', 'id')->where('user_id', $this->user()->id),
            ],
            'requested_service' => ['required', 'string', Rule::in(ServiceBooking::SERVICE_TYPES)],
            'preferred_date' => ['required', 'date', 'after_or_equal:now'],
        ];
    }
}
