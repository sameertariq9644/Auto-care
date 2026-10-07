<?php

namespace App\Http\Requests;

use App\Models\ServiceBooking;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Vehicle::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'plate_number' => ['required', 'string', 'max:255', 'unique:vehicles,plate_number'],
            'vin' => ['nullable', 'string', 'max:255', 'unique:vehicles,vin'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'services' => ['array'],
            'services.*' => ['string', 'distinct', Rule::in(ServiceBooking::SERVICE_TYPES)],
        ];
    }

    /**
     * Treat "no services ticked" as an empty selection.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(['services' => []]);
    }
}
