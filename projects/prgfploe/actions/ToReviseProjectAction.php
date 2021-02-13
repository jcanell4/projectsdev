<?php
/**
 * ToReviseProjectAction: L'autor marca el projecte apte per revisar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToReviseProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        $id = $this->params[ProjectKeys::KEY_ID];
        $subSet = "management";

        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($id, $subSet, $this->params['projectType']);
        $metaDataManagement = $metaDataQuery->getDataProject();
        $currentState = $metaDataManagement['workflow']['currentState'];
        $workflowJson = $model->getMetaDataJsonFile(FALSE, "workflow.json", $currentState);
        $newState = ($workflowJson['actions'][$actionCommand]['changeStateTo']) ? $workflowJson['actions'][$actionCommand]['changeStateTo'] : $currentState;

        $newMetaData['changeDate'] = date("Y-m-d");
        $newMetaData['oldState'] = $currentState;
        $newMetaData['newState'] = $newState;
        $newMetaData['changeAction'] = $actionCommand;
        $newMetaData['user'] = WikiIocInfoManager::getInfo("userinfo")['name'];

        $metaDataManagement['stateHistory'][] = $newMetaData;
        $metaDataManagement['workflow']['currentState'] = $newState;
        $metaDataQuery->setMeta(json_encode($metaDataManagement), $subSet, "canvi d'estat");

        // Històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // L'autor marca apte per revisar: canvi data i signatura autor
        $projectMetaData['cc_dadesAutor']['dataDeLaGestio'] = date("Y-m-d");
        $projectMetaData['cc_dadesAutor']['signatura'] = "signat";
        $hist['data'] = date("Y-m-d");
        $hist['autor'] = $this->getUserName($projectMetaData['autor']);
        $hist['modificacions'] = $projectMetaData['cc_raonsModificacio'];
        $projectMetaData['cc_historic'][] = $hist;

        return $projectMetaData;
    }

    private function getUserName($users) {
        global $auth;
        $retUser = "";
        $u = explode(",", $users);
        foreach ($u as $user) {
            $retUser .= $auth->getUserData($user)['name'] . ", ";
        }
        return trim($retUser, ", ");
    }

}
