<?php
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends ProjectMetadataAction {

    public function responseProcess() {
        //
        // 1. Comprobar que el tipo de proyecto a importar es adecuado
        //
        $model = $this->getModel();
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['id'], "management", $this->params['projectType']);
        $importProjectType = $metaDataQuery->getProjectType($this->params['project_import']);

        $data = $model->getCurrentDataProject("management", FALSE);
        $jsonConfig = $model->getMetaDataJsonFile(FALSE, "workflow.json", $data['workflow']['currentState']);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $validProjectTypes = $jsonConfig['actions'][$actionCommand]['button']['parms']['DJO']['projectType'];

        if ($importProjectType == $validProjectTypes || in_array($importProjectType, $validProjectTypes)) {
            //
            // 2. Verificar permisos sobre el proyecto a importar
            //
            $permissions = $jsonConfig['actions'][$actionCommand]['permissions'];
    //        $modelManager = AbstractModelManager::Instance($importProjectType);
    //        $buttonAuthorization = $modelManager->getAuthorizationManager('editProject');
        }else {
            throw new Exception("El tipus de projecte $importProjectType no és un tipus vàlid per a la importació.");
        }
        throw new Exception("Action no definida. Petición: $importProjectType");
    }

}
