<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeltLevelResource\Pages;
use App\Models\BeltLevel;
use App\RoleEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BeltLevelResource extends Resource
{
    protected static ?string $model = BeltLevel::class;

    protected static ?string $navigationLabel = 'Sabuk';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Sabuk'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Sabuk'),
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
            'index' => Pages\ManageBeltLevels::route('/'),
        ];
    }
}
