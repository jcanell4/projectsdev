<?php
/**
 * ToReviseProjectAction: L'Autor marca el projecte apte per revisar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToReviseProjectAction extends ViewProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // L'Autor marca el projecte apte per revisar: canvi data i signatura Autor i afegeix canvi a l'històric
//        $projectMetaData['cc_dadesAutor']['dataDeLaGestio'] = date("Y-m-d");
//        $projectMetaData['cc_dadesAutor']['signatura'] = "signat";
        $model->updateSignature($projectMetaData, "cc_dadesAutor");
        
//        if (!is_array($projectMetaData['cc_historic'])) $projectMetaData['cc_historic'] = [];
//        $hist['data'] = date("Y-m-d");
//        $hist['autor'] = $this->getUserName($projectMetaData['autor']);
//        $hist['modificacions'] = $projectMetaData['cc_raonsModificacio'];
//        $projectMetaData['cc_historic'][] = $hist;
        $model->modifyLastHistoricGestioDocument($projectMetaData);
        $model->setDataProject($projectMetaData, "Projecte marcat per a ser revisat");
        $response = parent::responseProcess();
        return $response;
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
