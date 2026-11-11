<?php

use frontend\assets\FlatpickrAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Reserva $model */
/** @var yii\widgets\ActiveForm $form */

// flatpickr solo se usa para "Otra fecha…" (reservas especiales lejanas).
FlatpickrAsset::register($this);

$config = [
    'urlSemana' => Url::to(['reserva/semana']),
    'permitirPasado' => !$model->isNewRecord,
];
$this->registerJs('selectorSemana(' . Json::encode($config) . ');');
?>

<div class="reserva-form tarjeta tarjeta-angosta">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'cantidad')->input('number', ['min' => 1, 'inputmode' => 'numeric'])->label('Personas') ?>

        <div class="mb-3">
            <label class="form-label">Fecha</label>

            <div id="selector-semana" class="selector-semana">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-nav="-1" aria-label="Semana anterior">&lsaquo;</button>
                    <strong class="titulo-semana"></strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-nav="1" aria-label="Semana siguiente">&rsaquo;</button>
                </div>
                <div class="dias-semana"></div>
                <div class="d-flex justify-content-between mt-2 small">
                    <a href="#" class="ir-hoy">Esta semana</a>
                    <span class="fecha-elegida text-muted"></span>
                    <a href="#" class="otra-fecha">Otra fecha…</a>
                </div>
                <input type="text" class="otra-fecha-input" tabindex="-1" aria-hidden="true">
            </div>

            <?= $form->field($model, 'fecha', ['options' => ['class' => 'mb-0']])
                ->hiddenInput(['id' => 'fecha-reserva'])
                ->label(false) ?>
        </div>

        <?= $form->field($model, 'telefono')->input('tel', ['maxlength' => true])->label('Teléfono') ?>

        <div class="acciones-form">
            <?= Html::submitButton($model->isNewRecord ? 'Guardar reserva' : 'Guardar cambios', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Cancelar', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<'JS'
window.selectorSemana = function (cfg) {
    var raiz = document.getElementById('selector-semana');
    var input = document.getElementById('fecha-reserva');
    var diasEl = raiz.querySelector('.dias-semana');
    var tituloEl = raiz.querySelector('.titulo-semana');
    var elegidaEl = raiz.querySelector('.fecha-elegida');

    var NOMBRES = ['lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom'];
    var DIAS_LARGOS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    // Fechas siempre en hora local, sin pasar por UTC.
    function aTexto(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function deTexto(s) {
        var p = s.split('-');
        return new Date(+p[0], +p[1] - 1, +p[2]);
    }
    function sumarDias(d, n) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n);
    }
    function lunesDe(d) {
        var dia = (d.getDay() + 6) % 7; // lunes = 0
        return sumarDias(d, -dia);
    }

    var hoy = new Date();
    hoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
    var inicio = input.value ? lunesDe(deTexto(input.value)) : lunesDe(hoy);
    var totales = {};

    function tituloSemana(lunes) {
        var dom = sumarDias(lunes, 6);
        if (lunes.getMonth() === dom.getMonth()) {
            return 'Semana del ' + lunes.getDate() + ' al ' + dom.getDate() + ' de ' + MESES[dom.getMonth()];
        }
        return 'Semana del ' + lunes.getDate() + ' de ' + MESES[lunes.getMonth()] +
               ' al ' + dom.getDate() + ' de ' + MESES[dom.getMonth()];
    }

    function dibujar() {
        tituloEl.textContent = tituloSemana(inicio);
        diasEl.innerHTML = '';
        for (var i = 0; i < 7; i++) {
            var d = sumarDias(inicio, i);
            var clave = aTexto(d);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dia';
            btn.dataset.fecha = clave;
            if (i >= 5) btn.classList.add('finde');
            if (clave === aTexto(hoy)) btn.classList.add('hoy');
            if (clave === input.value) btn.classList.add('elegido');
            if (!cfg.permitirPasado && d < hoy) btn.disabled = true;

            var t = totales[clave];
            btn.innerHTML =
                '<span class="nombre">' + NOMBRES[i] + '</span>' +
                '<span class="numero">' + d.getDate() + '</span>' +
                '<span class="ocupacion">' + (t ? t.personas + ' pers.' : '&nbsp;') + '</span>';
            if (t) btn.title = t.reservas + ' reserva(s), ' + t.personas + ' personas';
            diasEl.appendChild(btn);
        }
        if (input.value) {
            var e = deTexto(input.value);
            elegidaEl.textContent = DIAS_LARGOS[e.getDay()] + ' ' + e.getDate() + ' de ' + MESES[e.getMonth()];
        } else {
            elegidaEl.textContent = 'Sin fecha elegida';
        }
    }

    function cargarTotales() {
        totales = {};
        dibujar();
        var pedido = aTexto(inicio);
        fetch(cfg.urlSemana + (cfg.urlSemana.indexOf('?') >= 0 ? '&' : '?') + 'desde=' + pedido, {
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            credentials: 'same-origin'
        })
            .then(function (r) { return r.ok ? r.json() : {}; })
            .then(function (datos) {
                if (pedido === aTexto(inicio)) { // descartar si ya cambió de semana
                    totales = datos || {};
                    dibujar();
                }
            })
            .catch(function () { /* sin totales, el selector igual funciona */ });
    }

    function elegir(fecha) {
        input.value = fecha;
        $(input).trigger('change'); // revalida el campo con ActiveForm
        dibujar();
    }

    diasEl.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.dia');
        if (btn && !btn.disabled) elegir(btn.dataset.fecha);
    });
    raiz.querySelectorAll('[data-nav]').forEach(function (b) {
        b.addEventListener('click', function () {
            inicio = sumarDias(inicio, 7 * +b.dataset.nav);
            cargarTotales();
        });
    });
    raiz.querySelector('.ir-hoy').addEventListener('click', function (ev) {
        ev.preventDefault();
        inicio = lunesDe(hoy);
        cargarTotales();
    });

    // "Otra fecha…": calendario completo para reservas especiales lejanas.
    var linkOtra = raiz.querySelector('.otra-fecha');
    var fp = flatpickr(raiz.querySelector('.otra-fecha-input'), {
        locale: 'es',
        dateFormat: 'Y-m-d',
        minDate: cfg.permitirPasado ? null : 'today',
        disableMobile: true,
        positionElement: linkOtra,
        onChange: function (sel, texto) {
            if (!texto) return;
            inicio = lunesDe(deTexto(texto));
            elegir(texto);
            cargarTotales();
        }
    });
    linkOtra.addEventListener('click', function (ev) {
        ev.preventDefault();
        fp.open();
    });

    cargarTotales();
};
JS
, \yii\web\View::POS_HEAD);
?>
