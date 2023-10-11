<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction {

     protected function getDefaultValues(){
        $id = $this->params[ProjectKeys::KEY_ID];
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues['nsproject'] = $id;
        $metaDataValues['fitxercontinguts'] = $id.":".array_pop(explode(":", $metaDataValues['plantilla']));
        return $metaDataValues;
     }
}