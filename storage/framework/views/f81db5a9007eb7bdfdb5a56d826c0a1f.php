<div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('filament.widgets.donasi-donatur-list-table', [
        'donaturId' => $donaturId,
        'currentPeriode' => $currentPeriode ?? 'keseluruhan',
        'customStartDate' => $customStartDate ?? null,
        'customEndDate' => $customEndDate ?? null,
        'currentJenisDonasi' => $currentJenisDonasi ?? null,
    ]);

$__html = app('livewire')->mount($__name, $__params, 'lw-490687953-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php /**PATH /home/simadior/public_html/resources/views/filament/widgets/top-donatur/donasi-modal.blade.php ENDPATH**/ ?>