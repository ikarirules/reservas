<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Calendario flatpickr en castellano (versión fija para que no cambie sola).
 */
class FlatpickrAsset extends AssetBundle
{
    public $css = [
        'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css',
    ];
    public $js = [
        'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js',
        'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js',
    ];
    public $depends = [
        AppAsset::class,
    ];
}
