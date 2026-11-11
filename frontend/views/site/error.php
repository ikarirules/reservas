<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = $name;
$codigo = $exception instanceof \yii\web\HttpException ? $exception->statusCode : 500;
$textos = [
    403 => 'No tenés permiso para ver esta página.',
    404 => 'La página que buscás no existe.',
];
?>
<div class="site-error acceso">
    <div class="tarjeta text-center">
        <p class="display-6 fw-bold mb-1" style="color: var(--barro)"><?= $codigo ?></p>
        <h1 class="h5"><?= Html::encode($textos[$codigo] ?? 'Ocurrió un error.') ?></h1>
        <?php if (!isset($textos[$codigo])): ?>
            <p class="bajada small"><?= nl2br(Html::encode($message)) ?></p>
        <?php endif; ?>
        <?= Html::a('Volver al inicio', Yii::$app->homeUrl, ['class' => 'btn btn-outline-secondary mt-3']) ?>
    </div>
</div>
