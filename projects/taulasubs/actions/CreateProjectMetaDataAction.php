<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction {

     protected function getDefaultValues(){
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues['fitxercontinguts'] = $id.":".array_pop(explode(":", $metaDataValues['plantilla']));
        return $metaDataValues;
     }
}