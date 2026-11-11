<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Formulario de alta / edición de personal desde el panel.
 */
class UsuarioForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $rol;
    public $status = User::STATUS_ACTIVE;

    /** @var User */
    private $_user;

    public function __construct(?User $user = null, $config = [])
    {
        $this->_user = $user ?? new User();
        if (!$this->_user->isNewRecord) {
            $this->username = $this->_user->username;
            $this->email = $this->_user->email;
            $this->rol = $this->_user->rol;
            $this->status = $this->_user->status;
        }
        parent::__construct($config);
    }

    public function rules()
    {
        $excluirActual = $this->_user->isNewRecord ? null : ['<>', 'id', $this->_user->id];

        return [
            [['username', 'email'], 'trim'],
            [['username', 'email', 'rol', 'status'], 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'targetClass' => User::class, 'filter' => $excluirActual,
                'message' => 'Ese nombre de usuario ya existe.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'filter' => $excluirActual,
                'message' => 'Ese email ya está registrado.'],
            ['password', 'required', 'when' => function () {
                return $this->_user->isNewRecord;
            }],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['rol', 'in', 'range' => array_keys(User::roles())],
            ['status', 'in', 'range' => [User::STATUS_ACTIVE, User::STATUS_INACTIVE]],
            [['rol', 'status'], 'validarNoAutoBloqueo'],
        ];
    }

    /**
     * Evita que un administrador se quite a sí mismo el rol o se desactive.
     */
    public function validarNoAutoBloqueo($attribute)
    {
        if ($this->_user->isNewRecord || (int)$this->_user->id !== (int)Yii::$app->user->id) {
            return;
        }
        if ($attribute === 'rol' && $this->rol !== User::ROL_ADMIN) {
            $this->addError($attribute, 'No podés quitarte el rol de administrador.');
        }
        if ($attribute === 'status' && (int)$this->status !== User::STATUS_ACTIVE) {
            $this->addError($attribute, 'No podés desactivar tu propia cuenta.');
        }
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Usuario',
            'email' => 'Email',
            'password' => $this->_user->isNewRecord ? 'Contraseña' : 'Nueva contraseña (dejar vacío para no cambiarla)',
            'rol' => 'Categoría',
            'status' => 'Estado',
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->rol = $this->rol;
        $user->status = (int)$this->status;
        if ($this->password !== null && $this->password !== '') {
            $user->setPassword($this->password);
        }
        if ($user->isNewRecord) {
            $user->generateAuthKey();
        }

        return $user->save();
    }

    public function getUser(): User
    {
        return $this->_user;
    }
}
