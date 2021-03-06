<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
require_once DOKU_INC."inc/parserutils.php";

class renderDate extends AbstractRenderer {
    private $sep;

    public function __construct($factory, $cfgExport=NULL, $sep="-") {
        parent::__construct($factory, $cfgExport);
        $this->sep = $sep;
    }

    public function process($date) {
        $dt = strtotime(str_replace('/', '-', $date));
        return date("d". $this->sep."m".$this->sep."Y", $dt);
    }

}

class renderText extends AbstractRenderer {

    public function process($data) {
        return htmlentities($data, ENT_QUOTES);
    }
}

class renderField extends AbstractRenderer {

    public function process($data) {
        return htmlentities($data, ENT_QUOTES);
    }
}

class renderRenderizableText extends AbstractRenderer {

    public function process($data) {
        $instructions = p_get_instructions($data);
        $html = p_render('wikiiocmodel_ptxhtml', $instructions, $info);
        return $html;
    }
}

class renderFileToPsDom extends renderFile {
    protected function render($instructions, &$renderData){
        $ret = p_latex_render('wikiiocmodel_psdom', $instructions, $renderData);
        Logger::debug("psDom: $ret", 0, 0, 1, 0, FALSE);
        return $ret;
    }
}

class renderFile extends AbstractRenderer {

    public function process($data) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['export_html'] = $this->cfgExport->export_html;
        $_SESSION['tmp_dir'] = $this->cfgExport->tmp_dir;
        $_SESSION['latex_images'] = &$this->cfgExport->latex_images;
        $_SESSION['media_files'] = &$this->cfgExport->media_files;
        $_SESSION['graphviz_images'] = &$this->cfgExport->graphviz_images;
        $_SESSION['gif_images'] = &$this->cfgExport->gif_images;
        $_SESSION['alternateAddress'] = TRUE;

        $text = io_readFile(wikiFN($data));
        $instructions = p_get_instructions($text);
        $renderData = array();
        $html = p_render('wikiiocmodel_basicxhtml', $instructions, $renderData);
        $this->cfgExport->toc = $renderData["tocItems"];
        if ($startedHere) session_destroy();

        return $html;
    }
}
