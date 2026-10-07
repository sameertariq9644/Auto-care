<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'sku', 'quantity', 'unit_price', 'reorder_level'])]
class InventoryItem extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    public function serviceRecordItems(): HasMany
    {
        return $this->hasMany(ServiceRecordItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }
}
