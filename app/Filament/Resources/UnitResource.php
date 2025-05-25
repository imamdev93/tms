<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\City;
use App\Models\Province;
use App\Models\Subdistrict;
use App\Models\Unit;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Unit')
                    ->required()
                    ->string()
                    ->columnSpanFull(),

                Grid::make(12)->schema([
                    Select::make('province_id')->label('Provinsi')
                        ->options(Province::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->string()
                        ->reactive() // <--- penting agar memicu perubahan pada field lain
                        ->afterStateUpdated(fn (callable $set) => $set('city_id', null))
                        ->columnSpan(4),

                    Select::make('city_id')
                        ->label('Kota')
                        ->options(function (callable $get) {
                            $provinceId = $get('province_id');
                            if (! $provinceId) {
                                return [];
                            }

                            return City::where('province_id', $provinceId)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('subdistrict_id', null))
                        ->columnSpan(4),

                    Select::make('subdistrict_id')
                        ->label('Kecamatan')
                        ->options(function (callable $get) {
                            $cityId = $get('city_id');
                            if (! $cityId) {
                                return [];
                            }

                            return Subdistrict::where('city_id', $cityId)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->columnSpan(4),
                ]),
                Textarea::make('address')->label('Alamat')->string()->columnSpanFull(),
                Textarea::make('description')->label('Deskripsi')->string()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Unit')->searchable(),
                TextColumn::make('description')->label('Deskripsi')->searchable(),
                TextColumn::make('address')->label('Alamat')->searchable(),
                TextColumn::make('subdistrict.name')->label('Kecamatan')->searchable(),
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
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUnits::route('/'),
        ];
    }
}
