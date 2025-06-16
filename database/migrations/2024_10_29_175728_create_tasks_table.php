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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('status',['ToDo','Ongoing','Done','Canceled'])->default('ToDo');
            $table->enum('Urgency',['Later','Normal','Urgent'])->default('Later');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('deadline_date');
            $table->foreignId('author_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
