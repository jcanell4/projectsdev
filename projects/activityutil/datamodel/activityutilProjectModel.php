<?php
/**
 * activityutilProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class activityutilProjectModel extends MultiContentFilesProjectModel {

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }

    public function generateProject(){}

    public function directGenerateProject() {
        return $this->projectMetaDataQuery->setProjectGenerated();
    }

    /**
     * Gestiona la llista de documents definits per l'usuari
     * @param array $data : dades del projecte (camps del formulari actiu)
     */
    public function validateFields($data=NULL){
        if ($data) {
            $nousDocuments = json_decode($data['documents'], true);
            
            $dataProject = $this->getCurrentDataProject();
            $vellsDocuments = json_decode($dataProject['documents'], true);

            $id = $this->getId();
            $path_continguts = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $id);

            foreach ($nousDocuments as $k => $doc) {
                if (!isset($vellsDocuments[$k]['nom'])) {
                    $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
                }
                else if ($doc['nom'] !== $vellsDocuments[$k]['nom']) {
                    $this->renamePage($id, $path_continguts, $vellsDocuments[$k]['nom'], $doc['nom']);
                }
            }
            //En el cas que s'hagin eliminat files de la taula de documents
            for ($i=$k+1; $i<count($vellsDocuments); $i++) {
                $this->createPageFromTemplate("$id:{$vellsDocuments[$i]['nom']}", NULL, NULL, "remove page");
            }
        }
        else {
            throw new Exception("Aquí passa alguna cosa rara");
        }
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
