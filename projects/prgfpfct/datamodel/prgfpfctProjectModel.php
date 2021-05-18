<?php
/**
 * prgfpfctProjectModel
 * @culpable Josep Cañellas
 */
if (!defined("DOKU_INC")) die();

class prgfpfctProjectModel extends UniqueContentFileProjectModel{
    
    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }
    
    public function directGenerateProject() {
        //4. Establece la marca de 'proyecto generado'
        return $this->projectMetaDataQuery->setProjectGenerated();
    }

    public function validateFields($data=NULL){
        //EL responsable no pot ser buit
        if (!isset($data["responsable"]) || empty($data["responsable"])){
            throw new InvalidDataProjectException($this->id, "El camp responsable no pot quedar buit");
        }
    }

    /**
     * Overwrite
     * @param array $data : array de datos del proyecto
     */
    public function createTemplateDocument($data=NULL){
        $dataTemplate = $this->getRawDocument($data['projectMetaData']['fitxercontinguts']['value']);
        $dataTemplate = ":###".preg_replace(["/:###/","/###:/","/~~WIOCCL_DATA.+~~/","/~~FIELD_VERSION:.*?~~/"], "", $dataTemplate)."###:";
        $nomfitxercontinguts = $this->getTemplateContentDocumentId();
        $desti = $this->getContentDocumentId($nomfitxercontinguts);
        $this->getDokuPageModel()->setData([PageKeys::KEY_ID => $desti,
                                             PageKeys::KEY_WIKITEXT => $dataTemplate,
                                             PageKeys::KEY_SUM => "generate project"]);
    }

    public function getErrorFields($data=NULL) {
        $result  = array();
        $raTable = $data["resultatsAprenentatgeObjectiusTerminals"]['value']; if (!is_array($raTable)) $raTable = json_decode($raTable, TRUE);
        
        //Camps obligatoris
        $responseType = "SINGLE_MESSAGE";
        $message = WikiIocLangManager::getLang("El camp %s és obligatori. Cal que %s.");
        $campsAComprovar = [
             ["typeField"=>"SF", "field"=>"departament", "accioNecessaria"=>"hi poseu el nom del departament"]
            ,["typeField"=>"SF", "field"=>"cicle", "accioNecessaria"=>"hi poseu el nom del cicle"]
            ,["typeField"=>"SF", "field"=>"modulId", "accioNecessaria"=>"hi poseu el codi del mòdul"]
            ,["typeField"=>"SF", "field"=>"modul", "accioNecessaria"=>"hi poseu el nom del mòdul"]
            ,["typeField"=>"TF", "field"=>"resultatsAprenentatgeObjectiusTerminals", "accioNecessaria"=>"hi afegiu els resultats d'avalaució o Objectius Terminals"]
            ,["typeField"=>"SF", "field"=>"activitatsFormatives", "accioNecessaria"=>"hi afegiu les Activitats Formatives"]
            ,["typeField"=>"SF", "field"=>"cc_raonsModificacio", "accioNecessaria"=>"hi assigneu una raó per la modificació actual de la programació"]
            // ALERTA! Aquests camps no es corresponen amb els IDs que s'asignen als camps
            ,["typeField"=>"SF", "field"=>"autor", "accioNecessaria"=>"hi assigneu un autor"]
            ,["typeField"=>"OF", "field"=>"cc_dadesAutor#carrec", "accioNecessaria"=>"hi assigneu el càrrec de l'autor"]
            ,["typeField"=>"SF", "field"=>"revisor", "accioNecessaria"=>"hi assigneu un revisor"]
            ,["typeField"=>"OF", "field"=>"cc_dadesRevisor#carrec", "accioNecessaria"=>"hi assigneu el càrrec del revisor"]
            ,["typeField"=>"SF", "field"=>"validador", "accioNecessaria"=>"hi assigneu un validador"]
            ,["typeField"=>"OF", "field"=>"cc_dadesValidador#carrec", "accioNecessaria"=>"hi assigneu el càrrec del validador"]
        ];
        foreach ($campsAComprovar as $item) {
            if ($item["typeField"]=="SF" && (!isset($data[$item["field"]]) || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
                $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                
            }elseif($item["typeField"]=="TF" && (!isset($data[$item["field"]]) || empty ($data[$item["field"]]["value"]) || $data[$item["field"]]["value"]=="[]" || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
                $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                
            }else if($item["typeField"]=="OF"){
                $keys = explode("#", $item["field"]);
                $error=false;
                $dataf = $data;
                for($i=0; !$error && $i<count($keys); $i++){
                    if(!isset($dataf[$keys[$i]]) || $dataf[$keys[$i]]["value"]==$dataf[$keys[$i]]["default"]){
                        $error=true;
                    }else{
                        $dataf = $dataf[$keys[$i]]["value"];
                    }
                }
                if($error){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["fieldName"] ? $item["fieldName"] : $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                     
                }
            }
        }
        
        $verificadorRA = [];
        if(!empty($raTable)){
            foreach ($raTable as $item) {
                if(!isset($verificadorRA[$item["uf"]])){
                    $verificadorRA[$item["uf"]] =[];
                }
                if(!isset($verificadorRA[$item["uf"]][$item["ra"]])){
                    $verificadorRA[$item["uf"]][$item["ra"]] = false;
                }
            }
        }
        
        //Comprovat RAs
        foreach ($verificadorRA as $uf => $v) {
            foreach ($verificadorRA[$uf] as $ra => $assigned) {
                if(!$assigned){
                    //Error CA NO assignat
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'activitatsAprenentatge',
                        'message' => sprintf("El RA %d de la UF %d no es troba assignat a cap registre de la taula d'activitats d'aprenentatge (activitatsAprenentatge)."
                                            ,$ra
                                            ,$uf)
                    ];                    
                }
            }
        }

        if(!empty($raTable)){
            $horesIgualPonderacioRA = [];
            $ponderacioRA=[];
            foreach ($raTable as $item) {
                if(!isset($horesIgualPonderacioRA[$item["uf"]])){
                    $horesIgualPonderacioRA[$item["uf"]] =true;
                    $ponderacioRA[$item["uf"]] =0;
                }
                $horesIgualPonderacioRA[$item["uf"]] = $horesIgualPonderacioRA[$item["uf"]] && $item["hores"] == $item["ponderacio"];
                $ponderacioRA[$item["uf"]] += $item["ponderacio"];
            }            
        }

        if(!empty($horesIgualPonderacioRA)){
            foreach ($horesIgualPonderacioRA as $key => $value) {
                if(!$horesIgualPonderacioRA[$key] && $ponderacioRA[$key]!=100){
                    $result["WARNING"][] = [
                        'responseType' => $responseType,
                        'field' => 'resultatsAprenentatgeObjectiusTerminals',
                        'message' => sprintf("A la taula dels resultats d'aprenentatge (resultatsAprenentatgeObjectiusTerminals), la suma de les ponderacions dels RA de la unitat formativa %d no és 100 i els seus valors no coincidèixen amb les hores. Reviseu si es tracta d'un error."
                                            ,$key)
                    ];
                }
            }
            
        }
        
        if (empty($result)) {
            $responseType = "NOERROR";
            $result[$responseType] = WikiIocLangManager::getLang("No s'han detectat errors a les dades del projecte");
        }
        return $result;
    }

    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE) {
        $resultatsAprenentatge = $data["resultatsAprenentatgeObjectiusTerminals"];
        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
           $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
        }

        if ($resultatsAprenentatge) {
            foreach ($resultatsAprenentatge as $key => $value) {
                if ($resultatsAprenentatge[$key]["ponderacio"] == "0"){
                     $resultatsAprenentatge[$key]["ponderacio"] = $resultatsAprenentatge[$key]["hores"];
                }
            }
        }

        $data["resultatsAprenentatgeObjectiusTerminals"] = $resultatsAprenentatge;
        return $data;
    }

    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE) {
        $resultatsAprenentatge = $data["resultatsAprenentatgeObjectiusTerminals"];
        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
            $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
        }
        //Calcula les hores per bloc i si cal, la pnderació.
        $total = 0;

        //calcula les hores pe RA
        $horesRa = array();

        if ($resultatsAprenentatge) {
            foreach ($resultatsAprenentatge as $key => $value) {
                $resultatsAprenentatge[$key]["hores"] = $horesRa[$value["uf"]][$value["ra"]];
                if ($resultatsAprenentatge[$key]["ponderacio"] == $resultatsAprenentatge[$key]["hores"]){
                    $resultatsAprenentatge[$key]["ponderacio"] = 0;
                }
            }
        }

        $data["resultatsAprenentatgeObjectiusTerminals"] = $resultatsAprenentatge;
        $data["durada"] = $total;

        // Dades de la gestió de la darrera modificació
        $this->dadesActualsGestio($data);

        // Històric del control de canvis
        $this->modifyLastHistoricGestioDocument($data);

        return $data;
    }

    private function dadesActualsGestio(&$data) {
        if ($data['autor']) $data['cc_dadesAutor']['nomGestor'] = $this->getUserName($data['autor']);
        if ($data['revisor']) $data['cc_dadesRevisor']['nomGestor'] = $this->getUserName($data['revisor']);
        if ($data['validador']) $data['cc_dadesValidador']['nomGestor'] = $this->getUserName($data['validador']);
    }

    public function clearQualityRolesData(&$data){
        if(!is_array($data['cc_dadesAutor'])){
            $data['cc_dadesAutor'] = json_decode($data['cc_dadesAutor'], TRUE);
        }
        if(!is_array($data['cc_dadesRevisor'])){
            $data['cc_dadesRevisor'] = json_decode($data['cc_dadesRevisor'], TRUE);
        }
        if(!is_array($data['cc_dadesValidador'])){
            $data['cc_dadesValidador'] = json_decode($data['cc_dadesValidador'], TRUE);
        }
        $data['cc_dadesAutor']['dataDeLaGestio'] = "";
        $data['cc_dadesAutor']['signatura'] = "pendent";
        $data['cc_dadesRevisor']['dataDeLaGestio'] = "";
        $data['cc_dadesRevisor']['signatura'] = "pendent";
        $data['cc_dadesValidador']['dataDeLaGestio'] = "";
        $data['cc_dadesValidador']['signatura'] = "pendent";        
        $data['cc_raonsModificacio'] = "";        
    }

    public function updateSignature(&$data, $role, $date=FALSE) {        
        $keyConverter = ["cc_dadesAutor" =>"autor", "cc_dadesRevisor" => "revisor", "cc_dadesValidador" => "validador"];
        $data[$role]['nomGestor'] = $this->getUserName($data[$keyConverter[$role]]);;
        $data[$role]['dataDeLaGestio'] = $date?$date:date("Y-m-d");
        $data[$role]['signatura'] = "signat";
    }
    
    public function modifyLastHistoricGestioDocument(&$data, $date=false) {
        if ($data['cc_historic'] === '"[]"') {
            $data['cc_historic'] = array();
        }elseif (!is_array($data['cc_historic'])){
            $data['cc_historic'] = json_decode($data['cc_historic'], true);
        }
        if (is_array($data['cc_historic'])) {
            $hist['data'] = $date ? $date : date("Y-m-d");
            $hist['autor'] = $this->getUserName($data['autor']);
            $hist['modificacions'] = $data['cc_raonsModificacio'] ? $data['cc_raonsModificacio'] : "";
            $c = (count($data['cc_historic']) < 1) ? 0 : count($data['cc_historic'])-1;
            $data['cc_historic'][$c] = $hist;
        }
    }
    
    public function addHistoricGestioDocument(&$data) {
        if (!is_array($data['cc_historic'])){
            $data['cc_historic'] = json_decode($data['cc_historic'], true);
        }
        $hist['data'] = date("Y-m-d");
        $hist['autor'] = $this->getUserName($data['autor']);
        $hist['modificacions'] = $data['cc_raonsModificacio'] ? $data['cc_raonsModificacio'] : "";
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
        $metaDataManagement = ['workflow'=>['currentState'=>"creating"]];
        $metaDataQuery->setMeta(json_encode($metaDataManagement), $subSet, "creació", NULL);
    }

}