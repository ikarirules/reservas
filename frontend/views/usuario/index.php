<?php

use common\models\User;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Usuarios';
?>
<div class="usuario-index">

    <div class="encabezado">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="bajada">Personal con acceso al sistema.</p>
        </div>
        <?= Html::a('+ Nuevo usuario', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'tabla'],
        'layout' => "{items}\n{pager}",
        'columns' => [
            [
                'attribute' => 'username',
                'label' => 'Usuario',
                'format' => 'raw',
                'contentOptions' => ['class' => 'principal'],
                'value' => fn(User $u) => Html::encode($u->username)
                    . ($u->esProtegido() ? ' ' . Html::tag('span', 'Protegido', ['class' => 'etiqueta', 'title' => 'Solo puede modificarlo el propio usuario']) : ''),
            ],
            [
                'attribute' => 'email',
                'label' => 'Email',
                'contentOptions' => ['data-label' => 'Email'],
            ],
            [
                'attribute' => 'rol',
                'label' => 'Categoría',
                'contentOptions' => ['data-label' => 'Categoría'],
                'value' => fn(User $u) => $u->getRolNombre(),
            ],
            [
                'attribute' => 'status',
                'label' => 'Estado',
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Estado'],
                'value' => function (User $u) {
                    $activo = (int)$u->status === User::STATUS_ACTIVE;
                    return Html::tag('span', User::estados()[$u->status] ?? $u->status, [
                        'class' => 'etiqueta' . ($activo ? ' etiqueta-ok' : ''),
                    ]);
                },
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{update} {delete}',
                'contentOptions' => ['class' => 'acciones'],
                'visibleButtons' => [
                    'update' => fn(User $u) => $u->puedeSerModificadoPorActual(),
                    'delete' => fn(User $u) => $u->puedeSerModificadoPorActual()
                        && (int)$u->id !== (int)Yii::$app->user->id,
                ],
            ],
        ],
    ]) ?>

</div>
