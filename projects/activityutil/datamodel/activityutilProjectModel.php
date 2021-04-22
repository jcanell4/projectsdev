<?php
/**
 * activityutilProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class activityutilProjectModel extends MultiContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function generateProject() {} //abstract obligatorio

    public function directGenerateProject() {
        //4. Establece la marca de 'proyecto generado'
        return $this->projectMetaDataQuery->setProjectGenerated();
    }
    
    /**
     * Obtiene la lista de ficheros creados, por el autor, en el proyecto
     * @return array de ficheros
     */
    public function llistaDeEspaiDeNomsDeDocumentsDelProjecte() {
        //datos del proyecto almacenados
        $dataProject = $this->getCurrentDataProject();
        $id = str_replace(":", "_", $this->getId());
        //lista de campos susceptibles de ser tablas que contienen listas de ficheros
        $exportFields = $this->getMetaDataExport("fields", "main");
        if (is_array($exportFields)) {
            foreach ($exportFields as $f) {
                //obtención de los nombres de cada fichero
                $arrField = json_decode($dataProject[$f['field']], true);
                if (is_array($arrField)) {
                    foreach ($arrField as $n) {
                        $files[] = $id."_".$n['nom'].$f['ext'];
                    }
                }
            }
        }
        if (is_array($files)) {
            $files = array_unique($files);
        }
        $exportFields = $this->getMetaDataExport();
        return $files;
    }

}
