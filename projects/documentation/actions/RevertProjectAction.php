<?php
/**
 * Converteix la revisió en el projecte actual (reverteix el el projecte a una versió anterior)
 * @culpable Rafael
 */
if (!defined('DOKU_INC')) die();

class RevertProjectAction extends ProjectAction {

    public function init($modelManager=NULL) {
        parent::init($modelManager);
    }

    protected function startProcess() {
        $this->getModel()->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                 ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                 ProjectKeys::KEY_REV             => $this->params[ProjectKeys::KEY_REV],
                                 ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET]
                               ]);
    }

    /**
     * Envía los datos de la revisión al projectModel para sustituir al proyecto actual
     * @return array con la estructura y los valores del proyecto (la revisión se habrá convertido en el proyecto actual)
     */
    private function localRunProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $model = $this->getModel();

        //sólo se ejecuta si existe el proyecto
        if ($model->existProject()) {

            //$dataProject = $model->getCurrentDataProject();
            $oldPersonsDataProject = $model->getOldPersonsDataProject($id, $this->params[ProjectKeys::KEY_PROJECT_TYPE], $this->params[ProjectKeys::KEY_METADATA_SUBSET]);
            $dataRevision = $model->getDataRevisionProject($this->params[ProjectKeys::KEY_REV]);

            $metaData = [
                ProjectKeys::KEY_ID_RESOURCE => $id,
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET],
                ProjectKeys::KEY_FILTER => $this->params[ProjectKeys::KEY_FILTER],  //opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($dataRevision)
            ];

            $model->setData($metaData);
            $response = $model->getData();

            if (!$model->getNeedGenerateAction() || $model->isProjectGenerated()) {
                $params = $model->buildParamsToPersons($response[ProjectKeys::KEY_PROJECT_METADATA], $oldPersonsDataProject);
                $model->modifyACLPageAndShortcutToPerson($params);
                $model->forceFileComponentRenderization();
            }

            //Elimina todos los borradores dado que estamos haciendo una reversión del proyecto
            $model->removeDraft();
            //afegim les revisions del projecte a la resposta
            $response[ProjectKeys::KEY_REV] = $this->projectModel->getProjectRevisionList(0);
            $fieldRevVersion = $response[ProjectKeys::KEY_REV][$this->params['rev']]['extra'];

            $response['info'] = self::generateInfo("info", WikiIocLangManager::getLang('project_reverted'), $id, -1, $this->params[ProjectKeys::KEY_METADATA_SUBSET]);
            $response[ProjectKeys::KEY_ID] = $this->idToRequestId($id . $this->projectModel->getIdSuffix());
        }

        if (!$response) throw new ProjectExistException($id);
        return $response;
    }

    protected function responseProcess() {
        $this->startProcess();
        $ret = $this->localRunProcess();
        return $ret;
    }

}