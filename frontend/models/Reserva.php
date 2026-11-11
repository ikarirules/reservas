<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "reserva".
 *
 * @property int $id
 * @property string $nombre
 * @property int $cantidad personas reservadas
 * @property string $fecha
 * @property string|null $fecha_create
 * @property string|null $fecha_update
 * @property string|null $telefono
 * @property string $estado pendiente | llego
 * @property string|null $hora_llegada
 * @property int|null $personas_llegaron personas que realmente llegaron
 * @property bool $sin_reserva el grupo llegó sin haber reservado
 * @property int|null $mesa número de mesa del grupo ese día
 *
 * @property \common\models\User[] $mozos
 */
class Reserva extends \yii\db\ActiveRecord
{
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_LLEGO = 'llego';

    /** Tope razonable para la corrección de personas al llegar. */
    const MAX_PERSONAS = 500;

    public static function tableName()
    {
        return 'reserva';
    }

    public function rules()
    {
        return [
            [['nombre', 'cantidad', 'fecha'], 'required'],
            [['telefono'], 'required', 'when' => function (self $model) {
                return !$model->sin_reserva;
            }, 'enableClientValidation' => false, 'message' => 'Ingresá un teléfono de contacto.'],
            [['cantidad'], 'integer', 'min' => 1, 'max' => self::MAX_PERSONAS,
                'tooSmall' => 'Tiene que haber al menos 1 persona.'],
            [['nombre'], 'string', 'max' => 255],
            [['fecha'], 'date', 'format' => 'php:Y-m-d', 'message' => 'La fecha no es válida.'],
            [['fecha'], 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '>=', 'type' => 'string',
                'when' => function (self $model) {
                    return $model->isNewRecord;
                },
                'enableClientValidation' => false,
                'message' => 'No se puede reservar para un día que ya pasó.'],
            [['fecha_create', 'fecha_update'], 'safe'],
            [['telefono'], 'string', 'max' => 20],
            [['telefono'], 'default', 'value' => null],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'cantidad' => 'Personas',
            'fecha' => 'Fecha',
            'fecha_create' => 'Cargada',
            'fecha_update' => 'Modificada',
            'telefono' => 'Teléfono',
            'estado' => 'Estado',
            'hora_llegada' => 'Hora de llegada',
            'personas_llegaron' => 'Personas que llegaron',
            'sin_reserva' => 'Sin reserva',
        ];
    }

    public function haLlegado(): bool
    {
        return $this->estado === self::ESTADO_LLEGO;
    }

    /**
     * Personas a tener en cuenta: las que llegaron, o las reservadas si todavía no llegaron.
     */
    public function personasEfectivas(): int
    {
        return (int)($this->haLlegado() && $this->personas_llegaron ? $this->personas_llegaron : $this->cantidad);
    }

    /**
     * Marca la llegada del grupo (o corrige la cantidad si ya estaba marcado).
     * La hora de llegada se conserva al corregir.
     */
    public function marcarLlegada(int $personas): bool
    {
        if ($personas < 1 || $personas > self::MAX_PERSONAS) {
            $this->addError('personas_llegaron', 'La cantidad de personas no es válida.');
            return false;
        }

        $this->personas_llegaron = $personas;
        if (!$this->haLlegado()) {
            $this->estado = self::ESTADO_LLEGO;
            $this->hora_llegada = date('Y-m-d H:i:s');
        }

        return $this->save(false, ['estado', 'hora_llegada', 'personas_llegaron']);
    }

    public function deshacerLlegada(): bool
    {
        $this->estado = self::ESTADO_PENDIENTE;
        $this->hora_llegada = null;
        $this->personas_llegaron = null;

        return $this->save(false, ['estado', 'hora_llegada', 'personas_llegaron']);
    }

    /**
     * Mozos que atienden al grupo.
     */
    public function getMozos()
    {
        return $this->hasMany(\common\models\User::class, ['id' => 'mozo_id'])
            ->viaTable('{{%reserva_mozo}}', ['reserva_id' => 'id'])
            ->orderBy(['username' => SORT_ASC]);
    }

    /**
     * Carga de trabajo de cada mozo en una fecha: grupos y personas
     * (las que llegaron, o las reservadas si el grupo todavía no llegó).
     *
     * @return array [mozo_id => ['username' => string, 'grupos' => int, 'personas' => int]]
     */
    public static function cargaMozos(string $fecha): array
    {
        $filas = (new \yii\db\Query())
            ->select([
                'u.id', 'u.username',
                'grupos' => 'COUNT(r.id)',
                'personas' => "COALESCE(SUM(CASE WHEN r.estado = 'llego' AND r.personas_llegaron IS NOT NULL
                                             THEN r.personas_llegaron ELSE r.cantidad END), 0)",
            ])
            ->from(['u' => '{{%user}}'])
            ->leftJoin(['rm' => '{{%reserva_mozo}}'], 'rm.mozo_id = u.id')
            ->leftJoin(['r' => self::tableName()], ['and', 'r.id = rm.reserva_id', ['r.fecha' => $fecha]])
            ->where(['u.rol' => \common\models\User::ROL_MOZO, 'u.status' => \common\models\User::STATUS_ACTIVE])
            ->groupBy(['u.id', 'u.username'])
            ->orderBy(['u.username' => SORT_ASC])
            ->all();

        $carga = [];
        foreach ($filas as $fila) {
            $carga[(int)$fila['id']] = [
                'username' => $fila['username'],
                'grupos' => (int)$fila['grupos'],
                'personas' => (int)$fila['personas'],
            ];
        }
        return $carga;
    }

    /**
     * @return ReservaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReservaQuery(get_called_class());
    }
}
