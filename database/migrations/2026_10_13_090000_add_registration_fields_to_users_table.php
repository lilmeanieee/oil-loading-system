<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // pending = waiting for admin, active = can sign in, inactive = switched off, rejected = request refused.
            // Existing accounts keep working because the default is "active".
            $table->string('status', 20)->default('active')->after('role')->index();
            $table->string('requested_role', 30)->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('requested_role');
            $table->foreignId('reviewed_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->timestamp('last_login_at')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'requested_role', 'rejection_reason', 'reviewed_at', 'last_login_at']);
        });
    }
};