<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/BasicViewProjectMetaDataAction.php";

class ViewProjectMetaDataAction extends BasicViewProjectMetaDataAction{

    protected function runAction() {
        $response = parent::runAction();

        $metaDataSubSet = $this->params[ProjectKeys::KEY_METADATA_SUBSET];
        $confProjectType = $this->modelManager->getConfigProjectType();

        //obtenir la ruta de la configuració per a aquest tipus de projecte
        $projectTypeConfigFile = $this->projectModel->getProjectTypeConfigFile();

        $cfgProjectModel = $confProjectType."ProjectModel";
        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);

        $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                   ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                   ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
                                ]);

        $configResponse = $configProjectModel->getData();

        if ($configResponse['projectMetaData']['arraytaula']['value']) {
            $arraytaula = json_decode($configResponse['projectMetaData']['arraytaula']['value']);

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
