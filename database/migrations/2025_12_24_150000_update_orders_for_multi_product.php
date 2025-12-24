<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Make product_id nullable for multi-product orders
            $table->foreignId('product_id')->nullable()->change();

            // Add quantity and unit_price as nullable for multi-product orders
            $table->integer('quantity')->nullable()->change();
            $table->decimal('unit_price', 10, 2)->nullable()->change();

            // Add payment URL for NowPayments
            $table->string('payment_url')->nullable()->after('payment_method');
            $table->string('nowpayments_id')->nullable()->after('payment_url');
        });

        // Update order_items table to include quantity and price per product
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('order_id');
            $table->integer('quantity')->default(1)->after('product_id');
            $table->decimal('price', 10, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'nowpayments_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'quantity', 'price']);
        });
    }
};
