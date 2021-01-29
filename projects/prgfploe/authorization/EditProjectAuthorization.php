<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 *                                  con una autorización por roles y grupos
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class EditProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups = ["admin"];
        $this->allowedRoles[] = ProjectPermission::ROL_AUTOR;;
        $this->allowedRoles[] = ProjectPermission::ROL_REVISOR;
    }

}
