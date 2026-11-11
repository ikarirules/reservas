<?php

/** @var yii\web\View $this */
/** @var frontend\models\Reserva[] $grupos */

use yii\bootstrap5\Html;

$user = Yii::$app->user->identity;
$this->title = 'Mis mesas';
$personas = array_sum(array_map(fn($g) => $g->personasEfectivas(), $grupos));
?>
<div class="site-index">
    <div class="encabezado">
        <div>
            <h1>Hola, <?= Html::encode($user->username) ?></h1>
            <p class="bajada">
                Tus mesas de hoy, <?= Html::encode(Yii::$app->formatter->asDate('now', "EEEE d 'de' MMMM")) ?>.
            </p>
        </div>
    </div>

    <?php if (!$grupos): ?>
        <div class="tarjeta text-center text-muted">Todavía no tenés mesas asignadas hoy.</div>
    <?php else: ?>
        <div class="totales">
            <div><strong><?= count($grupos) ?></strong> <?= count($grupos) === 1 ? 'grupo' : 'grupos' ?></div>
            <div><strong><?= $personas ?></strong> personas</div>
        </div>

        <div class="mis-mesas">
            <?php foreach ($grupos as $g): ?>
                <?php $companeros = array_filter($g->mozos, fn($u) => (int)$u->id !== (int)$user->id); ?>
                <div class="tarjeta mesa-card<?= $g->haLlegado() ? ' llego' : '' ?>">
                    <div class="numero-mesa grande<?= $g->mesa ? '' : ' vacia' ?>"><?= $g->mesa ? (int)$g->mesa : '—' ?></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold"><?= Html::encode($g->nombre) ?></div>
                        <div class="text-muted small">
                            <?= $g->personasEfectivas() ?> personas
                            <?php if ($companeros): ?>
                                · con <?= Html::encode(implode(', ', array_map(fn($u) => $u->username, $companeros))) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?= $g->haLlegado()
                        ? Html::tag('span', '✓ Llegó ' . date('H:i', strtotime($g->hora_llegada)), ['class' => 'etiqueta etiqueta-ok'])
                        : Html::tag('span', 'Esperando', ['class' => 'etiqueta']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
