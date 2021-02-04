<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction {

     protected function getDefaultValues(){
        $metaDataValues = parent::getDefaultValues();
        if ($metaDataValues['plantilla']) {
            $metaDataValues['fitxercontinguts'] = $this->params['id'].":".array_pop(explode(":", $metaDataValues['plantilla']));
        }
        return $metaDataValues;
     }
}