<?php
/**
 * ProjectExportAction: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");

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
        parent::__construct();
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
        $this->projectModel->init([ProjectKeys::KEY_ID              => $this->projectID,
                                   ProjectKeys::KEY_PROJECT_TYPE    => $this->projectType,
                                   ProjectKeys::KEY_METADATA_SUBSET => $this->metadataSubset]);
        $this->dataArray = $this->projectModel->getCurrentDataProject();
    }

    protected function preResponseProcess() {
        parent::preResponseProcess();
        //Guarda una revisió del pdf existent abans no es guardi la nova versió
        $ext = ($this->mode === "xhtml") ? ".zip" : ".pdf";
        $output_filename = $this->projectID . ":" . str_replace(':', '_', $this->projectID) . $ext;
        media_saveOldRevision($output_filename);
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
                                           array("id"=> $this->projectID));
        $result = $render->process($this->dataArray);
        $result['ns'] = $this->projectNS;
        $result['ext'] = ".{$this->mode}";
        $ret['id'] = $this->idToRequestId($this->projectID);
        $ret['ns'] = $this->projectNS;

        switch ($this->mode) {
            case 'pdf' :
                $ret = ResultsWithFiles::get_html_metadata($result);
                break;
            case 'xhtml':
                $ret = ResultsWithFiles::get_html_metadata($result);
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
        return ResultsWithFiles::get_html_metadata($result);
    }

}
