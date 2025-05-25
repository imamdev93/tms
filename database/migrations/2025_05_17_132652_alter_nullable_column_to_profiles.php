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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->change();
            $table->date('registration_date')->nullable()->change();
            $table->bigInteger('current_belt_level_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('registration_number')->change();
            $table->date('registration_date')->change();
            $table->bigInteger('current_belt_level_id')->change();
        });
    }
};
