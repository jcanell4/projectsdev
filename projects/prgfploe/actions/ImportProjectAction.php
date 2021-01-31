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

        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['id'], "management", $this->params['projectType']);
        $data = $metaDataQuery->getDataProject();
        $jsonConfig = $model->getMetaDataJsonFile(FALSE, "workflow.json", $data['workflow']['currentState']);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $validProjectTypes = $jsonConfig['actions'][$actionCommand]['button']['parms']['DJO']['projectType'];

        if ($importProjectType == $validProjectTypes || in_array($importProjectType, $validProjectTypes)) {
            //
            // 2. Verificar permisos sobre el proyecto a importar
            //
            $permissions = $jsonConfig['actions'][$actionCommand]['permissions'];
            $external_modelManager = AbstractModelManager::Instance($importProjectType);
            $external_authorization = $external_modelManager->getAuthorizationManager('editProject');
            $has_perm_group = $this->array_in_array($permissions['groups'], $external_authorization->getAllowedGroups());
            $has_perm_rol = $this->array_in_array($permissions['rols'], $external_authorization->getAllowedRoles());
            if ($has_perm_group || $has_perm_rol) {
                throw new Exception("Petición correcta: $importProjectType");
            }
        }else {
            throw new Exception("El tipus de projecte $importProjectType no és un tipus vàlid per a la importació.");
        }
        throw new Exception("Action no definida. Petición: $importProjectType");
    }

    // Busca si algún elemento de $array1 está incluido en $array2
    public function array_in_array($array1, $array2) {
        $has = FALSE;
        foreach ($array1 as $elem) {
            if (in_array($elem, $array2)) {
                $has = TRUE;
                break;
            }
        }
        return $has;
    }

}
