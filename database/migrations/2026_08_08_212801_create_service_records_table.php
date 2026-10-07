<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('service_bookings')->nullOnDelete();
            $table->foreignId('mechanic_id')->nullable()->constrained('mechanics')->nullOnDelete();
            $table->text('description');
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->dateTime('serviced_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
