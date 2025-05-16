<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */

$this->title = Yii::t('app', 'Create Reserva');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reservas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reserva-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
