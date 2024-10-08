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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->string('type');
            $table->text('optionA')->nullable();
            $table->text('optionB')->nullable();
            $table->text('optionC')->nullable();
            $table->unsignedBigInteger('levelId');
            $table->timestamps();

            $table->foreign('levelId')->references('id')->on('levels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
