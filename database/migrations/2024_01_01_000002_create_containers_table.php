<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->enum('type', ['general', 'recyclable', 'organic', 'hazardous', 'electronic'])->default('general');
            $table->decimal('capacity', 8, 2)->comment('in liters');
            $table->decimal('fill_level', 5, 2)->default(0)->comment('percentage 0-100');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('address')->nullable();
            $table->text('address_ar')->nullable();
            $table->string('zone')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'full'])->default('active');
            $table->timestamp('last_emptied_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->json('sensor_data')->nullable();
            $table->string('rfid_tag')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']);
            $table->index('status');
            $table->index('zone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
