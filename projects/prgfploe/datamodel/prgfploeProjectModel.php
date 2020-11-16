<?php
/**
 * ptfploeProjectModel
 * @culpable Josep Cañellas
 */
if (!defined("DOKU_INC")) die();

class prgfploeProjectModel extends UniqueContentFileProjectModel{

    public function generateProject() {
        $ret = array();
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto

        //2. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->getProjectMetaDataQuery()->setProjectGenerated();

        if ($ret[ProjectKeys::KEY_GENERATED]) {
            try {
                //3. Otorga, a las Persons, permisos sobre el directorio de proyecto y añade enlace a dreceres
                $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
                $this->modifyACLPageAndShortcutToPerson($params);
            }
            catch (Exception $e) {
                $ret[ProjectKeys::KEY_GENERATED] = FALSE;
                $this->getProjectMetaDataQuery()->setProjectSystemStateAttr("generated", FALSE);
            }
        }

        return $ret;
    }

    public function validateFields($data=NULL){
        $details="";
        $nfTable = $data["taulaDadesNuclisFormatius"];
        if(!is_array($nfTable)){
            $nfTable = json_decode($nfTable, TRUE);
        }
        $ufTable = $data["taulaDadesUF"];
        if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
        }
        $totalUfs=array();
        foreach ($nfTable as $item){
            if(!isset($totalUfs[$item["unitat formativa"]])){
                $totalUfs[$item["unitat formativa"]]=0;
            }
            $totalUfs[$item["unitat formativa"]] += $item["hores"];
        }

        if(!empty($nfTable)){
            foreach ($ufTable as $item) {
                if($item["hores"]!=$totalUfs[$item["unitat formativa"]]){
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
    }

     public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE) {
         $ufTable = $data["taulaDadesUF"];
         if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
         }
         foreach ($ufTable as $key => $value) {
             if($ufTable[$key]["ponderació"]=="0"){
                 $ufTable[$key]["ponderació"]=$ufTable[$key]["hores"];
             }
         }
         $data["taulaDadesUF"]=$ufTable;
         return $data;
     }

     public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE) {
        $ufTable = $data["taulaDadesUF"];
        if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
        }
        $blocTable = array();
        $blocTotal = 0;
        $total = 0;
        $i=0;
        $size = count($ufTable);
        while($i<$size) {
             $ufTable[$i]["hores"] = $ufTable[$i]["horesMinimes"]+$ufTable[$i]["horesLLiureDisposicio"];
             $total += $ufTable[$i]["hores"];
             $blocTotal += $ufTable[$i]["hores"];
             $currentBloc=$ufTable[$i]["bloc"];
             if($ufTable[$i]["ponderació"]==$ufTable[$i]["hores"]){
                 $ufTable[$i]["ponderació"]==0;
             }
             $i++;
             if($i==$size || $ufTable[$i]['bloc']!=$currentBloc){
                $blocTable[$currentBloc]["bloc"] =$currentBloc;
                $blocTable[$currentBloc]["horesBloc"]=$blocTotal;
                $blocTotal=0;
             }
        }
        
        $data["taulaDadesUF"]=$ufTable;
        $data["taulaDadesBlocs"]=$blocTable;
        $data["durada"]=$total;
        return $data;
    }
}
