<?php
/**
 * project 'pblactivity'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");
require_once DOKU_PLUGIN."wikiiocmodel/exporter/BasicExporterClasses.php";

class renderObject extends BasicRenderObject {

}
