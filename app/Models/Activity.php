<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Model activity_log lokal (audit trail input data) yang dipakai
 * halaman LogAktivitas; perilaku identik dengan model Spatie.
 */
class Activity extends SpatieActivity {}
