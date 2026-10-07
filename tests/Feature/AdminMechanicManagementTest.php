<?php

namespace Tests\Feature;

use App\Models\Mechanic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMechanicManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_mechanic_account(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.mechanics.store'), [
            'name' => 'Jamie Mechanic',
            'email' => 'jamie@example.com',
            'phone' => '555-1234',
            'specialty' => 'Brakes',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('admin.mechanics.index'));

        $user = User::where('email', 'jamie@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('mechanic'));

        $mechanic = Mechanic::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals('Brakes', $mechanic->specialty);
        $this->assertEquals('active', $mechanic->status);
    }

    public function test_mechanic_creation_requires_a_unique_email(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)->post(route('admin.mechanics.store'), [
            'name' => 'Jamie Mechanic',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertEquals(1, User::where('email', 'taken@example.com')->count());
    }

    public function test_non_admin_cannot_access_mechanic_management(): void
    {
        $customer = User::factory()->create()->assignRole('customer');
        $mechanic = User::factory()->create()->assignRole('mechanic');

        $this->actingAs($customer)
            ->get(route('admin.mechanics.index'))
            ->assertForbidden();

        $this->actingAs($mechanic)
            ->post(route('admin.mechanics.store'), [
                'name' => 'Should Fail',
                'email' => 'shouldfail@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertForbidden();
    }
}
