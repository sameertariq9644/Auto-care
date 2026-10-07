<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_a_vehicle_and_gets_a_qr_code(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');

        $response = $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'ABC-123',
            'vin' => null,
        ]);

        $vehicle = Vehicle::firstOrFail();

        $response->assertRedirect(route('vehicles.show', $vehicle));
        $this->assertEquals($customer->id, $vehicle->user_id);
        $this->assertNotEmpty($vehicle->qr_uuid);
        $this->assertNotEmpty($vehicle->qr_code_path);

        Storage::disk('public')->assertExists($vehicle->qr_code_path);
    }

    public function test_customer_can_select_multiple_services_when_adding_a_vehicle(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'ABC-123',
            'services' => ['Oil Change', 'Tire Rotation'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['Oil Change', 'Tire Rotation'], Vehicle::firstOrFail()->services);
    }

    public function test_customer_can_upload_a_vehicle_photo(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'ABC-123',
            'image' => UploadedFile::fake()->image('car.jpg', 800, 500),
        ])->assertSessionHasNoErrors();

        $vehicle = Vehicle::firstOrFail();

        $this->assertNotNull($vehicle->image_path);
        Storage::disk('public')->assertExists($vehicle->image_path);

        $this->actingAs($customer)->get(route('vehicles.index'))
            ->assertOk()
            ->assertSee($vehicle->image_url);
    }

    public function test_uploaded_photo_replaces_the_placeholder_on_every_page(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'PIC-001',
            'image' => UploadedFile::fake()->image('car.jpg', 800, 500),
        ])->assertSessionHasNoErrors();

        $vehicle = Vehicle::firstOrFail();

        foreach ([route('customer.dashboard'), route('vehicles.index'), route('vehicles.show', $vehicle), route('bookings.create')] as $url) {
            $this->actingAs($customer)->get($url)
                ->assertOk()
                ->assertSee('src="'.$vehicle->image_url.'"', false)
                ->assertDontSee('id="body'.$vehicle->id.'"', false);
        }
    }

    public function test_placeholder_is_shown_when_no_photo_was_uploaded(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'PIC-002',
        ])->assertSessionHasNoErrors();

        $vehicle = Vehicle::firstOrFail();

        $this->assertNull($vehicle->image_url);

        $this->actingAs($customer)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('id="body'.$vehicle->id.'"', false)
            ->assertDontSee('/storage/vehicles/', false);

        foreach ([route('vehicles.index'), route('vehicles.show', $vehicle), route('bookings.create')] as $url) {
            $this->actingAs($customer)->get($url)
                ->assertOk()
                ->assertDontSee('/storage/vehicles/', false);
        }
    }

    public function test_replacing_and_deleting_a_vehicle_photo_cleans_up_old_files(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');
        $old = UploadedFile::fake()->image('old.jpg')->store('vehicles', 'public');
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'plate_number' => 'XYZ-999',
            'qr_uuid' => (string) Str::uuid(),
            'image_path' => $old,
        ]);

        $this->actingAs($customer)->put(route('vehicles.update', $vehicle), [
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'plate_number' => $vehicle->plate_number,
            'image' => UploadedFile::fake()->image('new.png'),
        ])->assertSessionHasNoErrors();

        $new = $vehicle->fresh()->image_path;
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);

        $this->actingAs($customer)->delete(route('vehicles.destroy', $vehicle));

        Storage::disk('public')->assertMissing($new);
    }

    public function test_vehicle_photo_must_be_an_image(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'ABC-123',
            'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('image');
    }

    public function test_vehicle_services_must_be_from_the_known_list(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'ABC-123',
            'services' => ['Teleportation'],
        ])->assertSessionHasErrors('services.0');

        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_customer_can_view_the_add_vehicle_form(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)
            ->get(route('vehicles.create'))
            ->assertOk();
    }

    public function test_customer_cannot_view_another_customers_vehicle(): void
    {
        $owner = User::factory()->create()->assignRole('customer');
        $intruder = User::factory()->create()->assignRole('customer');

        $vehicle = Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'plate_number' => 'XYZ-999',
            'qr_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($intruder)
            ->get(route('vehicles.show', $vehicle))
            ->assertForbidden();
    }

    public function test_mechanic_cannot_access_vehicle_routes(): void
    {
        $mechanic = User::factory()->create()->assignRole('mechanic');

        $this->actingAs($mechanic)
            ->get(route('vehicles.index'))
            ->assertForbidden();
    }

    public function test_customer_can_update_their_own_vehicle(): void
    {
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'plate_number' => 'UPD-001',
            'qr_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($customer)
            ->put(route('vehicles.update', $vehicle), [
                'make' => 'Honda',
                'model' => 'Accord',
                'year' => 2021,
                'plate_number' => 'UPD-001',
                'vin' => null,
            ])
            ->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertEquals('Accord', $vehicle->fresh()->model);
        $this->assertEquals(2021, $vehicle->fresh()->year);
    }

    public function test_customer_cannot_update_another_customers_vehicle(): void
    {
        $owner = User::factory()->create()->assignRole('customer');
        $intruder = User::factory()->create()->assignRole('customer');
        $vehicle = Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'plate_number' => 'UPD-002',
            'qr_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($intruder)
            ->put(route('vehicles.update', $vehicle), [
                'make' => 'Honda',
                'model' => 'Hacked',
                'year' => 2021,
                'plate_number' => 'UPD-002',
                'vin' => null,
            ])
            ->assertForbidden();

        $this->assertEquals('Civic', $vehicle->fresh()->model);
    }

    public function test_deleting_a_vehicle_removes_its_qr_code_file(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)->post('/vehicles', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'DEL-001',
            'vin' => null,
        ]);

        $vehicle = Vehicle::firstOrFail();
        $qrPath = $vehicle->qr_code_path;
        Storage::disk('public')->assertExists($qrPath);

        $this->actingAs($customer)
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertModelMissing($vehicle);
        Storage::disk('public')->assertMissing($qrPath);
    }

    public function test_customer_cannot_delete_another_customers_vehicle(): void
    {
        $owner = User::factory()->create()->assignRole('customer');
        $intruder = User::factory()->create()->assignRole('customer');
        $vehicle = Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'plate_number' => 'DEL-002',
            'qr_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($intruder)
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertForbidden();

        $this->assertModelExists($vehicle);
    }

    public function test_anyone_can_view_public_vehicle_history_page_via_qr_uuid(): void
    {
        $owner = User::factory()->create()->assignRole('customer');

        $vehicle = Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'plate_number' => 'HIST-001',
            'qr_uuid' => (string) Str::uuid(),
        ]);

        $this->get(route('vehicles.history', $vehicle->qr_uuid))
            ->assertOk()
            ->assertSee('No service history recorded yet.');
    }
}
