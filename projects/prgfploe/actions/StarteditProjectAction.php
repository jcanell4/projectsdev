<?php
/**
 * QualityProjectAction: gestiona el procés de qualitat
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class StarteditProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();

        // Històric del control de canvis
        $data = $model->getCurrentDataProject(FALSE, FALSE);
        switch ($button) {
            case "inici modificació":
                //eliminar signatures i dates de totes persones
                $data['cc_dadesAutor']['dataDeLaGestio'] = "";
                $data['cc_dadesAutor']['signatura'] = "pendent";
                $data['cc_dadesRevisor']['dataDeLaGestio'] = "";
                $data['cc_dadesRevisor']['signatura'] = "pendent";
                $data['cc_dadesValidador']['dataDeLaGestio'] = "";
                $data['cc_dadesValidador']['signatura'] = "pendent";
                break;
            case "autor marca apte per revisar":
                //canvi data i signatura autor
                $data['cc_dadesAutor']['dataDeLaGestio'] = date("Y-m-d");
                $data['cc_dadesAutor']['signatura'] = "signat";
                $hist['data'] = date("Y-m-d");
                $hist['autor'] = $this->getUserName($data['autor']);
                $hist['modificacions'] = $data['cc_raonsModificacio'];
                $data['cc_historic'][] = $hist;
                break;
            case "revisor marca apte per validar":
                //canvi data i signatura revisor
                $data['cc_dadesRevisor']['dataDeLaGestio'] = date("Y-m-d");
                $data['cc_dadesRevisor']['signatura'] = "signat";
                break;
            case "validador marca validat":
                //demanar data validació (es fa al client en el onClick del botó)
                // canviar data validació a l'històric actual (última entrada)
                //canvi data i signatura validador
                $data['cc_dadesValidador']['dataDeLaGestio'] = $this->params['dataDeLaGestio'];
                $data['cc_dadesValidador']['signatura'] = "signat";
                $data['cc_historic'][count($data['cc_historic'])-1]['data'] = $this->params['dataDeLaGestio'];
                break;
        }

        return $response;
    }

    private function addHistoricGestioDocument(&$data) {
        $data['cc_historic'] = $this->getCurrentDataProject(FALSE, FALSE)['cc_historic'];
        $hist['data'] = date("Y-m-d");
        $hist['autor'] = $this->getUserName($data['autor']);
        $hist['modificacions'] = $data['cc_raonsModificacio'];
        $data['cc_historic'][] = $hist;
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
