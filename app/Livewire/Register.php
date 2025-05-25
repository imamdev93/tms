<?php

namespace App\Livewire;

use App\Models\User;
use App\RegistrationTypeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component implements HasForms
{
    use InteractsWithForms;

    public $name;

    public $email;

    public $password;

    public $password_confirmation;

    public $role;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->label('Nama Lengkap')
                ->placeholder('Masukkan nama lengkap')
                ->extraAttributes([
                    'class' => 'fi-input-custom',
                    'style' => 'background-color: white !important; color: #111827 !important;',
                ]),

            TextInput::make('email')
                ->email()
                ->unique('users', 'email')
                ->required()
                ->label('Alamat Email')
                ->placeholder('contoh@email.com')
                ->extraAttributes([
                    'class' => 'fi-input-custom',
                    'style' => 'background-color: white !important; color: #111827 !important;',
                ]),

            TextInput::make('password')
                ->password()
                ->required()
                ->label('Password')
                ->placeholder('Minimal 8 karakter')
                ->extraAttributes([
                    'class' => 'fi-input-custom',
                    'style' => 'background-color: white !important; color: #111827 !important;',
                ]),

            TextInput::make('password_confirmation')
                ->password()
                ->required()
                ->label('Konfirmasi Password')
                ->placeholder('Ketik ulang password')
                ->extraAttributes([
                    'class' => 'fi-input-custom',
                    'style' => 'background-color: white !important; color: #111827 !important;',
                ]),
            Select::make('role')
                ->label('Daftar Sebagai')
                ->placeholder('Pilih Opsi Pendaftaran')
                ->options(
                    collect(RegistrationTypeEnum::cases())
                        ->mapWithKeys(fn ($level) => [$level->value => $level->getLabel()])
                        ->toArray()
                )->required()
                ->validationMessages([
                    'required' => 'Daftar Sebagai wajib diisi.',
                ])
                ->searchable(),
        ];
    }

    public function register()
    {
        $data = $this->form->getState();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            Notification::make()
                ->title('Registrasi berhasil')
                ->success()
                ->send();

            DB::commit();
            auth()->login($user);

            return redirect()->to('/tms/profile');
        } catch (\Throwable $th) {
            DB::rollBack();
            Notification::make()
                ->title('Registrasi gagal')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.register')
            ->layout('components.layouts.app');
    }
}
