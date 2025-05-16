<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\ReservaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="reserva-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nombre') ?>

    <?= $form->field($model, 'cantidad') ?>

    <?= $form->field($model, 'fecha') ?>

    <?= $form->field($model, 'fecha_create') ?>

    <?php // echo $form->field($model, 'fecha_update') ?>

    <?php // echo $form->field($model, 'telefono') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
