<?php

namespace common\components;

use yii\filters\AccessRule;

/**
 * Regla de acceso que entiende las categorías de personal guardadas en user.rol.
 *
 * Uso en un controlador:
 *   'access' => [
 *       'class' => AccessControl::class,
 *       'ruleConfig' => ['class' => RolAccessRule::class],
 *       'rules' => [
 *           ['allow' => true, 'roles' => [User::ROL_ADMIN, User::ROL_RECEPCIONISTA]],
 *       ],
 *   ],
 *
 * También acepta '?' (invitado) y '@' (cualquier usuario logueado).
 */
class RolAccessRule extends AccessRule
{
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role === '?' && $user->getIsGuest()) {
                return true;
            }
            if ($role === '@' && !$user->getIsGuest()) {
                return true;
            }
            if (!$user->getIsGuest() && $user->identity->rol === $role) {
                return true;
            }
        }

        return false;
    }
}
