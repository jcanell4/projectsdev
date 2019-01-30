<?php
/**
 * upgrader_1: Transforma los datos del proyecto "documentation"
 *             desde la estructura de la versión 0 a la estructura de la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process() {
        $dataProject = $this->model->getMetaDataProject($this->metaDataSubSet);

        //Cambiar el nombre de un campo del primer nivel
        $name0 = "addressmedia";  //nombre de clave original (versión 0)
        $name1 = "media_address";   //nuevo nombre de clave (versión 1)
        $dataChanged = $this->changeFieldName($dataProject, $name0, $name1);
        //$this->model->setDataProject(json_encode($dataChanged), "Upgrade: version 0 to 1");
    }

}
