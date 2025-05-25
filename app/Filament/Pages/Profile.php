<?php

namespace App\Filament\Pages;

use App\BloodTypeEnum;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationLabel = 'Profil';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.pages.profile';

    protected static bool $shouldRegisterNavigation = true;

    public $profile;

    public $user;

    public $account;

    public $signature_file_path;

    public $photo_file_path;

    protected static ?string $model = Member::class;

    public static function canAccess(): bool
    {
        return ! in_array(auth()->user()->role, [RoleEnum::SUPERADMIN->value, RoleEnum::ADMIN->value]);
    }

    public function mount(): void
    {
        $this->user = Auth::user();

        $this->form->fill([
            'profile' => $this->user->member?->toArray(),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Profile Tabs')
                ->tabs([
                    Tab::make('Data Diri')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    TextInput::make('profile.name')
                                        ->label(fn() => new HtmlString('Nama<br><small class="italic text-gray-500">Name</small>'))
                                        ->placeholder('Masukan Nama Lengkap')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Nama wajib diisi.',
                                        ])
                                        ->columnSpan(4),
                                    TextInput::make('profile.identity_number')
                                        ->label(fn() => new HtmlString('Nomor KTP / SIM / Kartu Pelajar<br><small class="italic text-gray-500">Identity Number</small>'))
                                        ->placeholder('Masukan Nomor KTP / SIM / Kartu Pelajar')
                                        ->rules([
                                            function () {
                                                $profileId = $this->user->member?->id;

                                                return Rule::unique('profiles', 'identity_number')
                                                    ->ignore($profileId);
                                            },
                                        ])
                                        ->validationMessages([
                                            'required' => 'Nomor KTP / SIM / Kartu Pelajar wajib diisi.',
                                            'unique' => 'Nomor KTP / SIM / Kartu Pelajar sudah digunakan.',
                                        ])
                                        ->columnSpan(4),
                                    Radio::make('profile.gender')
                                        ->label(fn() => new HtmlString('Jenis Kelamin<br><small class="italic text-gray-500">Gender</small>'))
                                        ->options(
                                            collect(GenderTypeEnum::cases())
                                                ->mapWithKeys(fn($gender) => [$gender->value => $gender->getLabel()])
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
                                    TextInput::make('profile.birth_place')
                                        ->label(fn() => new HtmlString('Tempat Lahir<br><small class="italic text-gray-500">Birth Place</small>'))
                                        ->placeholder('Masukan Tempat Lahir (Kota / Kabupaten)')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Tempat Lahir wajib diisi.',
                                        ])
                                        ->columnSpan(6),
                                    DatePicker::make('profile.birth_date')
                                        ->label(fn() => new HtmlString('Tanggal Lahir<br><small class="italic text-gray-500">Birth Date</small>'))
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
                                    Select::make('profile.province_id')
                                        ->label(fn() => new HtmlString('Provinsi<br><small class="italic text-gray-500">Province</small>'))
                                        ->placeholder('Pilih Provinsi Domisili')
                                        ->options(Province::pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Provinsi wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(fn(callable $set) => $set('profile.city_id', null))
                                        ->columnSpan(4),

                                    Select::make('profile.city_id')
                                        ->label(fn() => new HtmlString('Kota<br><small class="italic text-gray-500">City/Regency</small>'))
                                        ->placeholder('Pilih Kota/Kabupaten')
                                        ->options(function (callable $get) {
                                            $provinceId = $get('profile.province_id');
                                            $cityId = $get('profile.city_id');

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
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Kota/Kabupaten wajib diisi.',
                                        ])
                                        ->reactive()
                                        ->columnSpan(4),

                                    TextInput::make('profile.postal_code')
                                        ->label(fn() => new HtmlString('Kode Pos<br><small class="italic text-gray-500">Postal Code</small>'))
                                        ->placeholder('Masukan Kode Pos')
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Kode Pos wajib diisi.',
                                        ])
                                        ->columnSpan(4),
                                ]),
                            Textarea::make('profile.address')
                                ->label(fn() => new HtmlString('Alamat<br><small class="italic text-gray-500">Address</small>'))
                                ->placeholder('Masukan Alamat Tempat Tinggal Sekarang')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Alamat wajib diisi.',
                                ])
                                ->columnSpanFull(),
                        ]),
                    Tab::make('Data Orang Tua')
                        ->schema([
                            TextInput::make('profile.parent_name')
                                ->label(fn() => new HtmlString('Nama Orang Tua / Wali<br><small class="italic text-gray-500">Parent Name</small>'))
                                ->placeholder('Masukan Nama Orang Tua / Wali')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Nama Orang Tua / Wali wajib diisi.',
                                ]),
                            TextInput::make('profile.parent_phone')
                                ->label(fn() => new HtmlString('Nomor Telepon Orang Tua / Wali<br><small class="italic text-gray-500">Parent Phone Number</small>'))
                                ->placeholder('Masukan Nomor Telepon Orang Tua / Wali')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Nomor Telepon Orang Tua / Wali wajib diisi.',
                                ]),
                        ])->visible(
                            fn(Get $get) => optional($get('profile.birth_date'))
                                ? \Carbon\Carbon::parse($get('profile.birth_date'))->age < 17
                                : false
                        ),
                    Tab::make('Informasi Kesehatan')
                        ->schema([
                            Radio::make('profile.blood_type')
                                ->label(fn() => new HtmlString('Golongan Darah<br><small class="italic text-gray-500">Blood Type</small>'))
                                ->options(
                                    collect(BloodTypeEnum::cases())
                                        ->mapWithKeys(fn($bloodType) => [$bloodType->value => $bloodType->getLabel()])
                                        ->toArray()
                                )->required()
                                ->validationMessages([
                                    'required' => 'Golongan Darah wajib diisi.',
                                ])
                                ->inline()
                                ->inlineLabel(false),
                            Textarea::make('profile.history_illness')
                                ->label(fn() => new HtmlString('Riwayat Penyakit/Alergi<br><small class="italic text-gray-500">History Illness</small>'))
                                ->placeholder('Masukan Riwayat Penyakit/Alergi'),
                        ]),
                    Tab::make('Kontak Darurat')
                        ->schema([
                            TextInput::make('profile.emergency_contact_name')
                                ->label(fn() => new HtmlString('Nama Kontak Darurat<br><small class="italic text-gray-500">Emergency Contact Name</small>'))
                                ->placeholder('Masukan Nama Kontak Darurat')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Nama Kontak Darurat wajib diisi.',
                                ]),
                            Select::make('profile.relation')
                                ->label(fn() => new HtmlString('Hubungan<br><small class="italic text-gray-500">Relation</small>'))
                                ->placeholder('Pilih Hubungan')
                                ->options(
                                    collect(RelationEnum::cases())
                                        ->mapWithKeys(fn($relation) => [$relation->value => $relation->getLabel()])
                                        ->toArray()
                                )->required()
                                ->validationMessages([
                                    'required' => 'Hubungan wajib diisi.',
                                ]),
                            TextInput::make('profile.emergency_contact_phone')
                                ->label(fn() => new HtmlString('No Telepon Darurat<br><small class="italic text-gray-500">Emergency Contact Phone</small>'))
                                ->placeholder('Masukan Nomor Kontak Darurat')
                                ->required()
                                ->validationMessages([
                                    'required' => 'Nomor Kontak Darurat wajib diisi.',
                                ]),
                        ]),
                    Tab::make('Lampiran')
                        ->schema([
                            FileUpload::make('profile.signature_file_path')
                                ->label(fn() => new HtmlString('Foto Tanda Tangan<br><small class="italic text-gray-500">Signature Photo</small>'))
                                ->disk('public')
                                ->preserveFilenames(false)  // Jangan simpan dengan nama asli
                                ->afterStateUpdated(function ($state) {
                                    // Menyimpan path file setelah di-upload
                                    if ($state) {
                                        $originalName = pathinfo($state, PATHINFO_BASENAME);

                                        $nameOnly = pathinfo($originalName, PATHINFO_FILENAME);
                                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                                        $timestamp = now()->format('YmdHis');
                                        $finalFilename = "{$nameOnly}_{$timestamp}.{$extension}";

                                        Storage::disk('public')->move($state, "signatures/{$finalFilename}");
                                    }
                                })
                                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                ->downloadable()
                                ->maxSize(2048) // max 2MB
                                ->openable()
                                ->previewable()
                                // ->default($this->profile['signature_file_path'] ?? null)
                                ->directory('signatures'),
                            FileUpload::make('profile.photo_file_path')
                                ->label(fn() => new HtmlString('Foto Profil<br><small class="italic text-gray-500">Profile Photo</small>'))
                                ->disk('public')
                                ->directory('photos')
                                ->preserveFilenames(false)  // Jangan simpan dengan nama asli
                                ->afterStateUpdated(function ($state) {
                                    // Menyimpan path file setelah di-upload
                                    if ($state) {
                                        $originalName = pathinfo($state, PATHINFO_BASENAME);

                                        $nameOnly = pathinfo($originalName, PATHINFO_FILENAME);
                                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                                        $timestamp = now()->format('YmdHis');
                                        $finalFilename = "{$nameOnly}_{$timestamp}.{$extension}";

                                        Storage::disk('public')->move($state, "signatures/{$finalFilename}");
                                    }
                                })
                                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                ->downloadable()
                                ->maxSize(2048) // max 2MB
                                ->openable()
                                ->previewable(),

                            DatePicker::make('profile.registration_date')
                                ->label(fn() => new HtmlString('Tanggal Registrasi<br><small class="italic text-gray-500">Registration Date</small>'))
                                ->default(now()),
                        ]),
                ]),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        DB::beginTransaction();
        try {
            $this->user->update(['name' => $data['profile']['name']]);

            $this->user->member()->updateOrCreate(
                ['user_id' => $this->user->id],     // kondisi pencarian
                $data['profile']                    // data untuk update atau create
            );

            DB::commit();
            Notification::make()
                ->title('Data Profil berhasil diperbaharui.')
                ->success()
                ->send();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
