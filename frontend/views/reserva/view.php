<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */

$f = Yii::$app->formatter;
$this->title = $model->nombre;
?>
<div class="reserva-view">

    <?= Html::a('← Reservas', ['index'], ['class' => 'volver']) ?>

    <div class="encabezado">
        <div>
            <h1>
                <?= Html::encode($model->nombre) ?>
                <?php if ($model->sin_reserva): ?><span class="etiqueta align-middle">Sin reserva</span><?php endif; ?>
            </h1>
            <p class="bajada"><?= Html::encode(ucfirst($f->asDate($model->fecha, 'EEEE d \'de\' MMMM \'de\' y'))) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary',
                'data' => [
                    'confirm' => '¿Eliminar la reserva de ' . $model->nombre . '?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="tarjeta tarjeta-angosta">
        <dl class="ficha">
            <div>
                <dt><?= $model->sin_reserva ? 'Personas' : 'Reservadas' ?></dt>
                <dd><?= (int)$model->cantidad ?></dd>
            </div>
            <?php if ($model->haLlegado()): ?>
                <div>
                    <dt>Llegaron</dt>
                    <dd>
                        <?= (int)$model->personas_llegaron ?>
                        <span class="etiqueta etiqueta-ok">✓ <?= Html::encode(date('H:i', strtotime($model->hora_llegada))) ?></span>
                    </dd>
                </div>
            <?php endif; ?>
            <div>
                <dt>Teléfono</dt>
                <dd><?= $model->telefono
                    ? Html::a(Html::encode($model->telefono), 'tel:' . preg_replace('/[^0-9+]/', '', $model->telefono))
                    : '<span class="text-muted">—</span>' ?></dd>
            </div>
            <div>
                <dt>Fecha</dt>
                <dd><?= Html::encode($f->asDate($model->fecha, 'dd/MM/yyyy')) ?></dd>
            </div>
        </dl>

        <div class="meta">
            Cargada el <?= Html::encode(date('d/m/Y H:i', strtotime($model->fecha_create))) ?>
            <?php if ($model->fecha_update && $model->fecha_update !== $model->fecha_create): ?>
                · modificada el <?= Html::encode(date('d/m/Y H:i', strtotime($model->fecha_update))) ?>
            <?php endif; ?>
        </div>
    </div>

</div>
