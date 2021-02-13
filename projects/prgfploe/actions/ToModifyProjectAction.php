<?php
/**
 * ToModifyProjectAction: Inici del procés de modificació del projecte
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToModifyProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();

        // Històric del control de canvis
        $data = $model->getCurrentDataProject(FALSE, FALSE);
        // inici modificació
        //eliminar signatures i dates de totes persones
        $data['cc_dadesAutor']['dataDeLaGestio'] = "";
        $data['cc_dadesAutor']['signatura'] = "pendent";
        $data['cc_dadesRevisor']['dataDeLaGestio'] = "";
        $data['cc_dadesRevisor']['signatura'] = "pendent";
        $data['cc_dadesValidador']['dataDeLaGestio'] = "";
        $data['cc_dadesValidador']['signatura'] = "pendent";

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
