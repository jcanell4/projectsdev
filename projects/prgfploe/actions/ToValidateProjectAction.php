<?php
/**
 * ToValidateProjectAction: El Revisor marca el projecte com a revisat apte per validar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToValidateProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // El Revisor marca el projecte com a revisat: canvi data i signatura del Revisor
        $projectMetaData['cc_dadesRevisor']['dataDeLaGestio'] = date("Y-m-d");
        $projectMetaData['cc_dadesRevisor']['signatura'] = "signat";

        return $projectMetaData;
    }

}
