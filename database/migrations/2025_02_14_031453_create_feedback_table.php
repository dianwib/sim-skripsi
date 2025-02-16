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
        Schema::create('feedback', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('description')->nullable();
            $table->string('external_link', 255)->nullable();

            $table->ulid('thesis_supervision_id');
            $table->foreign('thesis_supervision_id')->references('id')->on('thesis_supervisions')->cascadeOnDelete()->cascadeOnUpdate();

            $table->ulid('thesis_supervision_file_id');
            $table->foreign('thesis_supervision_file_id')->references('id')->on('thesis_supervision_files')->cascadeOnDelete()->cascadeOnUpdate();

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
        Schema::dropIfExists('feedback');
    }
};
