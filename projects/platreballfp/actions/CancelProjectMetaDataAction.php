<?php
if (!defined('DOKU_INC')) die();
//if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
//include_once DOKU_PLUGIN . "projectsdev/projects/platreballfp/actions/ViewProjectMetaDataAction.php";

class CancelProjectMetaDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $response = BasicCancelProjectMetaDataAction::sharedRunAction($this);

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = 0;
        }else {
            $response = parent::runAction();
        }

        return $response;
    }

}
