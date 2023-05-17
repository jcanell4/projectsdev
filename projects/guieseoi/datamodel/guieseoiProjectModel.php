<?php
/**
 * guieseoiProjectModel
 * @culpable Rafael Claver
 * @aprenent Marjose
 */
if (!defined("DOKU_INC")) die();

//class guieseoiProjectModel extends MoodleContentFilesProjectModel {
class guieseoiProjectModel extends MoodleMultiContentFilesProjectModel {    
    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;        
    }


    
     /* ------------------------------
     * updateCalculatedFieldsOnRead
     * Calcula el valor de los campos calculables
     * @param JSON $data
     * $originalDataKeyValue és el que ve del client. 
     * Quan usuari modifica,primer passa pel calculateonread o calculateonsave. el resultat és $data
     * $originalDataKeyValue és el que ve directament del client 
     * sense haver passat per les modificacions del calculate (a confirmar que sigui així)
     * per ara això pot estar buit perquè no cal fer cap càlcul addicional
     * ------------------------------
     * public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE, $subset=FALSE){}
     * 
     */
   
    /* ------------------------------
     * getCalendariFieldFromMix
     * Per si més endavant agafa les dades del MIX
     * ------------------------------
    private function getCalendariFieldFromMix(&$values, $taulaCalendari){
        $dataFromMix = false;
        if(isset($values["moodleCourseId"]) && $values["moodleCourseId"]>0){            
            $dataFromMix = $this->getMixDataLessons($values["moodleCourseId"]);
            if($dataFromMix){
                //de moment taula buida. Aquesta funció ara no fa res
            }
        }
        $values["dataFromMix"] =$dataFromMix;
        return $taulaCalendari;        
    }
    */


    /* ------------------------------
     * getCalendarDates
     * Puja dades al calendari de moodle
     * Llista de les dates a pujar al calendari amb el format següent:
     *  - title
     *  - date (en format yyyy-mm-dd)
     *  - description
     * ------------------------------
    public function getCalendarDates() {
        $ret = array();
        //Per enviar dades al calendari. 
        //la deixem buida en previsió de que més endavant la necessitem.

        return $ret;
    }
     * *
     */
    
    
     /* ------------------------------
     * validateFields
     * Validem que els camps entrats són consistents.
     * comprovem el defaultsubset:  ProjectKeys::VAL_DEFAULTSUBSET és 
     * el subset per defecte, el main.
     * el responsable està al default subset (al main).
     * per això, el codi següent no entra mai al if, doncs no hi ha cap
     * més subset que el main. Però ho deixem per si més endavant hi ha.
     * $data: són les dades que m'arriben. En format key-value. 
     * ------------------------------*/
    public function validateFields($data = NULL, $subset=FALSE){
     
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            parent::validateFields($data, $subset);
        }else{//aquí estem al main (DEFAULTSUBSET)
            parent::validateFields($data);
            $values = is_array($data)?$data:json_decode($data, true);//is_array em retorna el valor de les dades en format array
            //$dadesGuiesEOI = IocCommon::toArrayThroughArrayOrJson($data);//Faria el mateix

            //Valida que hi ha un responsable assignat sempre en el moment de guardar
            if (empty($values["responsable"])){
                throw new InconsistentDataException("No hi ha un responsable assignat. Cap assignar-ho per poder crear aqesta guia.");
            }
        }
    }

    //M'obliga a implementar la funció abstracta... però no fa res.. és correcte?
    public function getCalendarDates() {
        parent::getCalendarDates();
    }

    //M'obliga a implementar la funció abstracta... però no fa res.. és correcte?
    public function getCourseId() {
        parent::getCourseId();
    }

    /* ------------------------------
     * getCourseId
     * Necessari quan vulguem 
     * actualitzar el calendari de 
     * l'aula des d'aquí
     * ------------------------------*/
    /*
    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }*/
}
