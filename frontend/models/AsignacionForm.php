<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\db\IntegrityException;

/**
 * Asigna número de mesa y mozos a un grupo para su día.
 */
class AsignacionForm extends Model
{
    const MESA_MAX = 999;

    /** @var int|string|null */
    public $mesa;

    /** @var int[] */
    public $mozo_ids = [];

    /** @var Reserva */
    private $_reserva;

    public function __construct(Reserva $reserva, $config = [])
    {
        $this->_reserva = $reserva;
        $this->mesa = $reserva->mesa;
        $this->mozo_ids = array_map('intval', $reserva->getMozos()->select('id')->column());
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            ['mesa', 'default', 'value' => null],
            ['mesa', 'integer', 'min' => 1, 'max' => self::MESA_MAX,
                'message' => 'El número de mesa tiene que ser un número.',
                'tooSmall' => 'El número de mesa tiene que ser mayor a 0.'],
            ['mesa', 'validarMesaLibre'],
            ['mozo_ids', 'default', 'value' => []],
            ['mozo_ids', 'each', 'rule' => ['in', 'range' => array_keys(self::mozosDisponibles())]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mesa' => 'Número de mesa',
            'mozo_ids' => 'Mozos',
        ];
    }

    public function validarMesaLibre($attribute)
    {
        if ($this->mesa === null || $this->mesa === '') {
            return;
        }
        $otro = Reserva::find()
            ->where(['fecha' => $this->_reserva->fecha, 'mesa' => (int)$this->mesa])
            ->andWhere(['<>', 'id', $this->_reserva->id])
            ->one();
        if ($otro !== null) {
            $this->addError($attribute, "La mesa {$this->mesa} ya está asignada a {$otro->nombre} ese día.");
        }
    }

    /**
     * Mozos activos: [id => username]
     */
    public static function mozosDisponibles(): array
    {
        return User::find()
            ->select(['username', 'id'])
            ->where(['rol' => User::ROL_MOZO, 'status' => User::STATUS_ACTIVE])
            ->orderBy(['username' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Mesas ya ocupadas ese día por otros grupos: [mesa => nombre]
     */
    public function mesasOcupadas(): array
    {
        return Reserva::find()
            ->select(['nombre', 'mesa'])
            ->where(['fecha' => $this->_reserva->fecha])
            ->andWhere(['not', ['mesa' => null]])
            ->andWhere(['<>', 'id', $this->_reserva->id])
            ->orderBy(['mesa' => SORT_ASC])
            ->indexBy('mesa')
            ->column();
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $reserva = $this->_reserva;
        $db = Reserva::getDb();
        $tx = $db->beginTransaction();
        try {
            $reserva->mesa = $this->mesa === null || $this->mesa === '' ? null : (int)$this->mesa;
            $reserva->save(false, ['mesa']);

            $db->createCommand()->delete('{{%reserva_mozo}}', ['reserva_id' => $reserva->id])->execute();
            $ids = array_unique(array_map('intval', (array)$this->mozo_ids));
            if ($ids) {
                $db->createCommand()->batchInsert('{{%reserva_mozo}}', ['reserva_id', 'mozo_id'],
                    array_map(fn($id) => [$reserva->id, $id], $ids))->execute();
            }

            $tx->commit();
            return true;
        } catch (IntegrityException $e) {
            // Otro usuario tomó la misma mesa al mismo tiempo (índice único fecha+mesa).
            $tx->rollBack();
            $this->addError('mesa', "La mesa {$this->mesa} se acaba de asignar a otro grupo.");
            return false;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function getReserva(): Reserva
    {
        return $this->_reserva;
    }
}
