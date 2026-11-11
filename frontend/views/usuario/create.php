<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var frontend\models\UsuarioForm $model */

$this->title = 'Nuevo usuario';
?>
<div class="usuario-create">

    <?= Html::a('← Usuarios', ['index'], ['class' => 'volver']) ?>

    <div class="encabezado">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
