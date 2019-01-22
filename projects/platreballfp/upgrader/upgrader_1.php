<?php
/**
 * upgrader_1: Transforma los datos del proyecto "platreballfp"
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
        if (!is_array($dataProject))
            $dataProject = json_decode($dataProject, TRUE);

        $name_0 = "activitatsAprenentatge";  //nombre de clave original (versión 0)
        $name_1 = "actvtsAprenentatge";   //nuevo nombre de clave (versión 1)
        $dataChanged = $this->changeFieldName($dataProject, $name_0, $name_1);

        $name_0 = "einesAprenentatge:eina";  //nombre de clave original (versión 0)
        $name_1 = "einesAprenentatge:herramienta";  //nuevo nombre de clave (versión 1)

        //-------- INICIO PRUEBAS -----------------
        ////build array
        //$data = array();
        //$this->buildArrayFromStringTokenized($data, $name_0);
        ////get value
        //$value = $this->getValueArrayFromIndexString($data, $name_0);

        //$pathresult = $this->getKeyPathArray($arrayDataProject, "eina");
        //-------- FIN PRUEBAS -----------------

        $dataChanged = $this->changeFieldNameArray($dataProject, $name_0, $name_1);

        //$this->model->setDataProject(json_encode($dataChanged), "Upgrade: version 0 to 1");
    }

}
