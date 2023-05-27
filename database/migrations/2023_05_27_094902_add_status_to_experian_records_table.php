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
        Schema::table('experian_records', function (Blueprint $table) {
            $table->smallInteger('status')->nullable()->after('ccris_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experian_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
