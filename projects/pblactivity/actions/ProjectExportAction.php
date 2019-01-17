<?php
/**
 * ProjectExportAction: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");

require_once WIKI_IOC_MODEL."persistence/ProjectMetaDataQuery.php";

class ProjectExportAction  extends AbstractWikiAction{
    const CONFIG_TYPE_FILENAME = "configMain.json";
    const CONFIG_RENDER_FILENAME = "configRender.json";

    protected $projectID = NULL;
    protected $projectNS = NULL;
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
        $this->mode        = $params[ProjectKeys::KEY_MODE];
        $this->filetype    = $params[ProjectKeys::KEY_FILE_TYPE];
        $this->projectType = $params[ProjectKeys::KEY_PROJECT_TYPE];
        $this->projectID   = $params[ProjectKeys::KEY_ID];
        $this->projectNS   = $params[ProjectKeys::KEY_NS];
        $this->metaDataSubSet = $params[ProjectKeys::KEY_METADATA_SUBSET];
        $this->typesRender = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "typesDefinition");
            $cfgArray = $this->getProjectConfigFile(self::CONFIG_TYPE_FILENAME, ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE)[0];
        $this->mainTypeName = $cfgArray['mainType']['typeDef'];
        $this->typesDefinition = $cfgArray['typesDefinition'];
            $projectfilename = $cfgArray[$this->metaDataSubSet];
            $idResoucePath = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $this->projectNS);
            $projectfilepath = "$idResoucePath/".$this->projectType."/$projectfilename";
        $this->dataArray = $this->getProjectDataFile($projectfilepath, $this->metaDataSubSet);
    }

    public function responseProcess() {
        $ret = array();
        $fRenderer = $this->factoryRender;
        $fRenderer->init(['mode'            => $this->mode,
                          'filetype'        => $this->filetype,
                          'typesDefinition' => $this->typesDefinition,
                          'typesRender'     => $this->typesRender]);
        $render = $fRenderer->createRender($this->typesDefinition[$this->mainTypeName], $this->typesRender[$this->mainTypeName], array("id"=> $this->projectID));
        $result = $render->process($this->dataArray);
        $result['id'] = $this->projectID;
        $result['ns'] = $this->projectNS;

        switch ($this->mode) {
            case 'pdf' :
                $ret = self::get_html_metadata($result);
                break;
            case 'xhtml':
                $ret = self::get_html_metadata($result);
                break;
            default:
                throw new Exception("ProjectExportAction: mode incorrecte.");
        }
        return $ret;
    }

    /**
     * @return array : Devuelve el subconjunto $rama del fichero de configuración del proyecto
     */
    private function getProjectConfigFile($filename, $rama) {
        $config = @file_get_contents(WikiIocPluginController::getProjectTypeDir($this->projectType) . "metadata/config/" . $filename);
        if ($config != FALSE) {
            $array = json_decode($config, true);
            return $array[$rama];
        }
    }

    /**
     * Extrae, del contenido del fichero, los datos correspondientes a la clave
     * @param string $file : ruta completa al fichero de datos del proyecto
     * @param string $metaDataSubSet : clave del contenido
     * @return array conteniendo el array de la clave 'metadatasubset' con los datos del proyecto
     */
    private function getProjectDataFile($file, $metaDataSubSet) {
        $contentFile = @file_get_contents($file);
        if ($contentFile != false) {
            $contentArray = json_decode($contentFile, true);
            return $contentArray[$metaDataSubSet];
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
            if ($result["zipFile"]) {
                if (!self::copyZip($result)) {
                    throw new Exception("Error en la còpia de l'arxiu zip des de la ubicació temporal");
                }
            }
            $file = WikiGlobalConfig::getConf('mediadir').'/'. preg_replace('/:/', '/', $result['ns']) .'/'.preg_replace('/:/', '_', $result['ns']);
            $ret = self::_getHtmlMetadata($result['ns'], $file, ".zip");
            $ret.= self::_getHtmlMetadata($result['ns'], $file, ".pdf");
        }
        return $ret;
    }

    private static function _getHtmlMetadata($ns, $file, $ext) {
        if ($ext === ".zip") {
            $P = ""; $nP = "";
            $class = "mf_zip";
            $mode = "HTML";
        }else {
            $P = "<p>"; $nP = "</p>";
            $class = "mf_pdf";
            $mode = "PDF";
        }
        if (@file_exists($file.$ext)) {
            $ret = '';
            $id = preg_replace('/:/', '_', $ns);
            $filename = str_replace(':','_',basename($ns)).$ext;
            $media_path = "lib/exe/fetch.php?media=$ns:$filename";
            $data = date("d/m/Y H:i:s", filemtime($file.$ext));

            if ($ext === ".pdf") {
                $ret.= '<p></p><div class="iocexport">';
                $ret.= '<span style="font-weight: bold;">Exportació PDF</span><br />';
                $ret.= '<form action="'.WIKI_IOC_MODEL.'renderer/basiclatex.php" id="export__form_'.$id.'" method="post">';
                $ret.= '<input name="filetype" value="zip" type="radio"> ZIP &nbsp;&nbsp;&nbsp;';
                $ret.= '<input name="filetype" value="pdf" checked type="radio"> PDF ';
                $ret.= '</form>';
                $ret.= '</div>';
            }
            $ret.= $P.'<span id="exportacio" style="word-wrap: break-word;">';
            $ret.= '<a class="media mediafile '.$class.'" href="'.$media_path.'" target="_blank">'.$filename.'</a> ';
            $ret.= '<span style="white-space: nowrap;">'.$data.'</span>';
            $ret.= '</span>'.$nP;
        }else{
            $mode = ($ext===".zip") ? "HTML" : "PDF";
            $ret.= '<span id="exportacio">';
            $ret.= '<p class="media mediafile '.$class.'">No hi ha cap exportació '.$mode.' feta</p>';
            $ret.= '</span>';
        }
        return $ret;
    }

    private static function copyZip($result){
        $dest = preg_replace('/:/', '/', $result['ns']);
        $path_dest = WikiGlobalConfig::getConf('mediadir').'/'.$dest;
        if (!file_exists($path_dest)){
            mkdir($path_dest, 0755, TRUE);
        }
        $ok = copy($result["zipFile"], $path_dest.'/'.$result["zipName"]);
        return $ok;
    }
}
