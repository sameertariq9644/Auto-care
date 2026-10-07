<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreServiceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Ownership of the booking is checked separately in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'labor_cost' => ['required', 'numeric', 'min:0'],
            'items' => ['array'],
            'items.*.inventory_item_id' => ['required_with:items', 'distinct', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }

    /**
     * Reject items that request more stock than is currently available.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = collect($this->input('items', []));

            if ($items->isEmpty()) {
                return;
            }

            $stock = InventoryItem::whereIn('id', $items->pluck('inventory_item_id'))->pluck('quantity', 'id');

            foreach ($items as $index => $item) {
                $available = $stock[$item['inventory_item_id']] ?? null;

                if ($available !== null && (int) $item['quantity'] > $available) {
                    $validator->errors()->add("items.{$index}.quantity", "Only {$available} in stock.");
                }
            }
        });
    }
}
