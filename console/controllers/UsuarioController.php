<?php

namespace console\controllers;

use common\models\User;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Gestión de usuarios desde la consola.
 */
class UsuarioController extends Controller
{
    /**
     * Crea un usuario de personal ya activo.
     *
     * Ejemplo: php yii usuario/crear admin admin@local.com MiClave123 administrador
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $rol administrador | recepcionista | mozo
     */
    public function actionCrear($username, $email, $password, $rol = User::ROL_ADMIN)
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->rol = $rol;
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($password);
        $user->generateAuthKey();

        if (!$user->save()) {
            $this->stderr("No se pudo crear el usuario:\n", Console::FG_RED);
            foreach ($user->getFirstErrors() as $attr => $error) {
                $this->stderr("  - $attr: $error\n");
            }
            return ExitCode::DATAERR;
        }

        $this->stdout("Usuario '{$username}' creado como {$user->getRolNombre()}.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Asigna (o cambia) la categoría de un usuario existente.
     *
     * Ejemplo: php yii usuario/rol david administrador
     *
     * @param string $username
     * @param string $rol administrador | recepcionista | mozo
     */
    public function actionRol($username, $rol)
    {
        $user = User::findOne(['username' => $username]);
        if ($user === null) {
            $this->stderr("No existe el usuario '{$username}'.\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $user->rol = $rol;
        if (!$user->save(true, ['rol', 'updated_at'])) {
            $this->stderr("No se pudo asignar el rol: " . implode(' ', $user->getFirstErrors()) . "\n", Console::FG_RED);
            $this->stderr("Roles válidos: " . implode(', ', array_keys(User::roles())) . "\n");
            return ExitCode::DATAERR;
        }

        if ((int)$user->status !== User::STATUS_ACTIVE) {
            $this->stdout("Atención: la cuenta no está activa (status={$user->status}), no va a poder iniciar sesión.\n", Console::FG_YELLOW);
        }

        $this->stdout("Usuario '{$username}' ahora es {$user->getRolNombre()}.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Lista los usuarios con su categoría y estado.
     */
    public function actionListar()
    {
        foreach (User::find()->orderBy('username')->all() as $user) {
            $this->stdout(sprintf("%-20s %-30s %-15s status=%s\n",
                $user->username, $user->email, $user->getRolNombre(), $user->status));
        }
        return ExitCode::OK;
    }
}
