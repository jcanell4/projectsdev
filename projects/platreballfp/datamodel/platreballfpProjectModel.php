<?php
/**
 * platreballfpProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class platreballfpProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getMetaDataProject();
        return $ret['fitxercontinguts'];
    }

    public function getContentDocumentId($responseData){
        if ($responseData['projectMetaData']["fitxercontinguts"]['value']){
            $contentName = $responseData['projectMetaData']["fitxercontinguts"]['value'];
        }else{
            $contentName = end(explode(":", $this->getTemplateContentDocumentId($responseData)));
        }
        return $this->id.":" .$contentName;
    }

    public function getTemplateContentDocumentId($responseData){
        $plantilla = $responseData['projectMetaData']["plantilla"]['value'];
        preg_match("/##.*?##/s", $plantilla, $matches);
        $field = substr($matches[0],2,-2);
        $plantilla = preg_replace("/##.*?##/s", $responseData['projectMetaData'][$field]['value'], $plantilla);
        return $plantilla;
    }

    public function generateProject() {
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto

        $plantilla = $this->getTemplateContentDocumentId($ret);
        $destino = $this->getContentDocumentId($ret);

        //1.1 Crea el archivo 'continguts', en la carpeta del proyecto, a partir de la plantilla especificada
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        $aAutors = preg_split("/[\s,]+/", $ret['projectMetaData']["autor"]['value']);
        foreach ($aAutors as $autor) {
            //2a. Otorga, a cada Autor, permisos sobre el directorio de proyecto
            PagePermissionManager::updatePagePermission($this->id.":*", $autor, AUTH_UPLOAD);

            //3a. Otorga permisos a cada Autor sobre su propio directorio (en el caso de que no los tenga)
            $ns = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$autor.":";
            PagePermissionManager::updatePagePermission($ns."*", $autor, AUTH_DELETE, TRUE);
            //3b. Incluye la página del proyecto en el archivo de atajos del Autor
            $params = [
                 'id' => $this->id
                ,'link_page' => $this->id
                ,'user_shortcut' => $ns.WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
            ];
            $this->includePageProjectToUserShortcut($params);
        }

        //2b. Otorga, a los Responsables, permisos sobre el directorio de proyecto
        $aResponsables = preg_split("/[\s,]+/", $ret['projectMetaData']["responsable"]['value']);
        foreach ($aResponsables as $responsable) {
            if (! in_array($responsable, $aAutors)) {
                PagePermissionManager::updatePagePermission($this->id.":*", $responsable, AUTH_UPLOAD);
            }
        }

        //4. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        return $ret;
    }

    /**
     * Modifica los permisos en el fichero de ACL y la página de atajos del autor
     * cuando se modifica el autor o el responsable del proyecto
     * @param array $parArr ['id','link_page','old_autor','old_responsable','new_autor','new_responsable','userpage_ns','shortcut_name']
     */
    public function modifyACLPageToUser($parArr) {
        parent::modifyACLPageToUser($parArr);
    }



}
