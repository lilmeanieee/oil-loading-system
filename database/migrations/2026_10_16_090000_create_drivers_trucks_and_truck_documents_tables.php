<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hauler_id')->constrained('haulers')->restrictOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->unique(['hauler_id', 'name']);
        });

        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 20)->unique();
            $table->foreignId('hauler_id')->constrained('haulers')->restrictOnDelete();
            // Each driver has one truck. The substitute can cover more than one truck.
            $table->foreignId('driver_id')->nullable()->unique()->constrained('drivers')->nullOnDelete();
            $table->foreignId('substitute_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->unsignedInteger('capacity');
            $table->json('compartments'); // liters per compartment, for example [4000,4000,5000]
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // One row per document type (or = Official Receipt, cr = Certificate of Registration, cal = Calibration).
        // The file itself is kept in storage/app/uploads/TruckMasterFiles/..., only its path is stored here.
        Schema::create('truck_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained('trucks')->cascadeOnDelete();
            $table->string('type', 3);
            $table->date('expiry_date');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['truck_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_documents');
        Schema::dropIfExists('trucks');
        Schema::dropIfExists('drivers');
    }
};