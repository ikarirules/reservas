<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */
/** @var yii\widgets\ActiveForm $form */
$this->registerJs("flatpickr('#fecha-reserva', { dateFormat: 'Y-m-d' });");

?>

<div class="reserva-form">

   <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'cantidad')->textInput() ?>

        <?= $form->field($model, 'fecha')->textInput(['id' => 'fecha-reserva']) ?>


        <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Guardar'), ['class' => 'btn btn-success']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
