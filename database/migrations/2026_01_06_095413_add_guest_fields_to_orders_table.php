<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Make buyer_id nullable for guest checkout
            $table->foreignId('buyer_id')->nullable()->change();

            // Add guest email field
            $table->string('guest_email')->nullable()->after('buyer_id');

            // Add guest token for order access without login
            $table->string('guest_token', 64)->nullable()->unique()->after('guest_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['guest_email', 'guest_token']);
            $table->foreignId('buyer_id')->nullable(false)->change();
        });
    }
};
