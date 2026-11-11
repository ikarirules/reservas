<?php

use common\models\User;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var frontend\models\UsuarioForm $model */
?>
<div class="usuario-form tarjeta tarjeta-angosta">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-sm-6">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'autofocus' => true]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'email')->input('email') ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'rol')->dropDownList(User::roles(), ['prompt' => 'Elegir…']) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'status')->dropDownList([
                User::STATUS_ACTIVE => 'Activo',
                User::STATUS_INACTIVE => 'Inactivo',
            ]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'new-password']) ?>
        </div>
    </div>

    <div class="acciones-form">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
