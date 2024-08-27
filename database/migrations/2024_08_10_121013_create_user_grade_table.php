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
        Schema::create('user_grade', function (Blueprint $table) {
            // Defining the composite primary key
            $table->string('user_phoneNumber');
            $table->unsignedBigInteger('level_id');
            $table->integer('score');
            $table->timestamps();

            // Setting the composite primary key
            $table->primary(['user_phoneNumber', 'level_id']);

            // Foreign keys
            $table->foreign('user_phoneNumber')->references('phoneNumber')->on('users')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_grade');
    }
};
