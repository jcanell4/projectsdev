<?php
/**
 * ProjectExportAction
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
define('WIKI_IOC_PROJECT', realpath(__DIR__ . "/../") . "/");

class ProjectExportAction extends ProjectMetadataAction{
    const PATH_RENDERER = WIKI_IOC_PROJECT."exporter/";
    const PATH_CONFIG_FILE = WIKI_IOC_PROJECT."metadata/config/";
    const CONFIG_TYPE_FILENAME = "configMain.json";
    const CONFIG_RENDER_FILENAME = "configRender.json";

    protected $projectID = NULL;
    protected $mainTypeName = NULL;
    protected $dataArray = array();
    protected $typesRender = array();
    protected $typesDefinition = array();
    protected $mode;
    protected $filetype;
    protected $projectType;
    protected $factoryRender;
    protected $metaDataSubSet;

    public function __construct($factory=NULL){
        $this->factoryRender = $factory;
    }
    /**
     * Ejecuta los procesos_render de primer nivel definidos en el primer nivel
     * del archivo de configuración del proyecto
     */
    protected function setParams($params) {
        $this->mode           = $params[ProjectKeys::KEY_MODE];
        $this->filetype       = $params[ProjectKeys::KEY_FILE_TYPE];
        $this->projectType    = $params[ProjectKeys::KEY_PROJECT_TYPE];
        $this->projectID      = $params[ProjectKeys::KEY_ID];
        $this->metaDataSubSet = $params[ProjectKeys::KEY_METADATA_SUBSET];
        $this->projectNS      = $params[ProjectKeys::KEY_NS] ? $params[ProjectKeys::KEY_NS] : $this->projectID;

        $this->typesRender = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "typesDefinition");

        $cfgArray = $this->getProjectConfigFile(self::CONFIG_TYPE_FILENAME, ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE, $this->metaDataSubSet);
        $this->mainTypeName = $cfgArray['mainType']['typeDef'];
        $this->typesDefinition = $cfgArray['typesDefinition'];

        $toInitModel = array(ProjectKeys::KEY_ID =>$this->projectID, ProjectKeys::KEY_PROJECT_TYPE=>$this->projectType, ProjectKeys::KEY_METADATA_SUBSET =>$this->metadataSubset);
        $this->projectModel->init($toInitModel);
        $this->dataArray = $this->projectModel->getDataProject(); //JOSEP: AIXÍ ESTÀ BË PERQUÈ DELEGUEM EN EL MODEL
    }

    public function responseProcess() {
        $ret = array();
        $this->factoryRender->init(['mode'            => $this->mode,
                                    'filetype'        => $this->filetype,
                                    'typesDefinition' => $this->typesDefinition,
                                    'typesRender'     => $this->typesRender]);

        $render = $this->factoryRender->createRender($this->typesDefinition[$this->mainTypeName],
                                                     $this->typesRender[$this->mainTypeName],
                                                     array(ProjectKeys::KEY_ID => $this->projectID));

        $result = $render->process($this->dataArray);
        $result['ns'] = $this->projectNS;
        $ret['id'] = str_replace(":", "_", $this->projectID);
        $ret['ns'] = $this->projectNS;

        switch ($this->mode) {
            case 'pdf':
                $ret["meta"] = self::get_html_metadata($result);
                break;
            default:
                throw new Exception("ProjectExportAction: mode incorrecte.");
        }

        if (!WikiGlobalConfig::getConf('plugin')['iocexportl']['saveWorkDir']){
            $this->removeDir($result["tmp_dir"]);
        }

        return $ret;
    }

    /**
     * @return array : Devuelve el subconjunto $rama del fichero de configuración del proyecto
     *                  Si se indica el metaDataSubSet, es que espera encontrar una estructura
     */
    private function getProjectConfigFile($filename, $rama, $metaDataSubSet=NULL) {
        $config = @file_get_contents(self::PATH_CONFIG_FILE.$filename);
        if ($config != FALSE) {
            $array = json_decode($config, true);
            if ($metaDataSubSet) {
                $elem = $array[$rama];
                for ($i=0; $i<count($elem); $i++) {
                    if (array_key_exists($metaDataSubSet, $elem[$i])) {
                        $ret = $elem[$i];
                        break;
                    }
                }
            }else{
                $ret = $array[$rama];
            }
            return $ret;
        }
    }

    protected function getTypesCollection($key = NULL) {
        return ($key === NULL) ? $this->typesDefinition : $this->typesDefinition[$key];
    }
    protected function getProjectDataArray($key = NULL) {
        return ($key === NULL) ? $this->dataArray : $this->dataArray[$key];
    }
    public function processTitle($param = NULL) {
        return ($param===NULL) ? $param : getProjectDataArray('title');
    }
    public function getProjectID() {
        return $this->projectID;
    }

    public static function get_html_metadata($result){
        if ($result['error']) {
            throw new Exception ("Error");
        }else{
            $path = WikiGlobalConfig::getConf('mediadir').'/'. preg_replace('/:/', '/', $result['ns']);
            $file = preg_replace('/:/', '_', $result['ns']);
            if ($result["tmp_dir"]) {
                self::_copyPdf($result["tmp_dir"], $path, $file.".pdf");
            }
            $ret.= self::_getHtmlMetadata($result['ns'], "$path/$file", ".pdf");
        }
        return $ret;
    }

    private static function _getHtmlMetadata($ns, $file, $ext) {
        $P = "<p>"; $nP = "</p>";
        $class = "mf_pdf";
        $mode = "PDF";

        if (@file_exists($file.$ext)) {
            $ret = '';
            $filename = str_replace(':','_',basename($ns)).$ext;
            $media_path = "lib/exe/fetch.php?media=$ns:$filename";
            $data = date("d/m/Y H:i:s", filemtime($file.$ext));
            /*
            $id = preg_replace('/:/', '_', $ns);
            if ($ext === ".pdf") {
                $ret.= '<p></p><div class="iocexport">';
                $ret.= '<span style="font-weight: bold;">Exportació PDF</span><br />';
                $ret.= '<form action="'.WIKI_IOC_MODEL.'renderer/basiclatex.php" id="export__form_'.$id.'" method="post">';
                $ret.= '<input name="filetype" value="zip" type="radio"> ZIP &nbsp;&nbsp;&nbsp;';
                $ret.= '<input name="filetype" value="pdf" checked type="radio"> PDF ';
                $ret.= '</form>';
                $ret.= '</div>';
            }*/
            $ret.= $P.'<span id="exportacio" style="word-wrap: break-word;">';
            $ret.= '<a class="media mediafile '.$class.'" href="'.$media_path.'" target="_blank">'.$filename.'</a> ';
            $ret.= '<span style="white-space: nowrap;">'.$data.'</span>';
            $ret.= '</span>'.$nP;
        }else{
            $ret.= '<span id="exportacio">';
            $ret.= '<p class="media mediafile '.$class.'">No hi ha cap exportació '.$mode.' feta</p>';
            $ret.= '</span>';
        }
        return $ret;
    }

    private static function _copyPdf($origin, $path, $file){
        if (!file_exists($path)){
            mkdir($path, 0755, TRUE);
        }
        $ok = copy($origin, "$path/$file");
        return $ok;
    }

    /**
     * Remove specified dir
     * @param string $directory
     */
    private function removeDir($directory) {
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
            return FALSE;
        }else {
            $dh = opendir($directory);
            while ($contents = readdir($dh)) {
                if ($contents != '.' && $contents != '..') {
                    $path = "$directory/$contents";
                    if (is_dir($path)) {
                        $this->removeDir($path);
                    }else {
                        unlink($path);
                    }
                }
            }
            closedir($dh);

            if (file_exists($directory)) {
                if (!rmdir($directory)) {
                    return FALSE;
                }
            }
            return TRUE;
        }
    }
}
