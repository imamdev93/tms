<?php

namespace App\Filament\Resources\BeltLevelResource\Pages;

use App\Filament\Resources\BeltLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBeltLevels extends ManageRecords
{
    protected static string $resource = BeltLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
