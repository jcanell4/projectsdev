<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "pblactivity"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/ProjectFactoryAuthorization.php");

class FactoryAuthorization extends ProjectFactoryAuthorization {

    const PROJECT_AUTH = WIKI_IOC_MODEL . "projects/pblactivity/authorization/";

    public function __construct($projectType=NULL) {
        if ($projectType===NULL) $projectType = "defaultProject";
        parent::__construct(self::PROJECT_AUTH);
    }

}
