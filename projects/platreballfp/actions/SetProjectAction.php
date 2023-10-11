<?php
if (!defined('DOKU_INC')) die();

class SetProjectAction extends BasicSetProjectAction {

     protected function responseProcess(){
         $response = parent::responseProcess();
         if($this->getModel()->isProjectGenerated()){
             $id = $this->getModel()->getContentDocumentId($response);
             p_set_metadata($id, array('metadataProjectChanged' => time()));
         }
         return $response;
     }
}