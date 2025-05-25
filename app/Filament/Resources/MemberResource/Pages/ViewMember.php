<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\ApprovalStatusEnum;
use App\Filament\Resources\MemberResource;
use App\GenderTypeEnum;
use App\Models\BeltLevel;
use App\Models\City;
use App\Models\Province;
use App\OrganizationLevelEnum;
use App\RegistrationTypeEnum;
use App\StatusEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMember extends ViewRecord
{
    // protected static string $view = 'filament.pages.view-member'; // View Blade untuk halaman

    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['approval_status' => ApprovalStatusEnum::APPROVED->value, 'status' => StatusEnum::ACTIVE->value]);
                    Notification::make()
                        ->title('Member diterima')
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['approval_status' => ApprovalStatusEnum::REJECTED->value, 'status' => StatusEnum::INACTIVE->value]);
                    Notification::make()
                        ->title('Member ditolak')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function makeInfolist(): Infolist
    {
        return Infolist::make()
            ->record($this->record)
            ->schema([
                Tabs::make('Member Info')
                    ->tabs([
                        Tab::make('Data Diri Member')->schema([
                            Section::make('Informasi Pribadi')->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('name')
                                        ->badge()
                                        ->label('Nama'),
                                    TextEntry::make('identity_number')
                                        ->badge()
                                        ->label('Nomor KTP / SIM / Kartu Pelajar'),
                                    TextEntry::make('user.email')
                                        ->badge()
                                        ->label('Email'),
                                    TextEntry::make('gender')
                                        ->badge()
                                        ->label('Jenis Kelamin')
                                        ->formatStateUsing(fn ($state) => GenderTypeEnum::tryFrom($state)?->getLabel() ?? '-'),
                                ]),
                            ]),
                            Section::make('Tempat & Tanggal Lahir')->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('birth_place')
                                        ->badge()
                                        ->label('Tempat Lahir'),
                                    TextEntry::make('birth_date')
                                        ->badge()
                                        ->label('Tanggal Lahir')
                                        ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->translatedFormat('d F Y')),
                                ]),
                            ]),
                            Section::make('Alamat')->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('province_id')
                                        ->badge()
                                        ->label('Provinsi')
                                        ->formatStateUsing(function ($state) {
                                            // Mengambil nama provinsi berdasarkan ID
                                            $province = Province::find($state);

                                            return $province ? $province->name : '-'; // Jika tidak ada provinsi, tampilkan '-'
                                        }),
                                    TextEntry::make('city_id')
                                        ->badge()
                                        ->label('Kota')
                                        ->formatStateUsing(function ($state) {
                                            // Mengambil nama provinsi berdasarkan ID
                                            $city = City::find($state);

                                            return $city ? $city->name : '-'; // Jika tidak ada provinsi, tampilkan '-'
                                        }),
                                    TextEntry::make('address')
                                        ->badge()
                                        ->label('Alamat'),
                                    TextEntry::make('postal_code')
                                        ->badge()
                                        ->label('Kode Pos'),
                                ]),
                            ]),
                        ]),
                        Tab::make('Data Pendaftaran')->schema([
                            Section::make('Informasi Pendaftaran')->schema([
                                Grid::make(5)->schema([
                                    TextEntry::make('registration_type')
                                        ->badge()
                                        ->label('Daftar Sebagai')
                                        ->formatStateUsing(fn ($state) => RegistrationTypeEnum::tryFrom($state)?->getLabel() ?? '-'),
                                    TextEntry::make('organization_level')
                                        ->badge()
                                        ->label('Wilayah')
                                        ->formatStateUsing(fn ($state) => OrganizationLevelEnum::tryFrom($state)?->getLabel() ?? '-'),
                                    TextEntry::make('organization_province_id')
                                        ->badge()
                                        ->label('Provinsi')
                                        ->formatStateUsing(function ($state) {
                                            // Mengambil nama provinsi berdasarkan ID
                                            $data = Province::find($state);

                                            return $data ? $data->name : '-'; // Jika tidak ada provinsi, tampilkan '-'
                                        }),
                                    TextEntry::make('organization_city_id')
                                        ->badge()
                                        ->label('Kota')
                                        ->formatStateUsing(function ($state) {
                                            // Mengambil nama provinsi berdasarkan ID
                                            $data = City::find($state);

                                            return $data ? $data->name : '-'; // Jika tidak ada provinsi, tampilkan '-'
                                        }),
                                    TextEntry::make('dojang')
                                        ->badge()
                                        ->label('Dojang'),
                                ]),
                            ]),
                        ]),
                        Tab::make('Data Orang Tua / Wali')->schema([
                            Section::make('Informasi Orang Tua / Wali')->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('parent_name')
                                        ->badge()
                                        ->label('Nama Orang Tua / Wali'),
                                    TextEntry::make('parent_phone')
                                        ->badge()
                                        ->label('No Telepon Orang Tua / Wali'),
                                ]),
                            ]),
                        ]),
                        Tab::make('Data Kesehatan')->schema([
                            Section::make('Informasi Kesehatan')->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('blood_type')
                                        ->badge()
                                        ->label('Golongan Darah'),
                                    TextEntry::make('history_illness')
                                        ->badge()
                                        ->label('Riwayat Penyakit / Alergi'),
                                ]),
                            ]),
                        ]),
                        Tab::make('Data Taekwondo')->schema([
                            Section::make('Informasi Taekwondo')->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('club_name')
                                        ->badge()
                                        ->label('Nama Klub'),
                                    TextEntry::make('coach_name')
                                        ->badge()
                                        ->label('Nama Pelatih'),
                                ]),
                                Grid::make(2)->schema([
                                    TextEntry::make('current_belt_level_id')
                                        ->badge()
                                        ->label('Sabuk Saat Ini')
                                        ->formatStateUsing(function ($state) {
                                            // Mengambil nama provinsi berdasarkan ID
                                            $data = BeltLevel::find($state);

                                            return $data ? $data->name : '-'; // Jika tidak ada provinsi, tampilkan '-'
                                        }),
                                    TextEntry::make('belt_rank')
                                        ->badge()
                                        ->label('Tingkatan ke'),
                                ]),
                                Grid::make(2)->schema([
                                    TextEntry::make('approval_status')
                                        ->badge()
                                        ->label('Status Approval')
                                        ->color(fn (string $state): string => ApprovalStatusEnum::tryFrom($state)?->color() ?? 'secondary')
                                        ->formatStateUsing(fn (string $state): string => StatusEnum::tryFrom($state)?->getLabel() ?? $state),
                                    TextEntry::make('status')
                                        ->badge()
                                        ->label('Status Keanggotaan')
                                        ->color(fn (string $state): string => StatusEnum::tryFrom($state)?->color() ?? 'secondary')
                                        ->formatStateUsing(fn (string $state): string => StatusEnum::tryFrom($state)?->getLabel() ?? $state),
                                ]),
                                Grid::make(2)->schema([
                                    TextEntry::make('start_year')
                                        ->badge()
                                        ->label('Tahun Mulai'),
                                    TextEntry::make('competition_participied')
                                        ->badge()
                                        ->label('Partisipasi Kompetisi yang diikuti'),
                                ]),
                            ]),
                        ]),
                        Tab::make('Data Lampiran')->schema([
                            Section::make('Informasi Lampiran')->schema([
                                Grid::make(3)->schema([
                                    ImageEntry::make('signature_file_path') // ganti sesuai nama field fotomu
                                        ->label('Foto Tanda Tangan')
                                        ->getStateUsing(fn ($record) => asset('storage/'.$record->signature_file_path))
                                        ->extraAttributes([
                                            'style' => 'width: 100%; height: auto; object-fit: contain;',
                                        ]),
                                    ImageEntry::make('photo_file_path') // ganti sesuai nama field fotomu
                                        ->label('Pas Foto')
                                        ->getStateUsing(fn ($record) => asset('storage/'.$record->photo_file_path))
                                        ->extraAttributes([
                                            'style' => 'width: 100%; height: auto; object-fit: contain;',
                                        ]),
                                    TextEntry::make('registration_date')
                                        ->badge()
                                        ->label('Tanggal Registrasi')
                                        ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->translatedFormat('d F Y')),
                                ]),
                            ]),
                        ]),
                    ]),
            ]);
    }
}
