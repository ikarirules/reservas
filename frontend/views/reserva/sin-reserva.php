<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */

$this->title = 'Llegó sin reserva';
?>
<div class="reserva-sin-reserva">

    <?= Html::a('← Hoy', ['index', 'periodo' => 'hoy'], ['class' => 'volver']) ?>

    <div class="encabezado">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="bajada">Se registra para hoy y queda marcado como llegado.</p>
        </div>
    </div>

    <div class="tarjeta tarjeta-angosta">
        <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'nombre')->textInput([
                'maxlength' => true,
                'autofocus' => true,
                'placeholder' => 'Apellido o una referencia del grupo',
            ]) ?>

            <?= $form->field($model, 'cantidad')->input('number', ['min' => 1, 'inputmode' => 'numeric'])->label('Personas') ?>

            <?= $form->field($model, 'telefono')->input('tel', ['maxlength' => true])->label('Teléfono (opcional)') ?>

            <div class="acciones-form">
                <?= Html::submitButton('Registrar llegada', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Cancelar', ['index', 'periodo' => 'hoy'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
