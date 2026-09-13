<div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('filament.widgets.donatur-fundraiser-table', [
        'fundraiserId' => $fundraiser->id,
        'timePeriod' => $timePeriod ?? 'all_time',
    ]);

$__html = app('livewire')->mount($__name, $__params, 'lw-2177405063-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>

<?php /**PATH /home/simadior/public_html/resources/views/filament/widgets/fundraiser-donatur-modal.blade.php ENDPATH**/ ?>