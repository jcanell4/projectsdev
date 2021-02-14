<?php
/**
 * ValidateProjectAction: El Validador marca el projecte com a validat apte per modificar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ValidateProjectAction extends ProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // El Validador marca el projecte com a validat: canvi data i signatura Validador
        $projectMetaData['cc_dadesValidador']['dataDeLaGestio'] = $this->params['data_validacio'];
        $projectMetaData['cc_dadesValidador']['signatura'] = "signat";
        $projectMetaData['cc_historic'][count($projectMetaData['cc_historic'])-1]['data'] = $this->params['data_validacio'];

        return $projectMetaData;
    }

}
