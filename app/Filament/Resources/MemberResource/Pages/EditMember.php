<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        DB::beginTransaction();

        try {
            $data['user_id'] = $record->user_id;

            $record->update($data); // update ke tabel utama

            // optional update relasi user
            $record->user->update(['name' => $data['name']]);

            DB::commit(); // jika semua berhasil

            return $record;
        } catch (Exception $e) {
            DB::rollBack(); // rollback DULU sebelum tangani error

            report($e); // untuk logging ke Laravel log
            throw $e;   // biar tetap muncul error-nya
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
