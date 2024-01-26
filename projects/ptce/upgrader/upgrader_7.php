<?php
/**
 * upgrader_7: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 6 a la versión 7
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_7 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $matches=array();
                preg_match("/([MC]\d{2})? *-? *(.+)/", $dataProject["modul"], $matches);
                if(empty($matches[1])){
                    $dataProject['modulId']="";
                }else{
                    $dataProject['modulId']=$matches[1];
                }
                $dataProject['modul'] = $matches[2];

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                /* Buscar y Sustituir en el archivo 'continguts'
                 * 1-B) s'ha d'obtenir una qualificació **mínima de 4,00 sense arrodoniment
                 * 1-S) s'ha d'obtenir una qualificació **mínima de {##notaMinimaEAF##},00 sense arrodoniment
                 * 2-B) es necessita una nota mínima de [##TODO: X##],00 sense arrodoniment
                 * 2-S) es necessita una nota mínima de {##notaMinimaJT##},00 sense arrodoniment
                 * 3-B) Ha suspès la convocatòria JT amb una nota inferior a 4,00
                 * 3-S) Ha suspès la convocatòria JT amb una nota inferior a {##notaMinimaJT##},00.
                 * Añadir las siguientes líneas:
                 * 4) antes de: \nS'ofereixen dues convocatòries ordinàries cada semestre: JT i recuperació JT
                 * 4) añadir: <WIOCCL:IF condition="{##hiHaRecuperacioPerJT##}==true">
                 * 5) antes de: \n\n===== Prova d'avaluació final (PAF) =====
                 * 5) añadir: </WIOCCL:IF>
                 */
                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [["obtenir una qualificació.* de 4,00", "obtenir una qualificació **mínima de {##notaMinimaEAF##},00"],
                            ["es necessita una nota mínima de.*,00", "es necessita una nota mínima de {##notaMinimaJT##},00"],
                            ["JT amb una nota inferior a 4,00", "JT amb una nota inferior a {##notaMinimaJT##},00"]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);
                //INSERT
                $aTokIns = [['regexp' => "^S'ofereixen dues .* JT i recuperació JT.*:$",
                               'text' => "<WIOCCL:IF condition=\"{##hiHaRecuperacioPerJT##}==true\">\n",
                               'pos' => 0,
                               'modif' => "m"],
                              ['regexp' => "^\[##TODO: Indiqueu.*aula##].$",
                               'text' => "\n</WIOCCL:IF>",
                               'pos' => 1,
                               'modif' => "m"]
                             ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                if (($status = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $status;
    }

}
