<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('model');
            $table->string('brand');
            $table->year('year');
            $table->enum('type', ['truck', 'mini_truck', 'compactor', 'tipper', 'suction'])->default('truck');
            $table->decimal('capacity', 8, 2)->comment('tons');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'on_route'])->default('active');
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            $table->decimal('fuel_level', 5, 2)->nullable()->comment('percentage');
            $table->string('fuel_type')->default('diesel');
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->string('insurance_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->json('gps_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('plate_number');
        });

        // Drivers
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('license_number')->unique();
            $table->enum('license_class', ['A', 'B', 'C', 'D'])->default('C');
            $table->date('license_expiry');
            $table->date('hire_date');
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active');
            $table->string('avatar')->nullable();
            $table->decimal('rating', 3, 2)->default(5.0);
            $table->integer('total_trips')->default(0);
            $table->text('address')->nullable();
            $table->string('national_id')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // Dumpsites
        Schema::create('dumpsites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('address')->nullable();
            $table->enum('type', ['landfill', 'transfer_station', 'recycling_center', 'composting'])->default('landfill');
            $table->enum('status', ['active', 'inactive', 'full', 'maintenance'])->default('active');
            $table->decimal('total_capacity', 12, 2)->comment('in tons');
            $table->decimal('current_fill', 12, 2)->default(0)->comment('in tons');
            $table->decimal('fill_percentage', 5, 2)->default(0);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->json('accepted_waste_types')->nullable();
            $table->json('boundary_polygon')->nullable()->comment('GeoJSON polygon');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']);
        });

        // Routes
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dumpsite_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned');
            $table->enum('frequency', ['daily', 'alternate', 'weekly', 'monthly', 'on_demand'])->default('daily');
            $table->json('waypoints')->nullable()->comment('Array of container IDs');
            $table->json('geojson_path')->nullable()->comment('Route GeoJSON');
            $table->decimal('total_distance', 10, 2)->nullable()->comment('km');
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->decimal('actual_distance', 10, 2)->nullable();
            $table->integer('actual_duration')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('start_lat', 10, 8)->nullable();
            $table->decimal('start_lng', 11, 8)->nullable();
            $table->decimal('end_lat', 10, 8)->nullable();
            $table->decimal('end_lng', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('scheduled_at');
        });

        // Route containers pivot
        Schema::create('route_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('container_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->enum('status', ['pending', 'collected', 'skipped'])->default('pending');
            $table->timestamp('collected_at')->nullable();
            $table->text('notes')->nullable();
        });

        // Complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('complainant_name');
            $table->string('complainant_phone')->nullable();
            $table->string('complainant_email')->nullable();
            $table->enum('category', [
                'missed_collection', 'damaged_container', 'illegal_dumping',
                'odor', 'noise', 'hazardous_waste', 'other'
            ])->default('other');
            $table->string('subject');
            $table->text('description');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('address')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'rejected'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
            $table->index('category');
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('text')->comment('text, number, boolean, json, color, file');
            $table->string('label')->nullable();
            $table->string('label_ar')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('group');
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_containers');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('dumpsites');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
    }
};
