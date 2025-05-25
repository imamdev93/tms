<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentCategoryResource\Pages;
use App\Models\PaymentCategory;
use App\RoleEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentCategoryResource extends Resource
{
    protected static ?string $model = PaymentCategory::class;

    protected static ?string $navigationLabel = 'Kategori Pembayaran';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Judul')->required(),
                TextInput::make('nominal')->label('Nominal')->numeric()->required(),
                Textarea::make('description')->label('Deskripsi')->required()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('description')->label('Deskripsi')->searchable(),
                TextColumn::make('nominal')->label('Nominal')->money('IDR'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()->role, [RoleEnum::SUPERADMIN->value, RoleEnum::ADMIN->value]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentCategories::route('/'),
        ];
    }
}
