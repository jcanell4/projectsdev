<?php
/**
 * ToReviseProjectAction: L'Autor marca el projecte apte per revisar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToReviseProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // L'Autor marca el projecte apte per revisar: canvi data i signatura Autor i afegeix canvi a l'històric
        $projectMetaData['cc_dadesAutor']['dataDeLaGestio'] = date("Y-m-d");
        $projectMetaData['cc_dadesAutor']['signatura'] = "signat";
        if (!is_array($projectMetaData['cc_historic'])) $projectMetaData['cc_historic'] = [];
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
