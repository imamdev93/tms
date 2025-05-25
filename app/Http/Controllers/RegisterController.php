<?php

namespace App\Http\Controllers;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use InteractsWithForms;

    public ?array $data = [];

    public function show()
    {
        $formSchema = $this->getFormSchema();

        return view('auth.register', [
            'formComponents' => $formSchema,
        ]);
    }

    public function store(Request $request)
    {
        // $this->form = $this->makeForm();

        $this->form->fill($request->all());

        $data = $this->form->getState();

        // Simpan user baru
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Notification::make()
            ->title('Registrasi berhasil!')
            ->success()
            ->send();

        return redirect()->route('login'); // atau login otomatis
    }

    // protected function makeForm(): Form
    // {
    //     return Form::make()
    //         ->schema($this->getFormSchema())
    //         ->statePath('data');
    // }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->label('Nama'),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->label('Email'),

            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                ->label('Password'),

            Forms\Components\TextInput::make('password_confirmation')
                ->password()
                ->required()
                ->same('password')
                ->label('Konfirmasi Password'),
        ];
    }
}
