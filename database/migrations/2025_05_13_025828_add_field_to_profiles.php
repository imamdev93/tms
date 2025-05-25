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
            $table->string('registration_type')->nullable();
            $table->string('organization_level')->nullable();
            $table->string('approval_status')->nullable();
            $table->bigInteger('organization_province_id')->nullable();
            $table->bigInteger('organization_city_id')->nullable();
            $table->string('dojang')->nullable();
            $table->string('postal_code')->nullable();
            $table->integer('belt_rank')->nullable();

            $table->foreign('organization_province_id')->references('id')->on('provinces');
            $table->foreign('organization_city_id')->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('registration_type');
            $table->dropColumn('organization_level');
            $table->dropColumn('approval_status');
            $table->dropColumn('organization_province_id');
            $table->dropColumn('organization_city_id');
            $table->dropColumn('dojang');
            $table->dropColumn('postal_code');
            $table->dropColumn('belt_rank');
        });
    }
};
