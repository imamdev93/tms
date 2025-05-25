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
            $table->bigInteger('province_id_unit')->nullable();
            $table->bigInteger('city_id_unit')->nullable();
            $table->bigInteger('subdistrict_id_unit')->nullable();
            $table->string('blood_type')->nullable();
            $table->text('history_illness')->nullable();
            $table->string('club_name')->nullable();
            $table->string('coach_name')->nullable();
            $table->string('status')->nullable();
            $table->string('start_year')->nullable();
            $table->text('competition_participied')->nullable();
            $table->text('emergency_contact_name')->nullable();
            $table->string('relation')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('signature_file_path')->nullable();
            $table->string('photo_file_path')->nullable();

            $table->foreign('province_id_unit')->references('id')->on('provinces');
            $table->foreign('city_id_unit')->references('id')->on('cities');
            $table->foreign('subdistrict_id_unit')->references('id')->on('subdistricts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('province_id_unit');
            $table->dropColumn('city_id_unit');
            $table->dropColumn('subdistrict_id_unit');
            $table->dropColumn('blood_type');
            $table->dropColumn('history_illness');
            $table->dropColumn('club_name');
            $table->dropColumn('coach_name');
            $table->dropColumn('status');
            $table->dropColumn('start_year');
            $table->dropColumn('competition_participied');
            $table->dropColumn('emergency_contact_name');
            $table->dropColumn('emergency_contact_phone');
            $table->dropColumn('relation');
            $table->dropColumn('emergency_phone');
            $table->dropColumn('parent_name');
            $table->dropColumn('parent_phone');
            $table->dropColumn('signature_file_path');
            $table->dropColumn('photo_file_path');
        });
    }
};
