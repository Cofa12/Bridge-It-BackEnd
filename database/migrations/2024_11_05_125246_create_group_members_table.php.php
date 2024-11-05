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
        Schema::create('group_members', function (Blueprint $table) {
            $table->foreignId('group_id')->references('id')->on('groups');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->primary(['group_id','user_id']);
            $table->enum('position',['admin','member']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_members');
    }
};
