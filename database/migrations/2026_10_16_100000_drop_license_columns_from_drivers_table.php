<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('drivers', 'license_no')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropUnique(['license_no']);
                $table->dropColumn('license_no');
            });
        }

        if (Schema::hasColumn('drivers', 'license_expiry')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('license_expiry');
            });
        }
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('license_no', 50)->nullable()->unique();
            $table->date('license_expiry')->nullable();
        });
    }
};