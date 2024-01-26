<?php
/**
 * upgrader_24: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 23 a la versión 24
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_24 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {

        switch ($type) {
            case "fields":
                //Ver upgrader_8
                $ret = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokIns = [
                            ['regexp' => "^En el cas dels EAF establerts amb metodologia de treball en equip,",
                             'text' => "<WIOCCL:IF condition=\"{##treballEquipEAF##}==true\">\n",
                             'pos' => 0,
                             'modif' => "m"
                            ],
                            ['regexp' => "^\s+\* La recuperació individual fa referència al contingut.*novament en grup\.\n",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => 1,
                             'modif' => "m"
                            ],
                            ['regexp' => "^<WIOCCL:SUBSET subsetvar=\"filtered\".*\{##itemUf\[unitat formativa\]##\}\">$",
                             'text' => "\n<WIOCCL:SET var=\"sum_ponderacio\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##filtered##},''ponderació'')_#}\">",
                             'pos' => 1,
                             'modif' => "m"
                            ],
                            ['regexp' => "^\s+\*\*\*QUF\{##itemUf\[unitat formativa\]##\}.*<\{#_SUBS\(\{#_ARRAY_LENGTH\(\{##filtered##\}\)_#\},1\)_#\}\">\+ <\/WIOCCL:IF><\/WIOCCL:FOREACH>\*\*$",
                             'text' => "\n</WIOCCL:SET>",
                             'pos' => 1,
                             'modif' => "m"
                            ]
                           ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                $aTokRep = [
                            ["^(\s+\*\*\*QUF\{##itemUf\[unitat formativa\]##\})(.*)(\{##item\[ponderació\]##\})(.*)(<\{#_SUBS\(\{#_ARRAY_LENGTH\(\{##filtered##\}\)_#\},1\)_#\}\">\+ <\/WIOCCL:IF><\/WIOCCL:FOREACH>\*\*)$",
                             "$1$2{#_GET_PERCENT({##sum_ponderacio##}, {##item[ponderació]##})_#}$4$5"
                            ]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver (simultànea a l'actualització de 7 a 8 de fields)", $ver);
                }
                break;
        }
        return $ret;
    }

}
