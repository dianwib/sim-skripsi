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
        Schema::create('thesis_supervision_files', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('external_link', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 255)->nullable();


            $table->ulid('thesis_supervision_id');
            $table->foreign('thesis_supervision_id')->references('id')->on('thesis_supervisions')->cascadeOnDelete()->cascadeOnUpdate();

            $table->ulid('thesis_supervision_file_id')->nullable();

            $table->ulid('previous_thesis_supervision_file_id')->nullable();


            $table->boolean('is_revision')->default(false);
            $table->string('revision_number', 255)->nullable();



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
        Schema::dropIfExists('thesis_supervision_files');
    }
};
