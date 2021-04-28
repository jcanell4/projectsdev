<?php
/**
 * projecte: prgfplogse
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
if (!defined('WIKI_LIB_IOC_MODEL')) define('WIKI_LIB_IOC_MODEL', DOKU_LIB_IOC."wikiiocmodel/");

class exportDocument extends renderHtmlDocument {

    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if ($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage'])) ? 'ca' : strtolower(preg_replace('/\n/', '', $params['ioclanguage']));
            $this->log = isset($params['log']);
        }
        $this->cfgExport->export_html = TRUE;
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }

        $departament = html_entity_decode(htmlspecialchars_decode($data["departament"], ENT_COMPAT|ENT_QUOTES));
        $cicle = html_entity_decode(htmlspecialchars_decode($data["cicle"], ENT_COMPAT|ENT_QUOTES));
        $credit = html_entity_decode(htmlspecialchars_decode($data["credit"], ENT_COMPAT|ENT_QUOTES));
        $creditId = html_entity_decode(htmlspecialchars_decode($data["creditId"], ENT_COMPAT|ENT_QUOTES));
        $hores_totals = 20;

        $params = array(
            "id" => $this->cfgExport->id,
            "tmp_dir" => $this->cfgExport->tmp_dir,
            "lang" => strtoupper($this->cfgExport->lang),
            "mode" => isset($this->mode) ? $this->mode : $this->filetype,
    	    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
            "style" => $this->cfgExport->rendererPath."/pdf/exportDocument/styles/main.stypdf",
            "data" => array(
                "header" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                             "wlogo" => 9.9,
                             "hlogo" => 11.1,
                             "ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nInstitut Obert de Catalunya",
                             "rtext" => $cicle."\n".$creditId." ".$credit],
                "titol" => ["departament" => $departament,
                            "cicle" => $cicle,
                            "creditId" => $creditId,
                            "credit" => $credit,
                            "hores" => $hores_totals],
                "peu" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutIOC.jpg",
                          "wlogo" => 10,
                          "hlogo" => 8,
                          "codi"  => "I61",
                          "versió" => $data["documentVersion"]],
                "contingut" => json_decode($data["pdfDocument"], TRUE)   //contingut latex ja rendaritzat
            )
        );
        $pdfRenderer = new PdfRenderer();
        $pdfRenderer->renderDocument($params, "pt.pdf");
        $result["tmp_dir"] = $this->cfgExport->tmp_dir."/pt.pdf";
        return $result;
    }

}
