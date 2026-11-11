<?php

use frontend\models\AsignacionForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\AsignacionForm $model */
/** @var array $carga [mozo_id => ['username', 'grupos', 'personas']] */

$reserva = $model->getReserva();
$ocupadas = $model->mesasOcupadas();
$this->title = 'Asignar mesa y mozos';
?>
<div class="reserva-asignar">

    <?= Html::a('← Hoy', ['index', 'periodo' => 'hoy'], ['class' => 'volver']) ?>

    <div class="encabezado">
        <div>
            <h1><?= Html::encode($reserva->nombre) ?></h1>
            <p class="bajada">
                <?= $reserva->personasEfectivas() ?> personas
                · <?= Html::encode(Yii::$app->formatter->asDate($reserva->fecha, "EEEE d 'de' MMMM")) ?>
                <?php if ($reserva->haLlegado()): ?> · <span class="etiqueta etiqueta-ok">✓ Llegó</span><?php endif; ?>
            </p>
        </div>
    </div>

    <div class="tarjeta tarjeta-angosta">
        <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'mesa')->input('number', [
                'min' => 1,
                'max' => AsignacionForm::MESA_MAX,
                'inputmode' => 'numeric',
                'autofocus' => true,
                'class' => 'form-control campo-mesa',
            ]) ?>

            <?php if ($ocupadas): ?>
                <p class="ayuda">
                    Ocupadas hoy:
                    <?php foreach ($ocupadas as $num => $nombre): ?>
                        <span class="etiqueta" title="<?= Html::encode($nombre) ?>"><?= (int)$num ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <?php if ($carga): ?>
                <?= $form->field($model, 'mozo_ids')->checkboxList(
                    AsignacionForm::mozosDisponibles(),
                    [
                        'class' => 'lista-mozos',
                        'item' => function ($index, $label, $name, $checked, $value) use ($carga) {
                            $c = $carga[(int)$value] ?? ['grupos' => 0, 'personas' => 0];
                            $detalle = $c['grupos']
                                ? "{$c['grupos']} " . ($c['grupos'] === 1 ? 'grupo' : 'grupos') . " · {$c['personas']} pers."
                                : 'libre';
                            return Html::label(
                                Html::checkbox($name, $checked, ['value' => $value, 'class' => 'form-check-input'])
                                . Html::tag('span', Html::encode($label), ['class' => 'mozo-nombre'])
                                . Html::tag('span', $detalle, ['class' => 'mozo-carga']),
                                null,
                                ['class' => 'mozo-opcion']
                            );
                        },
                    ]
                )->hint('Podés marcar más de uno.') ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    No hay mozos activos.
                    <?php if (Yii::$app->user->identity->tieneRol(\common\models\User::ROL_ADMIN)): ?>
                        <?= Html::a('Crear uno', ['/usuario/create']) ?>.
                    <?php else: ?>
                        Pedile al administrador que los cree.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="acciones-form">
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Cancelar', ['index', 'periodo' => 'hoy'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
