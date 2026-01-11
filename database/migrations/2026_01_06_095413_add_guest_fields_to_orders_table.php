<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add guest email and token fields
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('buyer_id');
            }
            if (!Schema::hasColumn('orders', 'guest_token')) {
                $table->string('guest_token', 64)->nullable()->unique()->after('guest_email');
            }
        });

        // Make buyer_id nullable using raw SQL (works without doctrine/dbal)
        // First drop the foreign key constraint
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['buyer_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist or have different name
        }

        // Modify column to be nullable
        DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NULL');

        // Re-add foreign key constraint with ON DELETE SET NULL
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('buyer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
        });

        DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NOT NULL');

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('buyer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->dropColumn(['guest_email', 'guest_token']);
        });
    }
};
