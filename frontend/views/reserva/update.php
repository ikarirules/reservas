<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */

$this->title = 'Editar reserva';
?>
<div class="reserva-update">

    <?= Html::a('← ' . Html::encode($model->nombre), ['view', 'id' => $model->id], ['class' => 'volver']) ?>

    <div class="encabezado">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
