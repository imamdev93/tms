<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Member;
use App\Models\PaymentCategory;
use App\Models\Transaction;
use App\PaymentMethodEnum;
use App\PaymentStatusEnum;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $startDate = Carbon::now()->month(Carbon::now()->month)->day(10)->startOfDay(); // Tanggal 10 bulan berjalan
        $endDate = Carbon::now()->subMonth()->day(20)->endOfDay(); // Tanggal 20 bulan sebelumnya

        $data = [
            'amount' => (int) preg_replace('/[^\d]/', '', $data['amount']),
            'user_id' => $data['user_id'],
            'payment_method' => PaymentMethodEnum::E_WALLET,
            'paid_at' => Carbon::now()->format('Y-m-d'),
            'status' => PaymentStatusEnum::PENDING,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $data['description'],
            'order_id' => Str::uuid(),
        ];

        return $data;
    }

    public static function afterCreate(Transaction $record, array $data): string
    {
        try {

            $params = [
                'transaction_details' => [
                    'order_id' => $record->order_id,
                    'gross_amount' => (int) $record->amount,
                ],
                'customer_details' => [
                    'first_name' => $record->user?->name,
                    'email' => $record->user?->email,
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

            $record->update([
                'snap_token' => $snapToken,
                'payment_url' => $paymentUrl,
                'status' => PaymentStatusEnum::PROCESSING->value,
            ]);

            return $paymentUrl;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Member')
                    ->options(self::getMembers())
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
                Radio::make('payment_category_ids')
                    ->label('Kategori Tagihan Pembayaran')
                    ->options(PaymentCategory::pluck('title', 'id'))
                    ->columnSpanFull()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $category = PaymentCategory::find($state);
                            $totalNominal = $category?->nominal ?? 0;

                            $formattedNominal = 'Rp' . number_format($totalNominal, 0, ',', '.');
                            $set('amount', $formattedNominal);
                        }
                    })
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Ambil total nominal dari kategori yang dipilih
                        $totalNominal = PaymentCategory::where('id', $state)->sum('nominal');
                        // Formatkan total nominal menjadi format IDR
                        $formattedNominal = 'Rp' . number_format($totalNominal, 0, ',', '.');

                        // Setkan nominal dengan nilai yang sudah diformat
                        $set('amount', $formattedNominal);
                    })
                    ->reactive(),
                TextInput::make('amount')->label('Nominal')->readOnly()->columnSpanFull()->required(),
                Textarea::make('description')->label('Deskripsi')->columnSpanFull()->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Transaction::where('user_id', auth()->user()->id);
            })
            ->columns([
                TextColumn::make('user.name')->label('Nama Member'),
                TextColumn::make('amount')->label('Nominal Tagihan')->money('IDR'),
                TextColumn::make('status')->label('Status Pembayaran')->badge()
                    ->color(fn(string $state): string => PaymentStatusEnum::tryFrom($state)?->color() ?? 'secondary')
                    ->formatStateUsing(fn(string $state): string => PaymentStatusEnum::tryFrom($state)?->getLabel() ?? $state),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('handle_payment')
                    ->button()
                    ->label('Bayar')
                    ->color('success')
                    ->icon('heroicon-o-banknotes')
                    ->action(function (Transaction $record, $livewire) {
                        // if ($record->payment_url) {
                        //     // Jika sudah punya payment_url, redirect ke sana
                        //     $livewire->redirect($record->payment_url);
                        //     return;
                        // }

                        // if ($record->status !== PaymentStatusEnum::PENDING->value) {
                        //     Notification::make()
                        //         ->title('Transaksi tidak valid')
                        //         ->body('Transaksi harus berstatus pending untuk membuat link pembayaran.')
                        //         ->danger()
                        //         ->send();
                        //     return;
                        // }

                        try {
                            $params = [
                                'transaction_details' => [
                                    'order_id' => $record->order_id,
                                    'gross_amount' => (int) $record->amount,
                                ],
                                'customer_details' => [
                                    'first_name' => $record->user?->name,
                                    'email' => $record->user?->email,
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

                            $record->update([
                                'order_id' => $record->order_id,
                                'snap_token' => $snapToken,
                                'payment_url' => $paymentUrl,
                                'status' => PaymentStatusEnum::PROCESSING->value,
                            ]);

                            $livewire->redirect($paymentUrl);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Membuat Link')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->url(fn($record) => $record->payment_url)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->status !== PaymentStatusEnum::COMPLETED->value),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getMembers(): array
    {
        $query = Member::query();
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->user()->id);
        }

        return $query->pluck('name', 'user_id')->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
        ];
    }
}
