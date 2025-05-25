<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasUuids;

    protected $table = 'profiles';

    public $fillable = [
        'name',
        'registration_number',
        'user_id',
        'birth_date',
        'address',
        'gender',
        'province_id',
        'city_id',
        'subdistrict_id',
        'unit_id',
        'current_belt_level_id',
        'registration_date',
        'blood_type',
        'history_illness',
        'club_name',
        'coach_name',
        'status',
        'start_year',
        'competition_participied',
        'emergency_contact_name',
        'relation',
        'emergency_contact_phone',
        'parent_name',
        'parent_phone',
        'signature_file_path',
        'photo_file_path',
        'photo_file_path',
        'identity_number',
        'birth_place',
        'registration_date',
        'registration_type',
        'organization_level',
        'approval_status',
        'organization_province_id',
        'organization_city_id',
        'dojang',
        'postal_code',
        'belt_rank',
        'member_type',
        'is_registration',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function belt()
    {
        return $this->belongsTo(BeltLevel::class, 'current_belt_level_id', 'id');
    }
}
