<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_flash_message_renders_globally_after_a_mutating_action(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.inventory.store'), [
            'name' => 'Air Filter',
            'sku' => 'AIR-999',
            'quantity' => 5,
            'unit_price' => 15,
            'reorder_level' => 2,
        ]);

        $response->assertRedirect(route('admin.inventory.index'));

        $this->actingAs($admin)
            ->get(route('admin.inventory.index'))
            ->assertSee('Inventory item added successfully.');
    }

    public function test_breeze_status_slugs_are_not_leaked_as_raw_text_on_profile_page(): void
    {
        $user = User::factory()->create()->assignRole('customer');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('profile.edit'));

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('profile-updated');
    }
}
