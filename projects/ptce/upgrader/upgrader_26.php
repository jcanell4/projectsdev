<?php
/**
 * upgrader_26: Transforma el archivo continguts.tx del proyecto 'ptfploe'
 *              desde la versión 25 a la versión 26 (asociado a la actualización de 8 a 9 de los datos del proyecto)
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_26 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [
                    ["(^::table:T10\s+:title:Dates PAFs\s+)(.*\s)*?:::$",
                     "$1:type:pt_taula\n"
                            ."  :footer:La vostra data i hora de la PAF es comunicarà al Taulell de Tutoria.\n"
                            ."^  PAF  ^  Data  ^  Publicació qualificació  ^\n"
                            ."|  1  |  {#_DATE(\"{##dataPaf11##}\")_#} o {#_DATE(\"{##dataPaf12##}\")_#}  |  {#_DATE(\"{##dataQualificacioPaf1##}\")_#}  |\n"
                            ."|  2  |  {#_DATE(\"{##dataPaf21##}\")_#} o {#_DATE(\"{##dataPaf22##}\")_#}  |  {#_DATE(\"{##dataQualificacioPaf2##}\")_#}  |\n"
                            .":::"],
                    ["presencial i obligatòria", "obligatòria"],
                    ["Per poder presentar-s'hi, ..cal confirmar.. l'assistència en el període establert.\\\n\\\n", ""],
                    ["frases V.F", "test, frases V/F"],
                    ["PAF 1 i PAF 2", "convocatòria PAF 1 i convocatòria PAF 2"]
                ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver (simultànea a la actualització de 8 a 9 de fields)", $ver);
                }
                break;
        }
        return $ret;
    }

}
