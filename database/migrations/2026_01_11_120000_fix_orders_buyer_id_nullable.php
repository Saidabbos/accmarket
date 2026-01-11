<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if buyer_id is already nullable
        $column = DB::selectOne("
            SELECT IS_NULLABLE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'orders'
            AND COLUMN_NAME = 'buyer_id'
        ");

        if ($column && $column->IS_NULLABLE === 'NO') {
            // Get foreign key name
            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'orders'
                AND COLUMN_NAME = 'buyer_id'
                AND REFERENCED_TABLE_NAME = 'users'
            ");

            // Drop existing foreign key if exists
            if ($fk) {
                DB::statement("ALTER TABLE orders DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }

            // Make buyer_id nullable
            DB::statement('ALTER TABLE orders MODIFY buyer_id BIGINT UNSIGNED NULL');

            // Re-add foreign key with ON DELETE SET NULL
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_buyer_id_foreign FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL');
        }

        // Add guest columns if not exists
        if (!Schema::hasColumn('orders', 'guest_email')) {
            DB::statement('ALTER TABLE orders ADD COLUMN guest_email VARCHAR(255) NULL AFTER buyer_id');
        }

        if (!Schema::hasColumn('orders', 'guest_token')) {
            DB::statement('ALTER TABLE orders ADD COLUMN guest_token VARCHAR(64) NULL AFTER guest_email');

            // Add unique index if not exists
            $indexExists = DB::selectOne("
                SELECT COUNT(*) as cnt
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'orders'
                AND INDEX_NAME = 'orders_guest_token_unique'
            ");

            if (!$indexExists || $indexExists->cnt == 0) {
                DB::statement('ALTER TABLE orders ADD UNIQUE INDEX orders_guest_token_unique (guest_token)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a fix, no need to reverse
    }
};
