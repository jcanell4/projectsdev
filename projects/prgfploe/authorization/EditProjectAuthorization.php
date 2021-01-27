<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y
 * que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class EditProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups = ["admin"];
        $this->allowedRoles[] = Permission::ROL_AUTOR;;
        $this->allowedRoles[] = Permission::ROL_REVISOR;
    }

}
