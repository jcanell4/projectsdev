<?php
/**
 * msfillProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class msfillProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function generateProject() {} //abstract obligatorio

    public function directGenerateProject($data) {
        //4. Establece la marca de 'proyecto generado'
        $ret = $this->projectMetaDataQuery->setProjectGenerated();
        return $ret;
    }

}
