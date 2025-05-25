<?php

namespace App\Filament\Pages;

use App\Models\Member;
use Filament\Pages\Page;

class MembershipCard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static string $view = 'filament.pages.membership-card';

    public Member $card;

    public function mount(Member $card)
    {
        $this->card = auth()->user()?->member;
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('filament.pages.membership-card-pdf', ['card' => $this->card])
            ->setPaper([0, 0, 226.77, 141.73]); // 80x50mm

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "card-{$this->card->card_number}.pdf"
        );
    }
}
