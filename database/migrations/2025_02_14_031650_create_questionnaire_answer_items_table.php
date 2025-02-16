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
        Schema::create('questionnaire_answer_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('aswer', 255)->nullable();
            $table->ulid('questionnaire_answer_id');
            $table->foreign('questionnaire_answer_id')->references('id')->on('questionnaire_answers')->cascadeOnDelete()->cascadeOnUpdate();


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
        Schema::dropIfExists('questionnaire_answer_items');
    }
};
