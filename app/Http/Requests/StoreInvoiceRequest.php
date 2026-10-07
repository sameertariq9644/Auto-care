<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends UpdateInvoiceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! User::role('customer')->whereKey($value)->exists()) {
                        $fail('The selected customer is invalid.');
                    }
                },
            ],
            'booking_id' => [
                'nullable',
                'integer',
                Rule::exists('service_bookings', 'id')->where('customer_id', $this->input('customer_id')),
            ],
            ...parent::rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_id.exists' => 'The selected booking does not belong to this customer.',
        ];
    }
}
