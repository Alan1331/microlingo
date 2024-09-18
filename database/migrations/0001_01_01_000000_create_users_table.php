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
        Schema::create('users', function (Blueprint $table) {
            $table->string('phoneNumber')->unique();
            $table->string('name')->nullable();
            $table->string('occupation')->nullable();
            $table->string('menuLocation');
            $table->string('progress');
            $table->integer('progressPercentage')->nullable();
            $table->string('currentGrade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
