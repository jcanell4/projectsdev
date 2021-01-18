<?php
/**
 * Permission: la clase gestiona los permisos de usuario en este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

class Permission extends ProjectPermission {
    const ROL_REVISOR = "revisor";
    const ROL_VALIDADOR = "validador";

    const ROL_REVISOR_ORDER = 3;
    const ROL_VALIDADOR_ORDER = 4;

    protected $revisor = [];
    protected $validador = [];

    public function __construct() {
        $this->aRoles[self::ROL_REVISOR] = self::ROL_REVISOR_ORDER;
        $this->aRoles[self::ROL_VALIDADOR] = self::ROL_VALIDADOR_ORDER;
    }
}
