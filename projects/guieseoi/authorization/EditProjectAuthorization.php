<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y
 * que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
defined('DOKU_INC') || die();

class EditProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups = ["admin", "eoimanager"];
        $this->allowedRoles[] = ProjectPermission::ROL_AUTOR;
        //[marjose copiat de RAFA] Aquesta assignació elimina els altres grups (desconec el motiu)
        // específicament el grup "manager" assignat per la classe SupervisorProjectAuthorization
        //Josep proposa eliminar aquest editProjectAuthorization.php de guieseoi.php 
        // perquè el que ja tenim - és a dir, el pare- és restrictiu. 
    }

}
