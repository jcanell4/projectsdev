<?php
if (!defined('DOKU_INC')) die();

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction {

     protected function getDefaultValues(){
        $metaDataValues = parent::getDefaultValues();
        if ($metaDataValues['plantilla']) {
            $metaDataValues['fitxercontinguts'] = $this->params['id'].":".array_pop(explode(":", $metaDataValues['plantilla']));
        }
        return $metaDataValues;
     }
}