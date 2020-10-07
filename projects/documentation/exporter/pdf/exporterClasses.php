<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
require_once(DOKU_PLUGIN.'iocexportl/lib/renderlib.php');

class renderField extends AbstractRenderer {
    public function process($data) {
        return renderLatexDocument::st_clean_accent_chars($data);
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = parent::process($data);
        return $ret;
    }
}

class renderText extends renderField {
    public function process($data) {
        $ret = parent::process($data);
        return $ret;
    }
}

class renderFile extends AbstractRenderer {
    public function process($data) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['tmp_dir'] = $this->cfgExport->tmp_dir;
        //las siguientes variables están pendientes de revisión para establecer la necesidad de su existencia
        $_SESSION['latex_images'] = &$this->cfgExport->latex_images;
        $_SESSION['media_files'] = &$this->cfgExport->media_files;
        $_SESSION['graphviz_images'] = &$this->cfgExport->graphviz_images;
        $_SESSION['gif_images'] = &$this->cfgExport->gif_images;

        $text = io_readFile(wikiFN($data));
        $info = array();
        $instructions = get_latex_instructions($text);
        $latex = p_latex_render('wikiiocmodel_basiclatex', $instructions, $info);

        if ($startedHere) session_destroy();
        return $latex;
    }
}
