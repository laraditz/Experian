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
        Schema::create('experian_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ref_no', 100)->nullable();
            $table->json('ccris_search')->nullable();
            $table->json('ccris_entity')->nullable();
            $table->json('ccris_report')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experian_records');
    }
};
