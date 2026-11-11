<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */

$this->title = 'Nueva reserva';
?>
<div class="reserva-create">

    <?= Html::a('← Reservas', ['index'], ['class' => 'volver']) ?>

    <div class="encabezado">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
