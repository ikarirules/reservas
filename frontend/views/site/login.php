<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Iniciar sesión';
?>
<div class="site-login acceso">

    <div class="tarjeta">
        <h1><?= Html::encode($this->title) ?></h1>
        <p class="bajada">Ingresá con tu usuario y contraseña.</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>

            <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'current-password']) ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <?= Html::submitButton('Ingresar', ['class' => 'btn btn-primary w-100 mt-2', 'name' => 'login-button']) ?>

        <?php ActiveForm::end(); ?>

        <p class="text-center small mt-3 mb-0">
            <?= Html::a('¿Olvidaste tu contraseña?', ['site/request-password-reset']) ?>
        </p>
    </div>

</div>
