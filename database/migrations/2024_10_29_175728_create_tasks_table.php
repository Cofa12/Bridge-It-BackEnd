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
            $table->enum('status',['new','in_progress','done','canceled'])->default('new');
            $table->enum('doc_status',['confirm','canceled','waiting'])->default('waiting');
            $table->string('doc_notes')->nullable();
            $table->date('start_date');
            $table->date('deadline_date');
            $table->foreignId('author_id')->references('id')->on('users');
            $table->foreignId('assigned_to')->references('id')->on('users');
            $table->foreignId('challenge_id')->nullable()->references('id')->on('challenges');
            $table->foreignId('group_id')->references('id')->on('groups');
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
