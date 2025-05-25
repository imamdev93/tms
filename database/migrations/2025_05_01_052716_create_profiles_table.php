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
        Schema::disableForeignKeyConstraints();

        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('registration_number')->unique();
            $table->uuid('user_id')->index();
            $table->date('birth_date');
            $table->text('address');
            $table->string('gender');
            $table->bigInteger('province_id')->index();
            $table->foreign('province_id')->references('id')->on('provinces');
            $table->bigInteger('city_id')->index();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->bigInteger('subdistrict_id')->index();
            $table->foreign('subdistrict_id')->references('id')->on('subdistricts');
            $table->bigInteger('unit_id')->index();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->bigInteger('current_belt_level_id')->index();
            $table->foreign('current_belt_level_id')->references('id')->on('belt_levels');
            $table->date('registration_date');

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
