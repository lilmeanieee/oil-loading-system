<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('haulers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 500);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // The four sample haulers (ids 1 to 4), so the sample trucks and drivers that still live in the
        // browser keep pointing at a real hauler (H1 to H4) until those move to the database too.
        $now = now();
        DB::table('haulers')->insert([
            ['id' => 1, 'name' => 'Mindanao Fuel Haulers Inc.', 'contact' => 'Ramon Dela Cruz', 'phone' => '0917 555 0142', 'address' => 'Brgy. Calumpang, General Santos City', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Sarangani Bay Tankers', 'contact' => 'Liza Mangubat', 'phone' => '0918 555 0177', 'address' => 'Poblacion, Alabel, Sarangani', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Soccsksargen Petro Logistics', 'contact' => 'Eduardo Tan', 'phone' => '0922 555 0119', 'address' => 'Zone III, Koronadal City', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Pacific Gulf Transport', 'contact' => 'Marites Ong', 'phone' => '0929 555 0164', 'address' => 'Brgy. Labangal, General Santos City', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('haulers');
    }
};