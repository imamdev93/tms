<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    return TransactionResource::mutateFormDataBeforeCreate($data);
                })
                ->after(function ($record, array $data) {
                    $paymentUrl = TransactionResource::afterCreate($record, $data);

                    if ($paymentUrl) {
                        // Gunakan redirect()->away() karena ini Livewire
                        return redirect()->away($paymentUrl);
                    }
                }),
        ];
    }
}
