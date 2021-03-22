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
        $model->updateSignature($projectMetaData, "cc_dadesAutor");
        $model->modifyLastHistoricGestioDocument($projectMetaData);
        $model->setDataProject($projectMetaData, "Projecte marcat per a ser revisat");
        $response = parent::responseProcess();
        return $response;
    }

}
