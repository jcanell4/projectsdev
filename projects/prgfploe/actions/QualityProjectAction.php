<?php
/**
 * QualityProjectAction: gestiona el procés de qualitat
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class QualityProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        $id = $this->params[ProjectKeys::KEY_ID];
        $subSet = "management";

        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($id, $subSet, $this->params['projectType']);

        $metaDataManagement = $metaDataQuery->getDataProject();   //$metaDataManagement = ["workflow" => ["currentState" => "creating"]]
        $currentState = $metaDataManagement['workflow']['currentState'];
        $workflowJson = $model->getMetaDataJsonFile(FALSE, "workflow.json", $currentState);  //$workflowJson contiene todo el array de workflow.json
        $newState = $workflowJson['actions'][$actionCommand]['changeStateTo'];

        if ($currentState !== $newState) {
            $newMetaData['changeDate'] = date;
            $newMetaData['oldState'] = $currentState;
            $newMetaData['newState'] = $newState;
            $newMetaData['changeAction'] = "acció provocadora";
            $newMetaData['user'] = "nom usuari que demana acció";

            $metaDataManagement['stateHistory'][] = $newMetaData;

            $model->setMeta(json_encode($metaDataManagement), $subSet, NULL, NULL);
            $response['info'] = self::generateInfo("info", "El canvi d'estat ha finalitzat correctament.", $id);
        }else {
            $response['info'] = self::generateInfo("info", "No hi ha canvi d'estat.", $id);
        }
        return $response;
    }

}
