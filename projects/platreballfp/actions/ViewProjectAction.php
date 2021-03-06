<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewProjectAction{

    protected function runAction() {

        $projectModel = $this->getModel();
        $isProjectGenerated = $projectModel->isProjectGenerated();

        if (!$isProjectGenerated) {
            $projectModel->setViewConfigName("firstView");
        }
        $response = parent::runAction();

        if ($isProjectGenerated) {
            $metaDataSubSet = $this->params[ProjectKeys::KEY_METADATA_SUBSET];
            $confProjectType = $this->modelManager->getConfigProjectType();

            //obtenir la ruta de la configuració per a aquest tipus de projecte
            $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile();

            $cfgProjectModel = $confProjectType."ProjectModel";
            $configProjectModel = new $cfgProjectModel($this->persistenceEngine);

            $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                       ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                       ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET
                                    ]);
            //Obtenir les dades de la configuració per a aquest tipus de projecte
            $metaDataConfigProject = $configProjectModel->getCurrentDataProject($metaDataSubSet);

            if ($metaDataConfigProject['arraytaula']) {
                $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
                $anyActual = date("Y");
                $dataActual = new DateTime();
                $dataActual->setTime(0, 0, 0);

                foreach ($arraytaula as $elem) {
                    if ($response[ProjectKeys::KEY_PROJECT_METADATA]['semestre']['value'] == "1") { //$response[ProjectKeys::KEY_PROJECT_METADATA]['semestre']['value'] Semestre actual indicado en los datos del proyecto
                        if ($elem['key']==="inici_semestre_1") {
                            $inici_semestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                        else if ($elem['key']==="fi_semestre_1") {
                            $fi_semestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                    }
                    else if ($response[ProjectKeys::KEY_PROJECT_METADATA]['semestre']['value'] == "2") {
                        if ($elem['key']==="inici_semestre_2") {
                            $inici_semestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                        else if ($elem['key']==="fi_semestre_2") {
                            $fi_semestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                    }
                }

                if ($inici_semestre) {
                    if ($inici_semestre > $fi_semestre) {
                        $inici_semestre = date_sub($inici_semestre, new DateInterval('P1Y'));
                    }
                    $interval = !($dataActual >= $inici_semestre && $dataActual <= $fi_semestre);
                    $response[AjaxKeys::KEY_ACTIVA_UPDATE_BTN] = ($interval) ? "1" : "0";
                }
            }
        }

        return $response;
    }

    /**
     * Retorna una data UNIX a partir de:
     * @param string $diames en format "01/06"
     * @param string $anyActual
     * @return object DateTime
     */
    private function _obtenirData($diames, $anyActual) {
        $mesdia = explode("/", $diames);
        return date_create($anyActual."/".$mesdia[1]."/".$mesdia[0]);
    }

}
