<?php
/**
 * ProjectExportAction: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ProjectExportAction  extends ProjectMetadataAction{
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
        $this->mode        = $params[ProjectKeys::KEY_MODE];
        $this->filetype    = $params[ProjectKeys::KEY_FILE_TYPE];
        $this->projectType = $params[ProjectKeys::KEY_PROJECT_TYPE];
        $this->projectID   = $params[ProjectKeys::KEY_ID];
        $this->metaDataSubSet = $params[ProjectKeys::KEY_METADATA_SUBSET];
        $this->projectNS   = $params[ProjectKeys::KEY_NS]?$params[ProjectKeys::KEY_NS]:$this->projectID;
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
        $fRenderer = $this->factoryRender;
        $fRenderer->init(['mode'            => $this->mode,
                          'filetype'        => $this->filetype,
                          'typesDefinition' => $this->typesDefinition,
                          'typesRender'     => $this->typesRender]);

        $render = $fRenderer->createRender($this->typesDefinition[$this->mainTypeName],
                                           $this->typesRender[$this->mainTypeName],
                                           array(ProjectKeys::KEY_ID => $this->projectID));

        $result = $render->process($this->dataArray);
        $result['ns'] = $this->projectNS;
        $ret['id'] = str_replace(":", "_", $this->projectID);
        $ret['ns'] = $this->projectNS;

        if ($this->mode === 'xhtml') {
            $ret["meta"] = ResultsWithFiles::get_html_metadata($result);
        }else {
            throw new Exception("ProjectExportAction: mode incorrecte.");
        }

        if (!WikiGlobalConfig::getConf('plugin')['iocexportl']['saveWorkDir']){
            $this->removeDir($result["tmp_dir"]);
        }

        $ret[ProjectKeys::KEY_ACTIVA_FTPSEND_BTN] = $this->getModel()->haveFilesToExportList();

        return $ret;
    }

    /**
     * @return array : Devuelve el subconjunto $rama del fichero de configuración del proyecto
     *                  Si se indica el metaDataSubSet, es que espera encontrar una estructura
     */
    private function getProjectConfigFile($filename, $rama, $metaDataSubSet=NULL) {
        $config = @file_get_contents(WikiIocPluginController::getProjectTypeDir($this->projectType) . "metadata/config/" . $filename);
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
        return ResultsWithFiles::get_html_metadata($result);
//        if ($result['error']) {
//            throw new Exception ("Error");
//        }else{
//            if ($result["zipFile"]) {
//                if (!self::copyZip($result)) {
//                    throw new Exception("Error en la còpia de l'arxiu zip des de la ubicació temporal");
//                }
//            }
//            $file = WikiGlobalConfig::getConf('mediadir').'/'. preg_replace('/:/', '/', $result['ns']) .'/'.preg_replace('/:/', '_', $result['ns']);
//            $ret = self::_getHtmlMetadata($result['ns'], $file);
//        }
//        return $ret;
    }

//    private static function _getHtmlMetadata($ns, $file) {
//        $ext = ".zip";
//        $P = "";
//        $nP = "";
//        $class = "mf_zip";
//        $mode = "HTML";
//
//        if (@file_exists($file.$ext)) {
//            $ret = '';
//            $id = preg_replace('/:/', '_', $ns);
//            $filename = str_replace(':','_',basename($ns)).$ext;
//            $media_path = "lib/exe/fetch.php?media=$ns:$filename";
//            $data = date("d/m/Y H:i:s", filemtime($file.$ext));
//
//            $ret.= $P.'<span id="exportacio" style="word-wrap: break-word;">';
//            $ret.= '<a class="media mediafile '.$class.'" href="'.$media_path.'" target="_blank">'.$filename.'</a> ';
//            $ret.= '<span style="white-space: nowrap;">'.$data.'</span>';
//            $ret.= '</span>'.$nP;
//        }else{
//            $ret.= '<span id="exportacio">';
//            $ret.= '<p class="media mediafile '.$class.'">No hi ha cap exportació '.$mode.' feta</p>';
//            $ret.= '</span>';
//        }
//        return $ret;
//    }
//
//    private static function copyZip($result){
//        $dest = preg_replace('/:/', '/', $result['ns']);
//        $path_dest = WikiGlobalConfig::getConf('mediadir').'/'.$dest;
//        if (!file_exists($path_dest)){
//            mkdir($path_dest, 0755, TRUE);
//        }
//        $ok = copy($result["zipFile"], $path_dest.'/'.$result["zipName"]);
//        return $ok;
//    }
//
//    /**
//     * Remove specified dir
//     * @param string $directory
//     */
//    private function removeDir($directory) {
//        if (file_exists($directory) && is_dir($directory) && is_readable($directory)) {
//            $dh = opendir($directory);
//            while ($contents = readdir($dh)) {
//                if ($contents != '.' && $contents != '..') {
//                    $path = "$directory/$contents";
//                    if (is_dir($path)) {
//                        $this->removeDir($path);
//                    }else {
//                        unlink($path);
//                    }
//                }
//            }
//            closedir($dh);
//
//            if (file_exists($directory)) {
//                rmdir($directory);
//            }
//        }
//    }
}
