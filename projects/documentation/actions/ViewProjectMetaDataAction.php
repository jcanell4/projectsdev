<?php
if (!defined('DOKU_INC')) die();

class ViewProjectMetaDataAction extends BasicViewProjectMetaDataAction{

    protected function runAction() {
        $response = parent::runAction();

        $confProjectType = $this->modelManager->getConfigProjectType();

        //obtenir la ruta de la configuració per a aquest tipus de projecte
        $projectTypeConfigFile = $this->projectModel->getProjectTypeConfigFile();

        $cfgProjectModel = $confProjectType."ProjectModel";
        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);

        $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                   ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                   ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET
                                ]);

        $metaDataConfigProject = $configProjectModel->getCurrentDataProject();

        if ($metaDataConfigProject['arraytaula']) {
            $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);

            foreach ($arraytaula as $e) {
                $elem = json_decode(json_encode($e), TRUE);
                if ($elem['key'] === "actiu") {
                    $actiu = ($elem['value'] === "Sí");
                }
            }

            if ($actiu === FALSE) {
                $response['actiu'] = "Sí";
            }
        }

        return $response;
    }

}
