<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewProjectAction{

    public function responseProcess() {
        $response = parent::responseProcess();
        $projectModel = $this->getModel();
        $response['generatedZipFiles'] = $projectModel->llistaDeEspaiDeNomsDeDocumentsDelProjecte();
        return $response;
    }

}
