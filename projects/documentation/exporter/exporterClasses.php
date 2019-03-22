<?php
/**
 * projecte 'documentation'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");
require_once DOKU_PLUGIN."wikiiocmodel/exporter/BasicExporterClasses.php";

class renderObject extends BasicRenderObject {

    /**
     * @param array $data : array correspondiente al campo actual del archivo de datos del proyecto
     * @return datos renderizados
     */
    public function process($data) {
        $this->data = $data;
        $campos = $this->getRenderFields();
        foreach ($campos as $keyField) {
            $typedefKeyField = $this->getTypedefKeyField($keyField);
            $renderKeyField = $this->getRenderKeyField($keyField);
            $render = $this->createRender($typedefKeyField, $renderKeyField);

            $dataField = $this->getDataField($keyField);
            $render->init($keyField);
            $arrayDeDatosParaLaPlantilla[$keyField] = $render->process($dataField);
        }
        $ret = $this->cocinandoLaPlantillaConDatos($arrayDeDatosParaLaPlantilla);
        return $ret;
    }

    public function getRenderFields() { //devuelve el array de campos establecidos para el render
        return $this->getRenderDef('render')['fields'];
    }
}
