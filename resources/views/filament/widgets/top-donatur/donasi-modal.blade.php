<div>
    @livewire('filament.widgets.donasi-donatur-list-table', [
        'donaturId' => $donaturId,
        'currentPeriode' => $currentPeriode ?? 'keseluruhan',
        'customStartDate' => $customStartDate ?? null,
        'customEndDate' => $customEndDate ?? null,
        'currentJenisDonasi' => $currentJenisDonasi ?? null,
    ])
</div>
