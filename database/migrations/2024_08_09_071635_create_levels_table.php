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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->nullable();
            $table->string('content')->nullable();
            $table->integer('sortId');
            $table->string('videoLink')->nullable();
            $table->unsignedBigInteger('unitId');
            $table->timestamps();

            $table->foreign('unitId')->references('id')->on('learning_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
