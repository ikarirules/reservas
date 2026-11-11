<?php

use frontend\models\ReservaSearch;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\ReservaSearch $model */

$url = function (string $periodo) use ($model) {
    return ['index', 'periodo' => $periodo, 'q' => $model->q ?: null];
};
?>
<div class="filtros">
    <?= Html::beginForm(['index'], 'get', ['class' => 'buscar d-flex gap-2']) ?>
        <?= Html::hiddenInput('periodo', $model->periodo) ?>
        <?= Html::textInput('q', $model->q, [
            'class' => 'form-control',
            'placeholder' => 'Buscar por nombre o teléfono',
            'type' => 'search',
            'aria-label' => 'Buscar',
        ]) ?>
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::endForm() ?>

    <div class="segmentos" role="group" aria-label="Período">
        <?php foreach ([
            ReservaSearch::PERIODO_HOY => 'Hoy',
            ReservaSearch::PERIODO_PROXIMAS => 'Próximas',
            ReservaSearch::PERIODO_TODAS => 'Todas',
        ] as $periodo => $texto): ?>
            <?= Html::a($texto, $url($periodo), ['class' => $model->periodo === $periodo ? 'activo' : '']) ?>
        <?php endforeach; ?>
    </div>
</div>
