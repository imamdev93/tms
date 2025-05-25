<?php

namespace App\Filament\Resources\MemberCategoryResource\Pages;

use App\Filament\Resources\MemberCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMemberCategories extends ManageRecords
{
    protected static string $resource = MemberCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
