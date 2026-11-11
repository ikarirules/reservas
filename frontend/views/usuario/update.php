<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var frontend\models\UsuarioForm $model */

$this->title = 'Editar usuario';
?>
<div class="usuario-update">

    <?= Html::a('← Usuarios', ['index'], ['class' => 'volver']) ?>

    <div class="encabezado">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="bajada"><?= Html::encode($model->getUser()->username) ?></p>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
