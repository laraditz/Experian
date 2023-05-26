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
        Schema::create('experian_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action', 100)->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('error_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experian_requests');
    }
};
