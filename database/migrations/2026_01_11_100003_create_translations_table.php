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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->index();
            $table->string('group')->index(); // e.g., 'nav', 'shop', 'auth'
            $table->string('key')->index(); // e.g., 'login', 'register'
            $table->text('value');
            $table->timestamps();

            $table->unique(['locale', 'group', 'key']);
            $table->foreign('locale')->references('code')->on('languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
