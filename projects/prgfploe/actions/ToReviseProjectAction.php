<?php
/**
 * ToReviseProjectAction: L'autor marca el projecte apte per revisar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToReviseProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();

        // Històric del control de canvis
        $data = $model->getCurrentDataProject(FALSE, FALSE);
        // L'autor marca apte per revisar
        //canvi data i signatura autor
        $data['cc_dadesAutor']['dataDeLaGestio'] = date("Y-m-d");
        $data['cc_dadesAutor']['signatura'] = "signat";
        $hist['data'] = date("Y-m-d");
        $hist['autor'] = $this->getUserName($data['autor']);
        $hist['modificacions'] = $data['cc_raonsModificacio'];
        $data['cc_historic'][] = $hist;

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
