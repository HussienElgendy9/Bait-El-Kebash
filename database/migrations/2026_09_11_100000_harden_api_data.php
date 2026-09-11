<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preflight before any DDL: never choose an owner or silently repair historical data.
        if (DB::table('users')->select('phone_number')->groupBy('phone_number')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate user phone numbers require operator review before migration.');
        }
        foreach (DB::table('order_items')->orderBy('id')->cursor() as $item) {
            if (! preg_match('/^[1-9][0-9]*$/', $item->quantity) || (int) $item->quantity > 2147483647
                || ! preg_match('/^[0-9]{1,14}(\.[0-9]{1,2})?$/', $item->price_snapshot)) {
                throw new RuntimeException('Invalid historical order item '.$item->id.' requires review before migration.');
            }
        }
        Schema::table('users', fn (Blueprint $table) => $table->unique('phone_number'));
        Schema::table('phone_verifications', fn (Blueprint $table) => $table->string('code', 255)->change());
        DB::table('phone_verifications')->update(['code' => '', 'invalidated_at' => now()]);
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_name')->nullable();
            $table->string('delivery_phone', 20)->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('idempotency_key', 100)->nullable();
            $table->string('request_hash', 64)->nullable();
            $table->unique(['user_id', 'idempotency_key']);
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
            $table->decimal('price_snapshot', 16, 2)->change();
            $table->string('product_name')->nullable();
            $table->string('product_unit')->nullable();
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Forward-only safety migration: restore a reviewed backup instead of discarding snapshots or restoring cascades.');
    }
};
