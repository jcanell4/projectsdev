<?php
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends AbstractWikiAction {

    public function responseProcess() {
        // Comprobar que el tipo de proyecto a importar es adecuado
        // Verificar permisos sobre el proyecto a importar
        /*
        $modelManager = AbstractModelManager::Instance($projectType_a_importar);
        $buttonAuthorization = $modelManager->getAuthorizationManager('editProject');
         */

        /*
         * getModelManager()
        $model = $plugin_controller->getCurrentProjectModel("management");

        $dataProject = $model->getCurrentDataProject("management", FALSE);
        $state = $dataProject['workflow']['currentState'];

        $jsonConfig = $model->getMetaDataJsonFile(FALSE, "workflow.json", $state);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $permissions = $jsonConfig['actions'][$actionCommand]['permissions'];

         */
        throw new Exception("Action no definida");
    }

}
