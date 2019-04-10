<?php
/**
 * upgrader_1: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 0 a la estructura de la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename=NULL) {
        switch ($type) {
            case "fields":
                break;

            case "templates":
                // Línia 96.  Es canvia "  :title:Taula Unitats" per "  :title:Apartats"
                // Línia 102. Es canvia "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[inici]##}" per "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[final]##}"
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [["\{\#\#item_act\[descripció\]\#\#\} \\\\ \<\/WIOCCL:FOREACH\>",
                             "{##item_act[descripció]##} \\\\\\\\ </WIOCCL:FOREACH>"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);
                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 2 to 3");
                }
                return !empty($dataChanged);
        }
    }

}
