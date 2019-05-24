<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectMetaDataAction {
    protected function runAction() {
        $response = parent::runAction();

        $projectModel = $this->getModel();
        $projectModel->setTemplateDocuments($response['plantilla']);
        $response[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = !$this->getModel()->validateTemplates()? 1 : 0;

        return $response;
    }

}
