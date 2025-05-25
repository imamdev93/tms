<?php

namespace App\Filament\Resources;

use App\BloodTypeEnum;
use App\Filament\Resources\MemberResource\Pages;
use App\GenderTypeEnum;
use App\MemberTypeEnum;
use App\Models\BeltLevel;
use App\Models\City;
use App\Models\Member;
use App\Models\Province;
use App\OrganizationLevelEnum;
use App\RegistrationTypeEnum;
use App\RelationEnum;
use App\RoleEnum;
use App\StatusEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Akun')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    TextInput::make('email')
                                        ->label(fn () => new HtmlString('Email<br><small class="italic text-gray-500">Email</small>'))
                                        ->placeholder('Masukan Alamat Email Aktif')
                                        ->email()
                                        ->validationMessages([
                                            'required' => 'Alamat Email wajib diisi.',
                                            'unique' => 'Alamat Email sudah digunakan.',
                                        ])
                                        ->required()
                                        ->unique('users', 'email', ignoreRecord: true)
                                        ->columnSpan(6),
                                    TextInput::make('password')
                                        ->label(fn () => new HtmlString('Password<br><small class="italic text-gray-500">Password</small>'))
                                        ->placeholder('Masukan Password Terdiri Dari Angka, Huruf Kecil dan Besar.')
                                        ->password()
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Password wajib diisi.',
                                        ])
                                        ->hidden(fn ($livewire) => filled($livewire->record))
                                        ->columnSpan(6),
                                ]),
                        ])->hidden(fn ($livewire) => filled($livewire->record)),
                    Step::make('Data Pribadi')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    TextInput::make('name')
                                        ->label(fn () => new HtmlString('Nama<br><small class="italic text-gray-500">Name</small>'))
                                        ->placeholder('Masukan Nama Lengkap')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Nama wajib diisi.',
                                        ])
                                        ->columnSpan(4),
                                    TextInput::make('identity_number')
                                        ->label(fn () => new HtmlString('Nomor KTP / SIM / Kartu Pelajar<br><small class="italic text-gray-500">Identity Number</small>'))
                                        ->placeholder('Masukan Nomor KTP / SIM / Kartu Pelajar')
                                        ->required()
                                        ->unique('profiles', 'identity_number', ignoreRecord: true)
                                        ->validationMessages([
                                            'required' => 'Nomor KTP / SIM / Kartu Pelajar wajib diisi.',
                                            'unique' => 'Nomor KTP / SIM / Kartu Pelajar sudah digunakan.',
                                        ])
                                        ->columnSpan(4),
                                    Radio::make('gender')
                                        ->label(fn () => new HtmlString('Jenis Kelamin<br><small class="italic text-gray-500">Gender</small>'))
                                        ->options(
                                            collect(GenderTypeEnum::cases())
                                                ->mapWithKeys(fn ($gender) => [$gender->value => $gender->getLabel()])
                                                ->toArray()
                                        )->required()
                                        ->validationMessages([
                                            'required' => 'Jenis Kelamin wajib diisi.',
                                        ])
                                        ->inline()
                                        ->inlineLabel(false)
                                        ->columnSpan(4),
                                ]),
                            Grid::make(12)
                                ->schema([
                                    TextInput::make('birth_place')
                                        ->label(fn () => new HtmlString('Tempat Lahir<br><small class="italic text-gray-500">Birth Place</small>'))
                                        ->placeholder('Masukan Tempat Lahir (Kota / Kabupaten)')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Tempat Lahir wajib diisi.',
                                        ])
                                        ->columnSpan(6),
                                    DatePicker::make('birth_date')
                                        ->label(fn () => new HtmlString('Tanggal Lahir<br><small class="italic text-gray-500">Birth Date</small>'))
                                        ->placeholder('Masukan Tanggal Lahir')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Tanggal Lahir wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->columnSpan(6),
                                ]),
                            Grid::make(12)
                                ->schema([
                                    Select::make('province_id')
                                        ->label(fn () => new HtmlString('Provinsi<br><small class="italic text-gray-500">Province</small>'))
                                        ->placeholder('Pilih Provinsi Domisili')
                                        ->options(Province::pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Provinsi wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('city_id', null))
                                        ->columnSpan(4),

                                    Select::make('city_id')
                                        ->label(fn () => new HtmlString('Kota/Kabupaten<br><small class="italic text-gray-500">City/Regency</small>'))
                                        ->placeholder('Pilih Kota/Kabupaten')
                                        ->options(function (callable $get) {
                                            $provinceId = $get('province_id');
                                            if (! $provinceId) {
                                                return [];
                                            }

                                            return City::where('province_id', $provinceId)->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Kota/Kabupaten wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('subdistrict_id', null))
                                        ->columnSpan(4),

                                    TextInput::make('postal_code')
                                        ->label(fn () => new HtmlString('Kode Pos<br><small class="italic text-gray-500">Postal Code</small>'))
                                        ->placeholder('Masukan Kode Pos')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Kode Pos wajib diisi.',
                                        ])
                                        ->columnSpan(4),
                                ]),
                            Textarea::make('address')
                                ->label(fn () => new HtmlString('Alamat<br><small class="italic text-gray-500">Address</small>'))
                                ->placeholder('Masukan Alamat Tempat Tinggal Sekarang')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Alamat wajib diisi.',
                                ])
                                ->columnSpanFull(),
                        ]),
                    Step::make('Informasi Pendaftaran')
                        ->schema([
                            Grid::make(12)->schema([
                                Select::make('registration_type')
                                    ->label(fn () => new HtmlString('Daftar Sebagai<br><small class="italic text-gray-500">Registration As</small>'))
                                    ->placeholder('Pilih Opsi Pendaftaran')
                                    ->options(
                                        collect(RegistrationTypeEnum::cases())
                                            ->mapWithKeys(fn ($level) => [$level->value => $level->getLabel()])
                                            ->toArray()
                                    )->required()
                                    ->validationMessages([
                                        'required' => 'Daftar Sebagai wajib diisi.',
                                    ])
                                    ->searchable()
                                    ->reactive()
                                    ->columnSpan(6),
                                Radio::make('organization_level')
                                    ->label(fn () => new HtmlString('Wilayah<br><small class="italic text-gray-500">Region </small>'))
                                    ->options(
                                        collect(OrganizationLevelEnum::cases())
                                            ->mapWithKeys(fn ($level) => [$level->value => $level->getLabel()])
                                            ->toArray()
                                    )->validationMessages([
                                        'required' => 'Wilayah wajib diisi.',
                                    ])
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->columnSpan(6),
                            ]),
                            Grid::make(12)
                                ->schema([
                                    Select::make('organization_province_id')
                                        ->label(fn () => new HtmlString('Provinsi<br><small class="italic text-gray-500">Province</small>'))
                                        ->placeholder('Pilih Provinsi')
                                        ->options(Province::pluck('name', 'id'))
                                        ->searchable()
                                        ->required(fn (callable $get) => $get('organization_level') !== OrganizationLevelEnum::PUSAT->value)
                                        ->validationMessages([
                                            'required' => 'Provinsi wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('organization_city_id', null))
                                        ->columnSpan(4),

                                    Select::make('organization_city_id')
                                        ->label(fn () => new HtmlString('Kota/Kabupaten<br><small class="italic text-gray-500">City/Regency</small>'))
                                        ->placeholder('Pilih Kota/Kabupaten')
                                        ->options(function (callable $get) {
                                            $provinceId = $get('organization_province_id');
                                            $cityId = $get('organization_city_id');

                                            // Jika provinsi belum dipilih tapi city_id sudah ada, tampilkan kota tersebut
                                            if (! $provinceId && $cityId) {
                                                return City::where('id', $cityId)->pluck('name', 'id');
                                            }

                                            if (! $provinceId) {
                                                return [];
                                            }

                                            return City::where('province_id', $provinceId)->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->required(fn (callable $get) => in_array($get('organization_level'), [OrganizationLevelEnum::KOTA->value, OrganizationLevelEnum::DOJANG->value]))
                                        ->validationMessages([
                                            'required' => 'Kota/Kabupaten wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->columnSpan(4),

                                    TextInput::make('dojang')
                                        ->label(fn () => new HtmlString('Klub (Dojang)<br><small class="italic text-gray-500">Training Ground</small>'))
                                        ->placeholder('Masukan Klub (Dojang)')
                                        ->required(fn (callable $get) => $get('organization_level') == OrganizationLevelEnum::DOJANG->value)
                                        ->validationMessages([
                                            'required' => 'Dojang wajib diisi.',
                                        ])
                                        ->columnSpan(4),
                                ]),
                        ]),
                    Step::make('Data Orang Tua')
                        ->schema([
                            TextInput::make('parent_name')
                                ->label(fn () => new HtmlString('Nama Orang Tua / Wali<br><small class="italic text-gray-500">Parent Name</small>'))
                                ->placeholder('Masukan Nama Orang Tua / Wali'),
                            TextInput::make('parent_phone')
                                ->label(fn () => new HtmlString('Nomor Telepon Orang Tua / Wali<br><small class="italic text-gray-500">Parent Phone Number</small>'))
                                ->placeholder('Masukan Nomor Telepon Orang Tua / Wali'),
                        ])->visible(
                            fn (Get $get) => ($get('registration_type') === RoleEnum::ATLET->value) &&
                                (optional($get('birth_date'))
                                    ? \Carbon\Carbon::parse($get('birth_date'))->age < 17
                                    : false)
                        ),
                    Step::make('Informasi Kesehatan')
                        ->schema([
                            Radio::make('blood_type')
                                ->label(fn () => new HtmlString('Golongan Darah<br><small class="italic text-gray-500">Blood Type</small>'))
                                ->options(
                                    collect(BloodTypeEnum::cases())
                                        ->mapWithKeys(fn ($bloodType) => [$bloodType->value => $bloodType->getLabel()])
                                        ->toArray()
                                )->required()
                                ->validationMessages([
                                    'required' => 'Golongan Darah wajib diisi.',
                                ])
                                ->inline()
                                ->inlineLabel(false),
                            Textarea::make('history_illness')
                                ->label(fn () => new HtmlString('Riwayat Penyakit/Alergi<br><small class="italic text-gray-500">History Illness</small>'))
                                ->placeholder('Masukan Riwayat Penyakit/Alergi'),
                        ])->visible(
                            fn (Get $get) => ($get('registration_type') === RoleEnum::ATLET->value)
                        ),
                    Step::make('Data Taekwondo')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    // TextInput::make('registration_number')
                                    //     ->label(fn() => new HtmlString('ID Anggota Taekwondo<br><small class="italic text-gray-500">Taekwondo Identity Number</small>'))
                                    //     ->placeholder('Isi jika sudah memiliki ID Anggota')
                                    //     ->unique(ignoreRecord: true)
                                    //     ->columnSpan(4),
                                    TextInput::make('club_name')
                                        ->label(fn () => new HtmlString('Nama Klub<br><small class="italic text-gray-500">Club Name</small>'))
                                        ->placeholder('Masukan Nama Klub')
                                        ->label('Nama Klub')
                                        ->columnSpan(6),
                                    TextInput::make('coach_name')
                                        ->label(fn () => new HtmlString('Nama Pelatih<br><small class="italic text-gray-500">Coach Name</small>'))
                                        ->placeholder('Masukan Nama Pelatih')
                                        ->columnSpan(6),
                                ]),
                            Grid::make(12)->schema([
                                Select::make('current_belt_level_id')
                                    ->label(fn () => new HtmlString('Sabuk Saat Ini<br><small class="italic text-gray-500">Current Belt Level</small>'))
                                    ->options(BeltLevel::pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->columnSpan(4),
                                // TextInput::make('belt_rank')
                                //     ->label(fn() => new HtmlString('Dan Ke-<br><small class="italic text-gray-500">Belt Rank</small>'))
                                //     ->placeholder('Level Dan dari 1 - 10')
                                //     ->numeric()
                                //     ->minLength(1)
                                //     ->maxLength(10)
                                //     ->columnSpan(4),
                                TextInput::make('start_year')
                                    ->label(fn () => new HtmlString('Tahun Mulai<br><small class="italic text-gray-500">Start Year</small>'))
                                    ->placeholder('Masukan Tahun Mulai Taekwondo')
                                    ->columnSpan(4),
                                Radio::make('member_type')
                                    ->label(fn () => new HtmlString('Status Keanggotaan<br><small class="italic text-gray-500">Member Status</small>'))
                                    ->options(
                                        collect(MemberTypeEnum::cases())
                                            ->mapWithKeys(fn ($status) => [$status->value => $status->getLabel()])
                                            ->toArray()
                                    )
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->columnSpan(4),
                            ]),
                            Textarea::make('competition_participied')
                                ->label(fn () => new HtmlString('Kompetisi yang pernah diikuti<br><small class="italic text-gray-500">Competition Participied</small>'))
                                ->placeholder('Masukan Kompetisi yang pernah diikuti'),
                        ])->visible(
                            fn (Get $get) => ($get('registration_type') === RoleEnum::ATLET->value ?? false)
                        ),
                    Step::make('Kontak Darurat')
                        ->schema([
                            TextInput::make('emergency_contact_name')
                                ->label(fn () => new HtmlString('Nama Kontak Darurat<br><small class="italic text-gray-500">Emergency Contact Name</small>'))
                                ->placeholder('Masukan Nama Kontak Darurat')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Nama Kontak Darurat wajib diisi.',
                                ]),
                            Select::make('relation')
                                ->label(fn () => new HtmlString('Hubungan<br><small class="italic text-gray-500">Relation</small>'))
                                ->placeholder('Pilih Hubungan')
                                ->options(
                                    collect(RelationEnum::cases())
                                        ->mapWithKeys(fn ($relation) => [$relation->value => $relation->getLabel()])
                                        ->toArray()
                                )->required()
                                ->validationMessages([
                                    'required' => 'Hubungan wajib diisi.',
                                ]),
                            TextInput::make('emergency_contact_phone')
                                ->label(fn () => new HtmlString('No Telepon Darurat<br><small class="italic text-gray-500">Emergency Contact Phone</small>'))
                                ->placeholder('Masukan Nomor Kontak Darurat')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Nomor Kontak Darurat wajib diisi.',
                                ]),
                        ])->visible(
                            fn (Get $get) => ($get('registration_type') === RoleEnum::ATLET->value)
                        ),
                    Step::make('Lampiran')
                        ->schema([
                            FileUpload::make('signature_file_path')
                                ->label(fn () => new HtmlString('Foto Tanda Tangan<br><small class="italic text-gray-500">Signature Photo</small>'))
                                ->disk('public')
                                ->directory('signatures')
                                ->preserveFilenames(false)  // Jangan simpan dengan nama asli
                                ->afterStateUpdated(function ($state) {
                                    // Menyimpan path file setelah di-upload
                                    if ($state) {

                                        foreach ($state as $file) {
                                            $originalName = $file->getClientOriginalName();

                                            // Sanitasi nama file: lowercase, hilangkan karakter aneh
                                            $sanitizedFilename = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', str_replace(' ', '', $originalName)));

                                            // Ambil bagian nama dan ekstensi
                                            $nameOnly = Str::beforeLast($sanitizedFilename, '.');
                                            $extension = Str::afterLast($sanitizedFilename, '.');

                                            // Tambahkan flag dan timestamp
                                            $timestamp = now()->format('YmdHis'); // contoh: 20250508153012
                                            $finalFilename = "{$nameOnly}_{$timestamp}.{$extension}";

                                            // Tentukan path penyimpanan
                                            $file->storeAs('signatures', $finalFilename, 'public');
                                        }
                                    }
                                })
                                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                ->downloadable()
                                ->maxSize(2048) // max 2MB
                                ->openable()
                                ->previewable(),
                            FileUpload::make('photo_file_path')
                                ->label(fn () => new HtmlString('Foto Profil<br><small class="italic text-gray-500">Profile Photo</small>'))
                                ->disk('public')
                                ->directory('photos')
                                ->preserveFilenames(false)  // Jangan simpan dengan nama asli
                                ->afterStateUpdated(function ($state) {
                                    // Menyimpan path file setelah di-upload
                                    if ($state) {

                                        foreach ($state as $file) {
                                            $originalName = $file->getClientOriginalName();

                                            // Sanitasi nama file: lowercase, hilangkan karakter aneh
                                            $sanitizedFilename = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', str_replace(' ', '', $originalName)));

                                            // Ambil bagian nama dan ekstensi
                                            $nameOnly = Str::beforeLast($sanitizedFilename, '.');
                                            $extension = Str::afterLast($sanitizedFilename, '.');

                                            // Tambahkan flag dan timestamp
                                            $timestamp = now()->format('YmdHis'); // contoh: 20250508153012
                                            $finalFilename = "{$nameOnly}_{$timestamp}.{$extension}";

                                            // Tentukan path penyimpanan

                                            $file->storeAs('photos', $finalFilename, 'public');
                                        }
                                    }
                                })
                                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                ->downloadable()
                                ->maxSize(2048) // max 2MB
                                ->openable()
                                ->previewable(),
                            DatePicker::make('registration_date')
                                ->label(fn () => new HtmlString('Tanggal Registrasi<br><small class="italic text-gray-500">Registration Date</small>'))
                                ->default(now()),
                        ])->visible(
                            fn (Get $get) => ($get('registration_type') === RoleEnum::ATLET->value)
                        ),
                ])->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Member::query();

                $user = auth()->user();
                $isPengurus = $user->role == RoleEnum::PENGURUS->value;

                if ($isPengurus) {

                    // as pengurus provinsi
                    if ($user->organization_level == OrganizationLevelEnum::PROVINSI->value) {
                        $query->whereIn('registration_type', [RegistrationTypeEnum::PELATIH->value, RegistrationTypeEnum::ATLET->value])
                            ->where('organization_province_id', $user->member?->organization_province_id);
                    }

                    // as pengurus kota
                    if ($user->organization_level == OrganizationLevelEnum::KOTA->value) {
                        $query->whereIn('registration_type', [RegistrationTypeEnum::PELATIH->value, RegistrationTypeEnum::ATLET->value])
                            ->where('organization_city_id', $user->member?->organization_city_id);
                    }
                }

                return $query;
            })
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama'),
                TextColumn::make('registration_type')
                    ->label('Daftar Sebagai'),
                TextColumn::make('status')
                    ->label('Status Keanggotaan')
                    ->badge()
                    ->color(fn (string $state): string => StatusEnum::tryFrom($state)?->color() ?? 'secondary')
                    ->formatStateUsing(fn (string $state): string => StatusEnum::tryFrom($state)?->getLabel() ?? $state),
                TextColumn::make('member_type')
                    ->label('Jenis Member')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MemberTypeEnum::tryFrom($state)?->getLabel() ?? $state),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()->role, [RoleEnum::SUPERADMIN->value, RoleEnum::ADMIN->value, RoleEnum::PENGURUS->value]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
            'view' => Pages\ViewMember::route('/{record}'),
        ];
    }
}
