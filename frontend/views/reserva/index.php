<?php

use frontend\models\Reserva;
use frontend\models\ReservaSearch;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\ReservaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Reservas';
$f = Yii::$app->formatter;
$esHoy = $searchModel->periodo === ReservaSearch::PERIODO_HOY;
$bajadas = [
    ReservaSearch::PERIODO_HOY => 'Hoy, ' . $f->asDate('now', "EEEE d 'de' MMMM") . '.',
    ReservaSearch::PERIODO_PROXIMAS => 'Próximas reservas, de hoy en adelante.',
    ReservaSearch::PERIODO_TODAS => 'Todas las reservas.',
];
$t = ReservaSearch::totales($dataProvider);
$plural = fn(int $n, string $uno, string $varios) => "<strong>{$n}</strong> " . ($n === 1 ? $uno : $varios);

/**
 * Formulario de llegada: cantidad de personas + botón.
 */
$formLlegada = function (Reserva $m, int $valor, string $boton, string $claseBoton) {
    return Html::beginForm(['llegada', 'id' => $m->id], 'post', ['class' => 'form-llegada'])
        . Html::input('number', 'personas', $valor, [
            'class' => 'form-control form-control-sm',
            'min' => 1,
            'max' => Reserva::MAX_PERSONAS,
            'required' => true,
            'inputmode' => 'numeric',
            'aria-label' => 'Personas que llegaron',
        ])
        . Html::submitButton($boton, ['class' => "btn btn-sm {$claseBoton}"])
        . Html::endForm();
};
?>
<div class="reserva-index">

    <div class="encabezado">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="bajada"><?= Html::encode($bajadas[$searchModel->periodo]) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('+ Sin reserva', ['sin-reserva'], ['class' => 'btn btn-outline-secondary', 'title' => 'Registrar un grupo que llegó sin reserva']) ?>
            <?= Html::a('+ Nueva reserva', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?= $this->render('_search', ['model' => $searchModel]) ?>

    <div class="totales">
        <div><?= $plural($t['reservas'], 'reserva', 'reservas') ?></div>
        <div><?= $plural($t['personas'], 'persona reservada', 'personas reservadas') ?></div>
        <?php if ($esHoy): ?>
            <div><?= $plural($t['llegaron'], 'grupo llegó', 'grupos llegaron') ?> · <strong><?= $t['personas_llegaron'] ?></strong> personas</div>
            <?php if ($t['sin_reserva'] > 0): ?>
                <div><?= $plural($t['sin_reserva'], 'sin reserva', 'sin reserva') ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($esHoy && ($carga = Reserva::cargaMozos(date('Y-m-d')))): ?>
        <div class="carga-mozos">
            <span class="titulo">Mozos</span>
            <?php foreach ($carga as $c): ?>
                <span class="mozo<?= $c['grupos'] ? '' : ' libre' ?>">
                    <?= Html::encode($c['username']) ?>
                    <small><?= $c['grupos'] ? "{$c['grupos']} gr · {$c['personas']} p" : 'libre' ?></small>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'tabla'],
        'layout' => "{items}\n{pager}",
        'emptyText' => $searchModel->q
            ? 'No hay reservas que coincidan con la búsqueda.'
            : ($esHoy ? 'No hay reservas para hoy.' : 'No hay reservas.'),
        'rowOptions' => fn(Reserva $m) => $m->haLlegado() ? ['class' => 'fila-llego'] : [],
        'columns' => [
            [
                'attribute' => 'fecha',
                'label' => 'Fecha',
                'visible' => !$esHoy,
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Fecha'],
                'value' => function (Reserva $m) use ($f) {
                    $texto = $f->asDate($m->fecha, 'EEE d MMM');
                    $esFinde = (int)date('N', strtotime($m->fecha)) >= 6;
                    $esHoy = $m->fecha === date('Y-m-d');
                    return Html::tag('span', Html::encode($texto), [
                        'class' => 'etiqueta' . ($esHoy ? ' etiqueta-ok' : ($esFinde ? ' etiqueta-finde' : '')),
                        'title' => $f->asDate($m->fecha, 'full'),
                    ]);
                },
            ],
            [
                'attribute' => 'nombre',
                'label' => 'Nombre',
                'format' => 'raw',
                'contentOptions' => ['class' => 'principal'],
                'value' => function (Reserva $m) {
                    $html = Html::a(Html::encode($m->nombre), ['view', 'id' => $m->id]);
                    if ($m->sin_reserva) {
                        $html .= ' ' . Html::tag('span', 'Sin reserva', ['class' => 'etiqueta']);
                    }
                    return $html;
                },
            ],
            [
                'attribute' => 'cantidad',
                'label' => 'Personas',
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Personas'],
                'value' => function (Reserva $m) {
                    $reservadas = (int)$m->cantidad;
                    $llegaron = (int)$m->personas_llegaron;
                    if (!$m->haLlegado() || $m->sin_reserva || $llegaron === $reservadas) {
                        return (string)$m->personasEfectivas();
                    }
                    return Html::tag('span', $reservadas, ['class' => 'tachado', 'title' => 'Reservadas'])
                        . ' → ' . Html::tag('strong', $llegaron, ['title' => 'Llegaron'])
                        . Html::tag('span', sprintf(' %+d', $llegaron - $reservadas),
                            ['class' => $llegaron > $reservadas ? 'dif-mas' : 'dif-menos']);
                },
            ],
            [
                'attribute' => 'telefono',
                'label' => 'Teléfono',
                'format' => 'raw',
                'enableSorting' => false,
                'contentOptions' => ['data-label' => 'Teléfono'],
                'value' => fn(Reserva $m) => $m->telefono
                    ? Html::a(Html::encode($m->telefono), 'tel:' . preg_replace('/[^0-9+]/', '', $m->telefono))
                    : '<span class="text-muted">—</span>',
            ],
            [
                'attribute' => 'mesa',
                'label' => 'Mesa',
                'visible' => $esHoy,
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Mesa', 'class' => 'celda-mesa'],
                'value' => function (Reserva $m) {
                    $mozos = array_map(fn($u) => Html::encode($u->username), $m->mozos);
                    if (!$m->mesa && !$mozos) {
                        return Html::a('Asignar', ['asignar', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary']);
                    }
                    return Html::a(
                        Html::tag('span', $m->mesa ? (int)$m->mesa : '—', ['class' => 'numero-mesa' . ($m->mesa ? '' : ' vacia')])
                        . Html::tag('span', $mozos ? implode(', ', $mozos) : 'sin mozo', ['class' => 'mozos' . ($mozos ? '' : ' vacio')]),
                        ['asignar', 'id' => $m->id],
                        ['class' => 'asignacion', 'title' => 'Cambiar mesa o mozos']
                    );
                },
            ],
            [
                'label' => 'Llegada',
                'visible' => $esHoy,
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Llegada', 'class' => 'celda-llegada'],
                'value' => function (Reserva $m) use ($formLlegada) {
                    if (!$m->haLlegado()) {
                        return $formLlegada($m, (int)$m->cantidad, 'Llegó', 'btn-primary');
                    }
                    $hora = $m->hora_llegada ? date('H:i', strtotime($m->hora_llegada)) : '';
                    return Html::tag('span', '✓ Llegó ' . $hora, ['class' => 'etiqueta etiqueta-ok'])
                        . '<details class="corregir"><summary>Corregir</summary>'
                        . $formLlegada($m, $m->personasEfectivas(), 'Guardar', 'btn-outline-secondary')
                        . ($m->sin_reserva ? '' : Html::a('Deshacer llegada', ['deshacer-llegada', 'id' => $m->id], [
                            'class' => 'deshacer',
                            'data' => ['method' => 'post', 'confirm' => "¿Deshacer la llegada de {$m->nombre}?"],
                        ]))
                        . '</details>';
                },
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{update} {delete}',
                'contentOptions' => ['class' => 'acciones'],
            ],
        ],
    ]) ?>

</div>
