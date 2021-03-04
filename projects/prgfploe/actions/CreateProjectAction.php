<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction {

    protected function postResponseProcess(&$response) {
        parent::postResponseProcess($response);

        $model = $this->getModel();
        $id = $this->params[ProjectKeys::KEY_ID];
        $subSet = "management";

        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($id, $subSet, $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $metaDataManagement = $metaDataQuery->getDataProject();
        if (!isset($response[ProjectKeys::KEY_ID])) {
            $response[ProjectKeys::KEY_ID] = $this->idToRequestId($id);
        }
        $response[ProjectKeys::KEY_EXTRA_STATE] = [ProjectKeys::KEY_EXTRA_STATE_ID => "workflowState",
                                                   ProjectKeys::KEY_EXTRA_STATE_VALUE => $metaDataManagement['workflow']['currentState']];
    }

}
