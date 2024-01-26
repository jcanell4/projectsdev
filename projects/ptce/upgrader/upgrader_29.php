<?php
/**
 * upgrader_29: Transforma el archivo continguts.tx del proyecto 'ptfploe'
 *              desde la versión 28 a la versión 29
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_29 extends CommonUpgrader {

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

                $aTokRep = [["(<WIOCCL:IF condition=\"\!\{#_IS_STR_EMPTY\(''\{##dataPaf21##\}''\)_#}\">\n)(.*)(\n<\/WIOCCL:IF>)",
                             "$2"],
                            ["( o \{#_DATE\(\"\{##dataPaf12##\}\"\)_#\})",
                             "<WIOCCL:IF condition=\"!{#_IS_STR_EMPTY(''{##dataPaf12##}'')_#}\">$1</WIOCCL:IF>"],
                            ["( o \{#_DATE\(\"\{##dataPaf22##\}\"\)_#\})",
                             "<WIOCCL:IF condition=\"!{#_IS_STR_EMPTY(''{##dataPaf22##}'')_#}\">$1</WIOCCL:IF>"]
                           ];

                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
