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
        Schema::create('template_user', function (Blueprint $table) {
            $table->foreignId('template_id')->references('id')->on('templates')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->primary(['template_id', 'user_id']);
            $table->integer('voting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_user');
    }
};
