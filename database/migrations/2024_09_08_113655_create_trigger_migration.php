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
        DB::unprepared("
            CREATE TRIGGER assign_sort_id_before_insert
            BEFORE INSERT ON learning_units
            FOR EACH ROW
            BEGIN
                DECLARE new_sort_id INT;
                
                -- Find the lowest available sortId starting from 1
                SET new_sort_id = 1;
                
                WHILE EXISTS (SELECT 1 FROM learning_units WHERE sortId = new_sort_id) DO
                    SET new_sort_id = new_sort_id + 1;
                END WHILE;
                
                -- Assign the calculated sortId to the new row
                SET NEW.sortId = new_sort_id;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trigger_migration');
    }
};
