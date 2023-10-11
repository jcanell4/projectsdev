<?php
/**
 * projecte 'documentation'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class renderObject extends BasicRenderObject {

    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->cfgExport->rendererPath = dirname(realpath(__FILE__));
    }

    public function getRenderFields() { //devuelve el array de campos establecidos para el render
        return $this->getRenderDef('render')['fields'];
    }
}
