<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
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

        //Obtenir les dades de la configuració d'aquest tipus de projecte
        $metaDataSubset = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;
        $metaDataConfigProject = $configProjectModel->getMetaDataProject($metaDataSubset);



        // TODO: Sobreescriure els fitxers del projecte amb les plantilles?
        // TODO: Actualitzar la configuració del fitxer amb les dades de les plantilles.
        //$projectModel->createTemplateDocument($response); // Es sobreescriuen?
        $projectModel->setTemplateDocuments($response['plantilla']);

        $response[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = !$this->getModel()->validateTemplates()? 1 : 0;

        $response[ProjectKeys::KEY_ID] = $projectModel->getModelAttributes()['id'];

        return $response;
    }

}
