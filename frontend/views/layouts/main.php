<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\models\User;
use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$identity = Yii::$app->user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#b5532c">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title ? $this->title . ' · ' . Yii::$app->name : Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' => '<span class="marca-punto"></span>' . Html::encode(Yii::$app->name),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar navbar-expand-md barra-superior'],
        'innerContainerOptions' => ['class' => 'container'],
    ]);

    if ($identity !== null) {
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav me-auto'],
            'items' => [
                [
                    'label' => 'Reservas',
                    'url' => ['/reserva/index'],
                    'active' => Yii::$app->controller->id === 'reserva',
                    'visible' => $identity->tieneRol(User::ROL_ADMIN, User::ROL_RECEPCIONISTA),
                ],
                [
                    'label' => 'Usuarios',
                    'url' => ['/usuario/index'],
                    'active' => Yii::$app->controller->id === 'usuario',
                    'visible' => $identity->tieneRol(User::ROL_ADMIN),
                ],
            ],
        ]);

        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex align-items-center gap-3 usuario-actual'])
            . Html::tag('span',
                Html::encode($identity->username)
                . Html::tag('small', Html::encode($identity->getRolNombre())),
                ['class' => 'usuario-nombre'])
            . Html::submitButton('Salir', ['class' => 'btn btn-sm btn-outline-secondary'])
            . Html::endForm();
    }

    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container contenido">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
