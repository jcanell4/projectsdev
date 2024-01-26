<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

/**
 * class IocTcPdf
 */
class IocTcPdf extends BasicIocTcPdf {
    public function __construct(TcPdfStyle &$stile) {
        parent::__construct($stile);
    }

    //Page header
    public function Header() {
        if ($this->PageNo() == 1) return;

        $margins = $this->getMargins();
        $subtitol = $this->Subtitle($this->header_string);

        // Logo
        $image_file = $this->header_logo;
        $this->Image($image_file, $margins['left'], 5, $this->header_logo_width, $this->header_logo_height, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $headerfont = $this->getHeaderFont();
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        $header_x = $margins['left'] + $margins['padding_left'] + ($this->header_logo_width * 1.1);
        $header_w = 95 - $header_x;

        // header title
        $this->SetTextColorArray($this->header_text_color);
        $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
        $this->SetX($header_x);
        $this->MultiCell($header_w, $cell_height, $this->header_title, 0, 'L', 0, 0, "", "", true);

        // header string
        $this->MultiCell(0, $cell_height, $subtitol, 0, 'R', 0, 0, "", "", true);
        $this->Line($margins['left'], 19, $this->getPageWidth()-$margins['right'], 19);
    }

    // Page footer
    public function Footer() {
        if ($this->PageNo() == 1) return;

        $this->SetY(-15);   //Position at 15 mm from bottom
        $this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
        $this->Cell(0, 10, 'pàgina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');  //Page number
    }

    public function setHeaderData($ln='', $lw=0, $lh=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_height = $lh;
    }

    protected function Subtitle($title) {
        $title = str_replace("/", "|", $title);
        $title = str_replace("\n", "|", $title);
        $arr_subtitol = explode("|", $title);
        $c = count($arr_subtitol);
        $arr_subtitol[0] = BasicPdfRenderer::getText($arr_subtitol[0], 100, $this);
        return $arr_subtitol[0] . "\n" . $arr_subtitol[$c-2] . "\n" . $arr_subtitol[$c-1];
    }

 }

class PdfRenderer extends BasicPdfRenderer {

    /**
     * params = hashArray:{
     *      id: string  //id del projecte
     *      path_templates:string,  // directori on es troben les plantilles latex usades per crear el pdf
     *      tmp_dir: string,    //directori temporal on crear el pdf
     *      lang: string  // idioma usat (CA, EN, ES, ...)
     *      mode: string  // pdf o zip
     *      data: hashArray:{
     *              titol:array os string    // linies de títol del document (cada ítem és una línia)
     *              contingut: string   //contingut latex ja rendaritzat
     */
    public function renderDocument($params, $output_filename="") {
        
        parent::renderDocument($params, $output_filename);

        $this->iocTcPdf->setStartingPageNumber(0);

        //primera pàgina
        $this->iocTcPdf->AddPage();

        $lin = 0;
        for ($i=2; $i<count($params["data"]["titol"]); $i++){
            $arr_subtitol = explode("|", str_replace("/", "|", $params["data"]["titol"][$i]));
            $lin += count($arr_subtitol);
        }
        $row_offset = $lin * 4;   //espai vertical que cal desplaçar per encabir un títol multilínia

        $this->iocTcPdf->SetX(100);
        $this->iocTcPdf->SetY($y=100-$row_offset);

        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 35);
        for ($i=0; $i<2; $i++){
            $this->iocTcPdf->MultiCell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }
        $this->iocTcPdf->SetY($y+=100-$row_offset);

        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 18);
        for ($i=2; $i<count($params["data"]["titol"]); $i++){
            $text = str_replace("/", "|", $params["data"]["titol"][$i]);
            $arr_subtitol = explode("|", $text);
            foreach ($arr_subtitol as $subtitol) {
                $this->iocTcPdf->MultiCell(0, 0, trim($subtitol), 0, 1);
            }
        }

        //continguts
        $this->iocTcPdf->AddPage();
        if (!empty($params["data"]["contingut"])) {
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                $this->resolveReferences($itemsDoc);
            }
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                $this->renderHeader($itemsDoc, $this->iocTcPdf);
            }
        }

        // add a new page for TOC
        $this->renderToc();

        $this->iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        return TRUE;
    }

}
