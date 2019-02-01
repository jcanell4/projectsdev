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
        if (!is_array($dataProject)) {
            $dataProject = json_decode($dataProject, TRUE);
        }
        //Cambiar el nombre de un campo del primer nivel
        $name0 = "activitatsAprenentatge";  //nombre de clave original (versión 0)
        $name1 = "actvtsAprntg";            //nuevo nombre de clave (versión 1)
        $dataChanged = $this->changeFieldName($dataProject, $name0, $name1);
        //$this->model->setDataProject(json_encode($dataChanged), "Upgrade: version 0 to 1");

        //Cambiar el nombre de un campo y de las subclaves indicadas en la ruta completa
        $name0 = ['taulaDadesUF', 'unitat formativa'];
        $name1 = ['TUF', 'UF'];
        $dataChanged = $this->changeFieldNameInArray($dataProject, $name0, $name1);
        //$this->model->setDataProject(json_encode($dataChanged), "Upgrade: version 0 to 1");

        // Aplica una nueva plantilla a un documento creado con una plantilla antigua
        $t0 = @file_get_contents("/home/rafael/clone/continguts0.txt");
        $t1 = @file_get_contents("/home/rafael/clone/continguts1.txt");
        $doc = @file_get_contents("/home/rafael/clone/document.txt");
        $dataChanged = $this->updateDocToNewTemplate($t0, $t1, $doc);

        // Aplica una nueva plantilla, con token numerado, a un documento creado con una plantilla antigua
        $t0 = @file_get_contents("/home/rafael/clone/continguts_00.txt");
        $t1 = @file_get_contents("/home/rafael/clone/continguts_01.txt");
        $doc = @file_get_contents("/home/rafael/clone/document_00.txt");
        $dataChanged = $this->updateDocToNewTemplateNumbered($t0, $t1, $doc);
    }


    public function processProves() {
        ////build array
        //$data = array();
        //$this->buildArrayFromStringTokenized($data, $name0);
        ////get value
        //$value = $this->getValueArrayFromIndexString($data, $name0);

        //$pathresult = $this->getKeyPathArray($arrayDataProject, "eina");
    }
}
