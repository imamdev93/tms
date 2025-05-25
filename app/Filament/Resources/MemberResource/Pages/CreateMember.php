<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Member;
use App\Models\User;
use App\StatusEnum;
use Exception;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        DB::beginTransaction();

        $memberId = $this->generateMemberId($data['province_id'], $data['city_id']);

        try {
            $user = User::create([
                'name' => $data['name'],   // atau ambil dari input lain
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['registration_type'],
            ]);

            // Tambahkan user_id ke data Member
            $data['user_id'] = $user->id;
            $data['registration_number'] = $memberId;
            $data['status'] = StatusEnum::INACTIVE->value;

            // Hapus field yang hanya milik User
            $data = Arr::except($data, ['email', 'password', 'role']);

            $member = static::getModel()::create($data);

            DB::commit();

            return $member;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generateMemberId(int $provinceId, int $cityId): string
    {
        return DB::transaction(function () use ($provinceId, $cityId) {
            // Ambil prefix
            $prefix = sprintf('INA-%02d-%04d-', $provinceId, $cityId);

            // Cari nomor terakhir yang sudah dipakai dengan prefix tersebut
            $lastNumber = Member::where('registration_number', 'like', $prefix.'%')
                ->lockForUpdate() // Lock baris agar tidak race condition
                ->orderByDesc('registration_number')
                ->value('registration_number');

            // Ambil angka terakhir (7 digit terakhir)
            $next = 1;
            if ($lastNumber) {
                $lastDigits = intval(substr($lastNumber, -7));
                $next = $lastDigits + 1;
            }

            // Format akhir
            return $prefix.str_pad($next, 7, '0', STR_PAD_LEFT);
        });
    }
}
