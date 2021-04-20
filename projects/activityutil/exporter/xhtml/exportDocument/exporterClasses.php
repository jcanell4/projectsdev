<?php
/**
 * projecte: activityutil
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
        $this->ioclangcontinue = array('ca'=>'continuació', 'de'=>'fortsetzung', 'en'=>'continued','es'=>'continuación','fr'=>'suite','it'=>'continua');
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if ($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->cfgExport->lang = preg_replace('/\n/', '', $this->cfgExport->lang);
            $this->log = isset($params['log']);
        }
        $this->cfgExport->export_html = TRUE;
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        $result["tmp_dir"] = $this->cfgExport->tmp_dir;
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $output_filename = $this->cfgExport->output_filename;
        $pathTemplate = "xhtml/exportDocument/templates";

        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir."/$output_filename.zip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/activityutil.tpl");

            if ($zip->addFromString('index.html', $document)) {
                $allPathTemplate = $this->cfgExport->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $allPathTemplate, "", "css", FALSE, $data['estil'].".css");
                $this->addDefaultCssFilesToZip($zip, "");
                $this->addFilesToZip($zip, $allPathTemplate, "", "img");
                $this->addFilesToZip($zip, $allPathTemplate, "", "js");

                $titol = html_entity_decode(htmlspecialchars_decode($data["titol"], ENT_COMPAT|ENT_QUOTES));
                $subtitol = html_entity_decode(htmlspecialchars_decode($data["subtitol"], ENT_COMPAT|ENT_QUOTES));
                $nom_real = html_entity_decode(htmlspecialchars_decode($data["nom_real"], ENT_COMPAT|ENT_QUOTES));
                $data_fitxer = html_entity_decode(htmlspecialchars_decode($data["data_fitxercontinguts"], ENT_COMPAT|ENT_QUOTES));
                $entitat_responsable = html_entity_decode(htmlspecialchars_decode($data["entitatResponsable"], ENT_COMPAT|ENT_QUOTES));

                $params = array(
                    "id" => $this->cfgExport->id,
                    "path_templates" => $this->cfgExport->rendererPath . "/pdf/exportDocument/templates",  // directori on es troben les plantilles latex usades per crear el pdf
                    "tmp_dir" => $this->cfgExport->tmp_dir,    //directori temporal on crear el pdf
                    "lang" => strtoupper($this->cfgExport->lang),
                    "mode" => isset($this->mode) ? $this->mode : $this->filetype,
                    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
                    "style" => $this->cfgExport->rendererPath . "/xhtml/exportDocument/pdf/".$data["estil"].".stypdf",
                    "data" => array(
                        "header" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                                     "wlogo" => 9.9,
                                     "hlogo" => 11.1,
                                     "ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nInstitut Obert de Catalunya",
                                     "rtext" => $titol],
                        "titol" => ['titol'    => $titol,
                                    'subtitol' => $subtitol,
                                    'autor'    => $data['mostrarAutor']==="true" || $data['mostrarAutor']===true ? $nom_real : "",
                                    'entitatResponsable' => $entitat_responsable,
                                    'data'     => $data_fitxer],                       
                        "contingut" => json_decode($data["documentPartsPdf"], TRUE)   //contingut latex ja rendaritzat
                    )
                );
                $filenamepdf = "$output_filename.pdf";
                $pdfRenderer = new PdfRenderer();                
                $pdfRenderer->renderDocument($params, $filenamepdf);
                $zip->addFile($this->cfgExport->tmp_dir."/$filenamepdf", "/$filenamepdf");

                $this->attachMediaFiles($zip);

                $result["zipFile"] = $zipFile;
                $result["zipName"] = "$output_filename.zip";
                $result["info"] = "fitxer {$result['zipName']} creat correctement";
            }else{
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
                throw new Exception ("Error en la creació del fitxer zip");
            }
            if (!$zip->close()) {
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
            }
        }else{
            $result['error'] = true;
            $result['info'] = $this->cfgExport->aLang['nozipfile'];
        }
        return $result;
    }

    private function replaceInTemplate($data, $file) {
        $tmplt = $this->loadTemplateFile($file);
        $document = WiocclParser::getValue($tmplt, [], $data);

        foreach ($this->cfgExport->toc as $tocKey => $tocItem) {
            $toc = "";
            $nivel_anterior = 1; //nivel anterior
            $ntoc = 0;           //número de tocItem actual

            if ($tocItem){
                foreach ($tocItem as $elem) {
                    if ($elem['level'] <= $data['nivells']) {
                        if ($elem['level'] > $nivel_anterior) {
                            $toc .= "<div class='hidden'>\n";
                        }
                        if ($elem['level'] <= $nivel_anterior) {
                            $toc .= $this->_add_close(($nivel_anterior-$elem['level'])*2+1);
                        }
                        $toc .= "<div class='toc_level_{$elem['level']}'>\n";
                        $toc .= "<span>\n";
                        if ($tocItem[$ntoc+1]['level'] > $elem['level']) { //si el elemento siguiente es de nivel inferior
                            $toc .= "<span onclick='switchopcl(this)' class='button_index cl'>&nbsp;&nbsp;&nbsp;&nbsp;</span>\n";
                        }else {
                            $toc .= "<span class='button_index'>&nbsp;&nbsp;&nbsp;&nbsp;</span>\n";
                        }
                        $toc .= "<a href='{$elem['link']}' onclick='closeNav(); return true'>".htmlentities($elem['title'])."</a>\n";
                        $toc .= "</span>\n";

                        $nivel_anterior = $elem['level'];
                        $ntoc++;
                    }
                }
                $toc .= $this->_add_close(($nivel_anterior-1)*2);
                $toc = substr($toc, 7) . "</div>\n";
            }
            $document = str_replace("@@TOC($tocKey)@@", $toc, $document);
        }
        return $document;
    }

    private function _add_close($n) {
        $toc = "";
        for ($i=1; $i<=$n; $i++) {
            $toc .= "</div>\n";
        }
        return $toc;
    }

    private function attachMediaFiles(&$zip) {
        //Attach media files
        foreach(array_unique($this->cfgExport->media_files) as $f){
            resolve_mediaid(getNS($f), $f, $exists);
            if ($exists) {
                $zip->addFile(mediaFN($f), 'img/'.str_replace(":", "/", $f));
            }
        }
        $this->cfgExport->media_files = array();

        //Attach latex files
        foreach(array_unique($this->cfgExport->latex_images) as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->latex_images = array();

        //Attach graphviz files
        foreach(array_unique($this->cfgExport->graphviz_images) as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->graphviz_images = array();

        //Attach gif (png, jpg, etc) files
        foreach(array_unique($this->cfgExport->gif_images) as $m){
            if (file_exists(mediaFN($m))) $zip->addFile(mediaFN($m), "img/". str_replace(":", "/", $m));
        }
        $this->cfgExport->gif_images = array();

        if (session_status() == PHP_SESSION_ACTIVE) session_destroy();
    }

}
