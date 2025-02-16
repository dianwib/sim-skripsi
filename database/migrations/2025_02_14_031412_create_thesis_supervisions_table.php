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
        Schema::create('thesis_supervisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('external_link', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('count_revision', 255)->nullable();


            $table->ulid('thesis_id');
            $table->foreign('thesis_id')->references('id')->on('thesis')->cascadeOnDelete()->cascadeOnUpdate();

            $table->ulid('student_id');
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete()->cascadeOnUpdate();

            $table->ulid('lecturer_id');
            $table->foreign('lecturer_id')->references('id')->on('lecturers')->cascadeOnDelete()->cascadeOnUpdate();




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
        Schema::dropIfExists('thesis_supervisions');
    }
};
