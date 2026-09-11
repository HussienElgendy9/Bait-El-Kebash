<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_verifications', function (Blueprint $table) {
            $table->timestamp('invalidated_at')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('phone_verifications', fn (Blueprint $table) => $table->dropColumn('invalidated_at'));
    }
};
