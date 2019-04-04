<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "manual"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/ProjectFactoryAuthorization.php");

class FactoryAuthorization extends ProjectFactoryAuthorization {
    //const PROJECT_AUTH = DOKU_PLUGIN."wikiiocmodel/projects/manual/authorization/";
    const PROJECT_AUTH = DOKU_PLUGIN."projectsdev/projects/manual/authorization/";

    public function __construct() {
        parent::__construct(self::PROJECT_AUTH);
    }

    public function setAuthorizationCfg() {
        if (empty($this->authCfg)) {
            parent::setAuthorizationCfg();
        }
        $aCfg = ['new_folderProject'   => "notAllowedCommand"
                ];
        $this->authCfg = array_merge($this->authCfg, $aCfg);
    }

}
