<?php

namespace Tests\Unit;

use App\Models\InventoryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_low_stock_is_true_when_quantity_is_at_or_below_reorder_level(): void
    {
        $atThreshold = $this->makeItem(quantity: 5, reorderLevel: 5);
        $belowThreshold = $this->makeItem(quantity: 1, reorderLevel: 5);

        $this->assertTrue($atThreshold->isLowStock());
        $this->assertTrue($belowThreshold->isLowStock());
    }

    public function test_is_low_stock_is_false_when_quantity_is_above_reorder_level(): void
    {
        $item = $this->makeItem(quantity: 10, reorderLevel: 5);

        $this->assertFalse($item->isLowStock());
    }

    public function test_decrementing_quantity_persists_to_the_database(): void
    {
        $item = $this->makeItem(quantity: 10, reorderLevel: 2);

        $item->decrement('quantity', 3);

        $this->assertEquals(7, $item->fresh()->quantity);
    }

    public function test_decrementing_below_the_reorder_level_flips_low_stock_status(): void
    {
        $item = $this->makeItem(quantity: 10, reorderLevel: 5);
        $this->assertFalse($item->isLowStock());

        $item->decrement('quantity', 6);

        $this->assertTrue($item->fresh()->isLowStock());
    }

    private function makeItem(int $quantity, int $reorderLevel): InventoryItem
    {
        return InventoryItem::create([
            'name' => 'Test Part',
            'sku' => 'SKU-'.uniqid(),
            'quantity' => $quantity,
            'unit_price' => 9.99,
            'reorder_level' => $reorderLevel,
        ]);
    }
}
