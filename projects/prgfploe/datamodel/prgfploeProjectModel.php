<?php
/**
 * ptfploeProjectModel
 * @culpable Josep Cañellas
 */
if (!defined("DOKU_INC")) die();

class prgfploeProjectModel extends UniqueContentFileProjectModel{

    public function validateFields($data=NULL){
        $insAvTable = $data["taulaInstrumentsAvaluacio"];
        if(!is_array($insAvTable)){
            $insAvTable = json_decode($insAvTable, TRUE);
        }
        $aaTable = $data["activitatsAprenentatge"];
        if(!is_array($aaTable)){
            $aaTable = json_decode($aaTable, TRUE);
        }
        $nfTable = $data["taulaDadesNuclisFormatius"];
        if(!is_array($nfTable)){
            $nfTable = json_decode($nfTable, TRUE);
        }
        $ufTable = $data["taulaDadesUF"];
        if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
        }
        $totalNFs = array();
        foreach ($aaTable as $item){
            if(!isset($totalNFs[$item["unitat formativa"]])){
                $totalNFs[$item["unitat formativa"]]=array();
            }
            if(!isset($totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]])){
                $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]]=0;
            }
            $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]] += $item["hores"];
        }

        $totalUfs = array();
        if (!empty($nfTable)){
            foreach ($nfTable as $item){
                if ($item["hores"] != $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]]){
                    throw new InvalidDataProjectException(
                        $this->id,
                        sprintf("Les hores del nucli formatiu %s  de la UF %d no coincideixen amb la suma de les hores de les seves activitats d'aprenentatge (hores NF=%d, però suma hoes AA=%d)."
                                ,$item["nucli formatiu"]
                                ,$item["unitat formativa"]
                                ,$item["hores"]
                                ,$totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]])
                    );
                }
                if (!isset($totalUfs[$item["unitat formativa"]])){
                    $totalUfs[$item["unitat formativa"]] = 0;
                }
                $totalUfs[$item["unitat formativa"]] += $item["hores"];
            }
        }

        if (!empty($ufTable)){
            foreach ($ufTable as $item) {
                if ($item["hores"] != $totalUfs[$item["unitat formativa"]]){
                    throw new InvalidDataProjectException(
                        $this->id,
                        sprintf("Les hores de la unitat formativa %s no coincideixen amb la suma de les hores dels seus nuclis foormatius (hores UF=%d, però suma hoes NF=%d)."
                                ,$item["unitat formativa"]
                                ,$item["hores"]
                                , $totalUfs[$item["unitat formativa"]])
                    );
                }
            }
        }

        if(!empty($insAvTable)){
            $sum=[];
            foreach ($insAvTable as $item) {
                if(!isset($sum[$item["unitat formativa"]])){
                    $sum[$item["unitat formativa"]]=0;
                }
                $sum[$item["unitat formativa"]] += $item["ponderacio"];
            }
            foreach ($insAvTable as $item) {
                if($item['tipus']=="PAF" && $item["ponderacio"]/$sum[$item["unitat formativa"]]>0.6){
                    throw new InvalidDataProjectException(
                        $this->id,
                        sprintf("La ponderació de la PAF de la unitat formativa %d pren el valor de %d sobre %d i per tant, supera el llindar del 60%s"
                                ,$item["unitat formativa"]
                                ,$item["ponderacio"]
                                ,$sum[$item["unitat formativa"]]
                                ,"%")
                    );
                }
            }
        }
    }

    public function getErrorFields($data=NULL) {
        $result = array();
        $iaTable = $data["taulaInstrumentsAvaluacio"]['value']; if (!is_array($iaTable)) $iaTable = json_decode($iaTable, TRUE);
        $aaTable = $data["activitatsAprenentatge"]['value'];    if (!is_array($aaTable)) $aaTable = json_decode($aaTable, TRUE);
        $nfTable = $data["taulaDadesNuclisFormatius"]['value']; if (!is_array($nfTable)) $nfTable = json_decode($nfTable, TRUE);
        $ufTable = $data["taulaDadesUF"]['value'];              if (!is_array($ufTable)) $ufTable = json_decode($ufTable, TRUE);

        $totalNFs = array();
        if (!empty($aaTable)) {
            foreach ($aaTable as $item){
                if (!isset($totalNFs[$item["unitat formativa"]])) {
                    $totalNFs[$item["unitat formativa"]] = array();
                }
                if (!isset($totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]])) {
                    $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]] = 0;
                }
                $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]] += $item["hores"];
            }
        }

        $totalUfs = array();
        if (!empty($nfTable)) {
            foreach ($nfTable as $item) {
                if ($item["hores"] != $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]]) {
                    $result['taulaDadesNuclisFormatius'][] = [
                        'id' => $this->id,
                        'field' => 'nucli formatiu',
                        'message' => sprintf("Les hores del nucli formatiu %s de la UF %d no coincideixen amb la suma de les hores de les seves activitats d'aprenentatge (hores NF=%d, però suma hoes AA=%d)."
                                            ,$item["nucli formatiu"]
                                            ,$item["unitat formativa"]
                                            ,$item["hores"]
                                            ,$totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]])
                    ];
                }
                if (!isset($totalUfs[$item["unitat formativa"]])) {
                    $totalUfs[$item["unitat formativa"]] = 0;
                }
                $totalUfs[$item["unitat formativa"]] += $item["hores"];
            }
        }

        if (!empty($ufTable)) {
            foreach ($ufTable as $item) {
                if ($item["hores"] != $totalUfs[$item["unitat formativa"]]){
                    $result['taulaDadesUF'][] = [
                        'id' => $this->id,
                        'field' => 'unitat formativa',
                        'message' => sprintf("Les hores de la unitat formativa %s no coincideixen amb la suma de les hores dels seus nuclis foormatius (hores UF=%d, però suma hoes NF=%d)."
                                            ,$item["unitat formativa"]
                                            ,$item["hores"]
                                            ,$totalUfs[$item["unitat formativa"]])
                    ];
                }
            }
        }

        if (!empty($iaTable)) {
            $sum = [];
            foreach ($iaTable as $item) {
                if (!isset($sum[$item["unitat formativa"]])) {
                    $sum[$item["unitat formativa"]] = 0;
                }
                $sum[$item["unitat formativa"]] += $item["ponderacio"];
            }
            foreach ($iaTable as $item) {
                if ($item['tipus'] == "PAF" && $item["ponderacio"]/$sum[$item["unitat formativa"]] > 0.6) {
                    $result['taulaInstrumentsAvaluacio'][] = [
                        'id' => $this->id,
                        'field' => 'ponderacio',
                        'message' => sprintf("La ponderació de la PAF de la unitat formativa %d pren el valor de %d sobre %d i per tant, supera el llindar del 60%s"
                                            ,$item["unitat formativa"]
                                            ,$item["ponderacio"]
                                            ,$sum[$item["unitat formativa"]]
                                            ,"%")
                    ];
                }
            }
        }

        if (empty($result)) {
            $result['noerror'] = "No s'han detectat errors a les dades del projecte";
        }
        return $result;
    }

    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE) {
        $ufTable = $data["taulaDadesUF"];
        if ($ufTable && !is_array($ufTable)){
            $ufTable = json_decode($ufTable, TRUE);
        }
        $resultatsAprenentatge = $data["resultatsAprenentatge"];
        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
           $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
        }

        if ($ufTable) {
            foreach ($ufTable as $key => $value) {
                if ($ufTable[$key]["ponderació"] == "0"){
                    $ufTable[$key]["ponderació"] = $ufTable[$key]["hores"];
                }
            }
        }
        if ($resultatsAprenentatge) {
            foreach ($resultatsAprenentatge as $key => $value) {
                if ($resultatsAprenentatge[$key]["ponderacio"] == "0"){
                     $resultatsAprenentatge[$key]["ponderacio"] = $resultatsAprenentatge[$key]["hores"];
                }
            }
        }

        $data["taulaDadesUF"] = $ufTable;
        $data["resultatsAprenentatge"] = $resultatsAprenentatge;
        return $data;
    }

    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE) {
        $ufTable = $data["taulaDadesUF"];
        if ($ufTable && !is_array($ufTable)){
            $ufTable = json_decode($ufTable, TRUE);
        }
        $resultatsAprenentatge = $data["resultatsAprenentatge"];
        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
            $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
        }
        $activitatsAprenentatge = $data["activitatsAprenentatge"];
        if ($activitatsAprenentatge && !is_array($activitatsAprenentatge)){
            $activitatsAprenentatge = json_decode($activitatsAprenentatge, TRUE);
        }
        $insAvTable = $data["taulaInstrumentsAvaluacio"];
        if ($insAvTable && !is_array($insAvTable)){
            $insAvTable = json_decode($insAvTable, TRUE);
        }
        //Calcula les hores per bloc i si cal, la pnderació.
        $blocTable = array();
        $total = 0;
        if ($ufTable) {
            $blocTotal = 0;
            $i = 0;
            $size = count($ufTable);
            while($i < $size) {
                $ufTable[$i]["hores"] = $ufTable[$i]["horesMinimes"] + $ufTable[$i]["horesLLiureDisposicio"];
                $total += $ufTable[$i]["hores"];
                $blocTotal += $ufTable[$i]["hores"];
                $currentBloc = $ufTable[$i]["bloc"];
                if ($ufTable[$i]["ponderació"] == $ufTable[$i]["hores"]){
                    $ufTable[$i]["ponderació"] = 0;
                }
                $i++;
                if ($i==$size || $ufTable[$i]['bloc'] != $currentBloc){
                    $blocRow = array();
                    $blocRow["bloc"] = $currentBloc;
                    $blocRow["horesBloc"] = $blocTotal;
                    $blocTable[] = $blocRow;
                    $blocTotal = 0;
                 }
            }
        }

        //calcula les hores pe RA
        $horesRa = array();
        foreach ($activitatsAprenentatge as $value) {
            if (!isset($horesRa[$value["unitat formativa"]])){
                $horesRa[$value["unitat formativa"]]=array();
            }
            if (!isset($horesRa[$value["unitat formativa"]][$value["ra"]])){
                $horesRa[$value["unitat formativa"]][$value["ra"]]=0;
            }
            $horesRa[$value["unitat formativa"]][$value["ra"]]+=$value["hores"];
        }

        if ($resultatsAprenentatge) {
            foreach ($resultatsAprenentatge as $key => $value) {
                $resultatsAprenentatge[$key]["hores"] = $horesRa[$value["uf"]][$value["ra"]];
                if ($resultatsAprenentatge[$key]["ponderacio"] == $resultatsAprenentatge[$key]["hores"]){
                    $resultatsAprenentatge[$key]["ponderacio"] = 0;
                }
            }
        }

        $notaMinimaAC = 10;
        $notaMinimaPAF = 10;
        $notaMinimaEAF = 10;
        $notaMinimaJT = 10;
        if ($insAvTable) {
            foreach ($insAvTable as $item) {
                if ($item["tipus"] == "AC"){
                    if ($notaMinimaAC > $item["notaMinima"]){
                        $notaMinimaAC = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "EAF"){
                    if($notaMinimaEAF > $item["notaMinima"]){
                        $notaMinimaEAF = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "JT"){
                    if ($notaMinimaJT > $item["notaMinima"]){
                        $notaMinimaJT = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "PAF"){
                    if($notaMinimaPAF > $item["notaMinima"]){
                        $notaMinimaPAF = $item["notaMinima"];
                    }
                }
            }
        }
        if ($notaMinimaAC == 10){
            $notaMinimaAC = 0;
        }
        if ($notaMinimaPAF == 10){
            $notaMinimaPAF = 4;
        }
        if ($notaMinimaEAF == 10){
            $notaMinimaEAF = 4;
        }
        if ($notaMinimaJT == 10){
            $notaMinimaJT = 0;
        }

        $data["resultatsAprenentatge"] = $resultatsAprenentatge;
        $data["taulaDadesUF"] = $ufTable;
        $data["taulaDadesBlocs"] = $blocTable;
        $data["durada"] = $total;
        $data["notaMinimaAC"] = $notaMinimaAC;
        $data["notaMinimaEAF"] = $notaMinimaEAF;
        $data["notaMinimaJT"] = $notaMinimaJT;
        $data["notaMinimaPAF"] = $notaMinimaPAF;

        // Dades de la gestió de la darrera modificació
        $this->dadesActualsGestio($data);

        // Històric del control de canvis
        // Ver class QualityProjectAction
        $this->addHistoricGestioDocument($data);

        return $data;
    }

    private function dadesActualsGestio(&$data) {
        if ($data['autor']) $data['cc_dadesAutor']['nomGestor'] = $this->getUserName($data['autor']);
        if ($data['revisor']) $data['cc_dadesRevisor']['nomGestor'] = $this->getUserName($data['revisor']);
        if ($data['validador']) $data['cc_dadesValidador']['nomGestor'] = $this->getUserName($data['validador']);
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

    /**
     * @override Guarda los datos en el momento de la cración
     * @param array $toSet (s'ha generat a l'Action corresponent)
     */
    public function createData($toSet) {
        parent::createData($toSet);

        //Creació de l'arxiu de metadades corresponent al workflow
        $subSet = "management";
        $metaDataQuery = $this->getPersistenceEngine()->createProjectMetaDataQuery($this->id, $subSet, $this->projectType);
        $metaDataManagement['workflow']['currentState'] = "creating";
        $metaDataQuery->setMeta(json_encode($metaDataManagement), $subSet, "creació", NULL);
    }

}
