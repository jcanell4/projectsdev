<?php
/**
 * projecte: platreballfp
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();

class exportDocument extends renderHtmlDocument {

    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if($params){
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
        $output_filename = str_replace(':','_',$this->cfgExport->id);
        $pathTemplate = "xhtml/exportDocument/templates";

        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir."/$output_filename.zip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/index.html");

            if ($zip->addFromString('index.html', $document)) {
                $allPathTemplate = $this->cfgExport->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $allPathTemplate, "", "img");
                $zip->addFile($allPathTemplate."/main.css", "main.css");
                $this->addDefaultCssFilesToZip($zip, "pt_sencer/");
                $this->addFilesToZip($zip, $allPathTemplate, "", "pt_sencer", TRUE);
                $ptSencer = $this->replaceInTemplate($data, "$pathTemplate/pt_sencer/pt.tpl");
                //cal modificar la ruta dels arxius gràfics
                $ptSencer = preg_replace("/(<img class=.media. src=.)(img.*?\.gif)/", "$1../$2", $ptSencer);
                $ptSencer = preg_replace("/(<img.*? src=.)(img\/)(.*?\/)*(.*?)(\.[png|jpg])/", "$1../$2$4$5", $ptSencer);
                $zip->addFromString('/pt_sencer/pt.html', $ptSencer);
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
        $this->setResultFileList($result);

        return $data;
    }

    private function replaceInTemplate($data, $file) {
        $tmplt = $this->loadTemplateFile($file);
        $document = WiocclParser::getValue($tmplt, [], $data);
        foreach ($this->cfgExport->toc as $tocKey => $tocItem) {
            $toc ="";
            foreach ($tocItem as $elem) {
                if($elem['level']==1){
                    $toc .= "<a href='{$elem['link']}'>".htmlentities($elem['title'])."</a>\n";
                }
            }
            $document = str_replace("@@TOC($tocKey)@@", $toc, $document);
        }
        return $document;
    }

    protected function attachMediaFiles(&$zip) {
        //Attach media files
        foreach($this->cfgExport->media_files as $f){
            resolve_mediaid(getNS($f), $f, $exists);
            if ($exists) {
                //eliminamos el primer nivel del ns
                $arr = explode(":", $f);
                array_shift($arr);
                $zip->addFile(mediaFN($f), 'img/'.implode("/", $arr));
            }
        }
        $this->cfgExport->media_files = array();

        //Attach latex files
        foreach($this->cfgExport->latex_images as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->latex_images = array();

        //Attach graphviz files
        foreach($this->cfgExport->graphviz_images as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->graphviz_images = array();

        //Attach gif (png, jpg, etc) files
        foreach($this->cfgExport->gif_images as $m){
            if (file_exists(mediaFN($m))) $zip->addFile(mediaFN($m), "img/". str_replace(":", "/", $m));
        }
        $this->cfgExport->gif_images = array();

        if (session_status() == PHP_SESSION_ACTIVE) session_destroy();
    }

}
