<?php
/**
 * platreballfpProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class platreballfpProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    public function getTemplateContentDocumentId($responseData){
        $plantilla = $responseData[ProjectKeys::KEY_PROJECT_METADATA]["plantilla"]['value'];
        preg_match("/##.*?##/s", $plantilla, $matches);
        $field = substr($matches[0],2,-2);
        $plantilla = preg_replace("/##.*?##/s", $responseData[ProjectKeys::KEY_PROJECT_METADATA][$field]['value'], $plantilla);
        return $plantilla;
    }

    public function generateProject() {
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto

        $plantilla = $this->getTemplateContentDocumentId($ret);
        $destino = $this->getContentDocumentId($ret);

        //1.1 Crea el archivo 'continguts', en la carpeta del proyecto, a partir de la plantilla especificada
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

         //3. Otorga, a cada 'person', permisos adecuados sobre el directorio de proyecto y añade shortcut si no se ha otorgado antes
        $params = $this->buildParamsToPersons($ret[ProjectKeys::KEY_PROJECT_METADATA], NULL);
        $this->modifyACLPageAndShortcutToPerson($params);
        
        //4. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        return $ret;
    }

}
