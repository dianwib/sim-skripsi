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
        Schema::create('thesis', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('last_activity', 255)->nullable();
            $table->string('count_supervision', 255)->nullable();

            $table->ulid('student_id');
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete()->cascadeOnUpdate();

            $table->ulid('lecturer_1_id');
            $table->foreign('lecturer_1_id')->references('id')->on('lecturers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->ulid('lecturer_2_id');
            $table->foreign('lecturer_2_id')->references('id')->on('lecturers')->cascadeOnDelete()->cascadeOnUpdate();


            $table->ulid('created_by')->nullable();
            $table->ulid('updated_by')->nullable();
            $table->ulid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesis');
    }
};
