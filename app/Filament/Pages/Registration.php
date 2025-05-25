<?php

namespace App\Filament\Pages;

use App\BloodTypeEnum;
use App\GenderTypeEnum;
use App\MemberTypeEnum;
use App\Models\BeltLevel;
use App\Models\City;
use App\Models\Member;
use App\Models\PaymentCategory;
use App\Models\Province;
use App\Models\Transaction;
use App\OrganizationLevelEnum;
use App\PaymentStatusEnum;
use App\RegistrationTypeEnum;
use App\RelationEnum;
use App\RoleEnum;
use App\StatusEnum;
use App\TransactionTypeEnum;
use Carbon\Carbon;
use Error;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class Registration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $view = 'filament.pages.registration';

    protected static ?string $navigationGroup = 'Pendaftaran';

    protected static ?string $navigationLabel = 'Member';

    public $member;

    public $user;

    public $account;

    public $signature_file_path;

    public $photo_file_path;

    // protected static ?string $model = Member::class;

    public function mount(): void
    {
        $this->user = Auth::user();

        $this->form->fill([
            'member' => $this->user->member?->toArray(),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Data Pribadi')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                TextInput::make('member.name')
                                    ->label(fn() => new HtmlString('Nama<br><small class="italic text-gray-500">Name</small>'))
                                    ->placeholder('Masukan Nama Lengkap')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Nama wajib diisi.',
                                    ])
                                    ->columnSpan(4),
                                TextInput::make('member.identity_number')
                                    ->label(fn() => new HtmlString('Nomor KTP / SIM / Kartu Pelajar<br><small class="italic text-gray-500">Identity Number</small>'))
                                    ->placeholder('Masukan Nomor KTP / SIM / Kartu Pelajar')
                                    ->required()
                                    ->numeric()
                                    ->minLength(8)
                                    ->maxLength(16)
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
                                        'numeric' => 'Nomor KTP / SIM / Kartu Pelajar harus berupa angka.',
                                        'min_digits' => 'Nomor KTP / SIM / Kartu Pelajar minimal :min digit angka.',
                                        'max_digits' => 'Nomor KTP / SIM / Kartu Pelajar maksimal :max digit angka.',
                                    ])
                                    ->columnSpan(4),
                                Radio::make('member.gender')
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
                                TextInput::make('member.birth_place')
                                    ->label(fn() => new HtmlString('Tempat Lahir<br><small class="italic text-gray-500">Birth Place</small>'))
                                    ->placeholder('Masukan Tempat Lahir (Kota / Kabupaten)')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Tempat Lahir wajib diisi.',
                                    ])
                                    ->columnSpan(6),
                                DatePicker::make('member.birth_date')
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
                                Select::make('member.province_id')
                                    ->label(fn() => new HtmlString('Provinsi<br><small class="italic text-gray-500">Province</small>'))
                                    ->placeholder('Pilih Provinsi Domisili')
                                    ->options(Province::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Provinsi wajib diisi.',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('member.city_id', null))
                                    ->columnSpan(4),

                                Select::make('member.city_id')
                                    ->label(fn() => new HtmlString('Kota/Kabupaten<br><small class="italic text-gray-500">City/Regency</small>'))
                                    ->placeholder('Pilih Kota/Kabupaten')
                                    ->options(function (callable $get) {
                                        $provinceId = $get('member.province_id');
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
                                    ->afterStateUpdated(fn(callable $set) => $set('member.subdistrict_id', null))
                                    ->columnSpan(4),

                                TextInput::make('member.postal_code')
                                    ->label(fn() => new HtmlString('Kode Pos<br><small class="italic text-gray-500">Postal Code</small>'))
                                    ->placeholder('Masukan Kode Pos')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Kode Pos wajib diisi.',
                                    ])
                                    ->columnSpan(4),
                            ]),
                        Textarea::make('member.address')
                            ->label(fn() => new HtmlString('Alamat<br><small class="italic text-gray-500">Address</small>'))
                            ->placeholder('Masukan Alamat Tempat Tinggal Sekarang')
                            ->required()
                            ->validationMessages([
                                'required' => 'Alamat wajib diisi.',
                            ])
                            ->columnSpanFull(),
                    ])->disabled(fn(): bool => $this->isRegistrationComplete()),
                Step::make('Data Pendaftaran')
                    ->schema([
                        Grid::make(12)->schema([
                            Select::make('member.registration_type')
                                ->label(fn() => new HtmlString('Daftar Sebagai<br><small class="italic text-gray-500">Registration As</small>'))
                                ->placeholder('Pilih Opsi Pendaftaran')
                                ->options(
                                    collect(RegistrationTypeEnum::cases())
                                        ->mapWithKeys(fn($level) => [$level->value => $level->getLabel()])
                                        ->toArray()
                                )->required()
                                ->validationMessages([
                                    'required' => 'Daftar Sebagai wajib diisi.',
                                ])
                                ->searchable()
                                ->reactive()
                                ->columnSpan(6),
                            Radio::make('member.organization_level')
                                ->label(fn() => new HtmlString('Wilayah<br><small class="italic text-gray-500">Region </small>'))
                                ->options(
                                    collect(OrganizationLevelEnum::cases())
                                        ->mapWithKeys(fn($level) => [$level->value => $level->getLabel()])
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
                                Select::make('member.organization_province_id')
                                    ->label(fn() => new HtmlString('Provinsi<br><small class="italic text-gray-500">Province</small>'))
                                    ->placeholder('Pilih Provinsi')
                                    ->options(Province::pluck('name', 'id'))
                                    ->searchable()
                                    ->required(fn(callable $get) => $get('member.organization_level') !== OrganizationLevelEnum::PUSAT->value)
                                    ->validationMessages([
                                        'required' => 'Provinsi wajib diisi.',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('member.organization_city_id', null))
                                    ->columnSpan(4),

                                Select::make('member.organization_city_id')
                                    ->label(fn() => new HtmlString('Kota/Kabupaten<br><small class="italic text-gray-500">City/Regency</small>'))
                                    ->placeholder('Pilih Kota/Kabupaten')
                                    ->options(function (callable $get) {
                                        $provinceId = $get('member.organization_province_id');
                                        $cityId = $get('member.organization_city_id');

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
                                    ->required(fn(callable $get) => in_array($get('member.organization_level'), [OrganizationLevelEnum::KOTA->value, OrganizationLevelEnum::DOJANG->value]))
                                    ->validationMessages([
                                        'required' => 'Kota/Kabupaten wajib diisi.',
                                    ])
                                    ->reactive()
                                    ->columnSpan(4),

                                TextInput::make('member.dojang')
                                    ->label(fn() => new HtmlString('Klub (Dojang)<br><small class="italic text-gray-500">Training Ground</small>'))
                                    ->placeholder('Masukan Klub (Dojang)')
                                    ->required(fn(callable $get) => $get('member.organization_level') == OrganizationLevelEnum::DOJANG->value)
                                    ->validationMessages([
                                        'required' => 'Klub/Dojang wajib diisi.',
                                    ])
                                    ->columnSpan(4),
                            ]),
                    ])->disabled(fn(): bool => $this->isRegistrationComplete()),
                Step::make('Data Orang Tua')
                    ->schema([
                        TextInput::make('member.parent_name')
                            ->label(fn() => new HtmlString('Nama Orang Tua / Wali<br><small class="italic text-gray-500">Parent Name</small>'))
                            ->placeholder('Masukan Nama Orang Tua / Wali')
                            ->required()
                            ->validationMessages([
                                'required' => 'Nama Orang Tua / Wali wajib diisi.',
                            ]),
                        TextInput::make('member.parent_phone')
                            ->label(fn() => new HtmlString('Nomor Telepon Orang Tua / Wali<br><small class="italic text-gray-500">Parent Phone Number</small>'))
                            ->placeholder('Masukan Nomor Telepon Orang Tua / Wali')
                            ->required()
                            ->numeric()
                            ->minLength(9)
                            ->minLength(12)
                            ->validationMessages([
                                'required' => 'Nomor Telepon Orang Tua / Wali wajib diisi.',
                                'min_digits' => 'Nomor Telepon Orang Tua / Wali minimal :min angka',
                                'max_digits' => 'Nomor Telepon Orang Tua / Wali maksimal :max angka',
                            ]),
                    ])->visible(
                        fn($get) => ($get('member.registration_type') === RoleEnum::ATLET->value) &&
                            (optional($get('member.birth_date'))
                                ? \Carbon\Carbon::parse($get('member.birth_date'))->age < 17
                                : false)
                    )->disabled(fn(): bool => $this->isRegistrationComplete()),
                Step::make('Informasi Kesehatan')
                    ->schema([
                        Radio::make('member.blood_type')
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
                        Textarea::make('member.history_illness')
                            ->label(fn() => new HtmlString('Riwayat Penyakit/Alergi<br><small class="italic text-gray-500">History Illness</small>'))
                            ->placeholder('Masukan Riwayat Penyakit/Alergi'),
                    ])->visible(
                        fn($get) => ($get('registration_type') === RoleEnum::ATLET->value)
                    )->disabled(fn(): bool => $this->isRegistrationComplete()),
                Step::make('Data Taekwondo')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                TextInput::make('member.club_name')
                                    ->label(fn() => new HtmlString('Nama Klub<br><small class="italic text-gray-500">Club Name</small>'))
                                    ->placeholder('Masukan Nama Klub')
                                    ->columnSpan(6),
                                TextInput::make('member.coach_name')
                                    ->label(fn() => new HtmlString('Nama Pelatih<br><small class="italic text-gray-500">Coach Name</small>'))
                                    ->placeholder('Masukan Nama Pelatih')
                                    ->columnSpan(6),
                            ]),
                        Grid::make(12)->schema([
                            Radio::make('member.member_type')
                                ->label(fn() => new HtmlString('Status Pendaftaran<br><small class="italic text-gray-500">Registration Status</small>'))
                                ->options(
                                    collect(MemberTypeEnum::cases())
                                        ->mapWithKeys(fn($status) => [$status->value => $status->getLabel()])
                                        ->toArray()
                                )
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // Jika member_type adalah NEW, set sabuk putih (asumsi ID 1 adalah sabuk putih)
                                    if ($state === MemberTypeEnum::NEW->value) {
                                        $beltLevelId = BeltLevel::where('name', 'ilike', 'geup 10')->first()?->id;
                                        $set('member.current_belt_level_id', $beltLevelId); // Ganti dengan ID sabuk putih yang sesuai
                                    }
                                })
                                ->inline()
                                ->inlineLabel(false)
                                ->columnSpan(4),
                            Select::make('member.current_belt_level_id')
                                ->label(fn() => new HtmlString('Sabuk Saat Ini<br><small class="italic text-gray-500">Current Belt Level</small>'))
                                ->options(BeltLevel::pluck('name', 'id')->toArray())
                                ->searchable()
                                ->columnSpan(4),
                            TextInput::make('member.start_year')
                                ->label(fn() => new HtmlString('Tahun Mulai<br><small class="italic text-gray-500">Start Year</small>'))
                                ->placeholder('Masukan Tahun Mulai Taekwondo')
                                ->columnSpan(4),

                        ]),
                        Grid::make(12)
                            ->schema([
                                TextInput::make('member.status')
                                    ->label('Status Keanggotaan')
                                    ->formatStateUsing(fn(string $state): string => StatusEnum::tryFrom($state)?->getLabel() ?? $state)
                                    ->disabled()
                                    ->columnSpan(4),
                                TextInput::make('member.start_date')
                                    ->label('Tanggal Mulai')
                                    ->placeholder('Terupdate setelah status keanggotaan aktif')
                                    ->disabled()
                                    ->columnSpan(4),
                                TextInput::make('member.end_date')
                                    ->label('Tanggal Berakhir')
                                    ->placeholder('Terupdate setelah status keanggotaan aktif')
                                    ->columnSpan(4)
                                    ->disabled()
                            ]),
                        Textarea::make('member.competition_participied')
                            ->label(fn() => new HtmlString('Kompetisi yang pernah diikuti<br><small class="italic text-gray-500">Competition Participied</small>'))
                            ->placeholder('Masukan Kompetisi yang pernah diikuti'),
                    ])->disabled(fn(): bool => $this->isRegistrationComplete()),
                Step::make('Kontak Darurat')
                    ->schema([
                        TextInput::make('member.emergency_contact_name')
                            ->label(fn() => new HtmlString('Nama Kontak Darurat<br><small class="italic text-gray-500">Emergency Contact Name</small>'))
                            ->placeholder('Masukan Nama Kontak Darurat')
                            ->required()
                            ->validationMessages([
                                'required' => 'Nama Kontak Darurat wajib diisi.',
                            ]),
                        Select::make('member.relation')
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
                        TextInput::make('member.emergency_contact_phone')
                            ->label(fn() => new HtmlString('No Telepon Darurat<br><small class="italic text-gray-500">Emergency Contact Phone</small>'))
                            ->placeholder('Masukan Nomor Kontak Darurat')
                            ->required()
                            ->validationMessages([
                                'required' => 'Nomor Kontak Darurat wajib diisi.',
                            ]),
                    ])->disabled(fn(): bool => $this->isRegistrationComplete()),
                Step::make('Pernyataan')
                    ->schema([
                        FileUpload::make('member.signature_file_path')
                            ->label(fn() => new HtmlString('Foto Tanda Tangan<br><small class="italic text-gray-500">Signature Photo</small>'))
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
                        FileUpload::make('member.photo_file_path')
                            ->label(fn() => new HtmlString('Foto Profil<br><small class="italic text-gray-500">Profile Photo</small>'))
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
                        DatePicker::make('member.registration_date')
                            ->label(fn() => new HtmlString('Tanggal Registrasi<br><small class="italic text-gray-500">Registration Date</small>'))
                            ->displayFormat('d-m-Y') // Format tampilan
                            ->format('Y-m-d') // Format penyimpanan di database
                            ->default(now()),
                    ])->disabled(fn(): bool => $this->isRegistrationComplete()),

            ])
                ->submitAction(
                    Action::make('submitRegistration')
                        ->label('Daftar')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi')
                        ->action('submitRegistration')
                        ->disabled($this->isRegistrationComplete())
                )
                ->columnSpanFull(),
        ];
    }

    public function submitRegistration()
    {
        $data = $this->form->getState();

        DB::beginTransaction();
        try {
            $this->user->update(['name' => $data['member']['name']]);

            $memberId = $data['member']['registration_number'] ?? $this->generateMemberId($data['member']);
            $registrationDate = $data['member']['registration_date'] ?? now()->format('Y-m-d');

            $data['member']['registration_number'] = $memberId;
            $data['member']['registration_date'] = $registrationDate;
            $data['member']['status'] = StatusEnum::INACTIVE->value;
            $data['member']['is_registration'] = true;

            $this->user->member()->updateOrCreate(
                ['user_id' => $this->user->id],     // kondisi pencarian
                $data['member']                    // data untuk update atau create
            );

            // cek kategori pembayaran
            $paymentCategory = PaymentCategory::where('type', $data['member']['member_type'])->first();

            if (!$paymentCategory) {
                throw new Error('Kategori Pembayaran tidak ditemukan');
            }

            $transaction = Transaction::create([
                'user_id' => $this->user->id,
                'amount' => $paymentCategory?->nominal,
                'type' => TransactionTypeEnum::NEW->value,
                'order_id' => Str::uuid(),
                'payment_category_id' => $paymentCategory->id,
                'status' => PaymentStatusEnum::PENDING->value,
            ]);

            $params = [
                'transaction_details' => [
                    'order_id' => Str::uuid(),
                    'gross_amount' => (int) $transaction?->amount,
                ],
                'customer_details' => [
                    'first_name' => $this->user?->name,
                    'email' => $this->user?->email,
                ],
                'expiry' => [
                    'start_time' => Carbon::now()->format('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => config('services.midtrans.expire_time'),
                ],
            ];

            $midtrans = app('midtrans');
            $snapToken = $midtrans->createTransaction($params)->token;

            $paymentUrl = config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken
                : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;

            $transaction->update([
                'snap_token' => $snapToken,
                'payment_url' => $paymentUrl,
                'status' => PaymentStatusEnum::PROCESSING->value,
            ]);

            DB::commit();

            Notification::make()
                ->title('Registrasi Berhasil')
                // ->modalDescription('Silahkan melakukan pembayaran untuk mengaktifkan keanggotaan')
                ->success()
                ->send();
            return redirect($transaction->payment_url);
        } catch (\Throwable $th) {
            DB::rollBack();
            Notification::make()
                ->title($th->getMessage())
                ->danger()
                ->send();
        }
    }

    public function generateMemberId(array $member): string
    {
        return DB::transaction(function () use ($member) {
            // Ambil prefix
            $prefix = sprintf('INA-%02d-%04d-', $member['organization_province_id'], $member['organization_city_id']);

            // Cari nomor terakhir yang sudah dipakai dengan prefix tersebut
            $lastNumber = Member::where('registration_number', 'like', $prefix . '%')
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
            return $prefix . str_pad($next, 7, '0', STR_PAD_LEFT);
        });
    }

    protected function getFormModel(): string
    {
        return Member::class; // Model yang digunakan
    }

    protected function isRegistrationComplete(): bool
    {
        return $this->user->member?->is_registration;
        // Sesuaikan dengan logika status registrasi Anda
    }
}
