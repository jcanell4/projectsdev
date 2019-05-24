<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $metaDataSubSet = $this->params[ProjectKeys::KEY_METADATA_SUBSET];

        $projectModel = $this->getModel();
        $response = $projectModel->getDataProject();

        $confProjectType = $this->modelManager->getConfigProjectType();
        //obtenir la ruta de la configuració per a aquest tipus de projecte
        $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile();

        $cfgProjectModel = $confProjectType."ProjectModel";
        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);

        $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                   ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                   ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet
                                ]);


        $projectModel->setTemplateDocuments($response['plantilla'], 'update templates');

        $response[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = !$this->getModel()->validateTemplates()? 1 : 0;

        $response[ProjectKeys::KEY_ID] = str_replace(':','_',$projectModel->getModelAttributes()['id']);



        return $response;
    }

}
