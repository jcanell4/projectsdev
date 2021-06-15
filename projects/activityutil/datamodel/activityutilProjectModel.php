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

            $nom_continguts = $this->_getContingutsName();
            $template = $this->getRawProjectTemplate(substr($nom_continguts, 0, -4));
            $path_continguts = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $this->getId());

            foreach ($nousDocuments as $k => $doc) {
                if (!isset($vellsDocuments[$k]['nom'])) {
                    file_put_contents("$path_continguts/{$doc['nom']}.txt", $template);
                }
                else if ($doc['nom'] !== $vellsDocuments[$k]['nom']) {
                    rename("$path_continguts/{$vellsDocuments[$k]['nom']}.txt", "$path_continguts/{$doc['nom']}.txt");
                }
            }
            //En el cas que s'hagin eliminat files de document
            for ($i=$k+1; $i<count($vellsDocuments); $i++) {
                unlink("$path_continguts/{$vellsDocuments[$i]['nom']}.txt");
            }
        }
        else {
            throw new Exception("Aquí passa alguna cosa rara");
        }
    }

    private function _getContingutsName() {
        $project_dir = $this->getProjectTypeDir()."/metadata/plantilles";
        $list = scandir($project_dir);
        foreach ($list as $file) {
            if (is_file("$project_dir/$file")) {
                return $file;
            }
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
