<?php
/**
 * eoiProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once(WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once(WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class eoiProjectModel extends AbstractProjectModel
{

    public function __construct($persistenceEngine)
    {
        parent::__construct($persistenceEngine);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getProjectDocumentName()
    {
        $ret = $this->getMetaDataProject();
        return $ret['fitxercontinguts'];
    }

    protected function getContentDocumentIdFromResponse($responseData)
    {
        if ($responseData['projectMetaData']["fitxercontinguts"]['value']) {
            $contentName = $responseData['projectMetaData']["fitxercontinguts"]['value'];
        } else {
            $contentName = end(explode(":", $this->getTemplateContentDocumentId($responseData)));
        }
        return $this->id . ":" . $contentName;
    }

    public function generateProject()
    {
        // Considerem que el projecte es generat si les dates son correctes, això permet fer la exportació

        $success = $this->validateProjectDates(); // TODO: cridar a una funció que validi les dades

        if ($success) {
            $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        } else {
            $ret[ProjectKeys::KEY_GENERATED] = FALSE;
        }

        $this->projectMetaDataQuery->setProjectSystemStateAttr("generated", $ret[ProjectKeys::KEY_GENERATED]);
    }

    protected function validateProjectDates()
    {
        $projectData = $this->getData();

        $today = new DateTime();
        $dataProva1 = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataProva1']['value']);
        $dataProva2 = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataProva2']['value']);
        $dataResultats = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataResultats']['value']);
        $dataDemandaNE = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataDemandaNE']['value']);

        $validated = true;
        $validated &= $dataProva1 > $today && $dataProva2 > $today;
        $validated &= $dataResultats > $dataProva1 && $dataResultats > $dataProva2;
        $validated &= $dataDemandaNE > $today && $dataDemandaNE < $dataProva1 && $dataDemandaNE < $dataProva2;

        return $validated;
    }

    public function validateTemplates() {
        $templateDates = $this->projectMetaDataQuery->getProjectSystemStateAttr("templateDates");

        $data = $this->getData();
//        $configTemplates = json_decode($data['projectMetaData']['plantilla']['value']);
        $configTemplates = $data['projectMetaData']['plantilla']['value'];
        $projectTemplates = explode(',', $configTemplates);


        if (count($templateDates) !== count($projectTemplates)) {
            // Ha canviat el nombre de plantilles
            return false;
        }

        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir() . "metadata/plantilles/";

        foreach ($projectTemplates as $key) {
            //$file = wikiFN($this->id .':' . $key); // ALERTA! Això es la data dels fitxers, però necessitem la dels templates!
            $file = $pdir . $key . '.txt';

            if (!isset($templateDates[$key])) {
                // No existeix el nom del fitxer
                return false;
            }

            $currentFileTime = filemtime($file);

            if ($currentFileTime != $templateDates[$key]) {
                // La plantilla del projecte ha estat modificada
                return false;
            }
        }



        return true;
    }

    public function setTemplateDocuments($files){
        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir() . "metadata/plantilles/";

        if (is_array($files)) {
            $templates = $files;
        } else {
            // Considerem que es una llista de fitxers separats per comes
            $templates = explode(',', $files);
        }

        $templateDates = [];



        foreach ($templates as $template) {

            $fullpath = $pdir . $template . ".txt";
            $plantilla = file_get_contents($fullpath);
            $destino = $this->getContentDocumentId($template);
            $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
                PageKeys::KEY_WIKITEXT => $plantilla,
                PageKeys::KEY_SUM => "generate project"]);

            $templateDates[$template] = filemtime($fullpath);
        }

        $this->projectMetaDataQuery->setProjectSystemStateAttr("templateDates", $templateDates);
    }

    public function createTemplateDocument($data)
    {
        $templates = $data['projectMetaData']["plantilla"]['value'];

        $this->setTemplateDocuments($templates);

    }

    public function modifyACLPageToSupervisor($parArr)
    {
        $project_ns = $parArr['id'] . ":*";

        // S'ha modificat el supervisor
        if ($parArr['old_supervisor'] !== $parArr['new_supervisor']) {
            if ($parArr['old_supervisor'] !== $parArr['new_autor']
                && $parArr['old_supervisor'] !== $parArr['new_responsable']) {
                //Elimina ACL de old_responsable sobre la página del proyecto
                if ($parArr['old_supervisor'] && $parArr['old_supervisor'] !== "") {
                    $ret = PagePermissionManager::deletePermissionPageForUser($project_ns, $parArr['old_supervisor']);
                    if (!$ret) $retError[] = "Error en eliminar permissos a '${parArr['old_supervisor']}' sobre '$project_ns'";
                }
            }

            // Si el supervisor es també autor o responsable te permisos superiors, no cal fer res
            //Crea ACL para new_responsable sobre la pàgina del projecte
            if ($parArr['new_supervisor'] !== $parArr['new_autor']
                && $parArr['new_supervisor'] !== $parArr['new_responsable']
                && $parArr['new_supervisor'] !== '') {
                $ret = PagePermissionManager::updatePagePermission($project_ns, $parArr['new_supervisor'], AUTH_READ, TRUE);
                if (!$ret) $retError[] = "Error en assignar permissos a '${parArr['new_supervisor']}' sobre '$project_ns'";
            }
        }

        if ($retError) {
            foreach ($retError as $e) {
                throw new UnknownProjectException($project_ns, $e);
            }
        }
    }

    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFields($data)
    {

        $values = json_decode($data, true);

        $values["dataReclamacions"] = $this->sumDate($values["dataResultats"], 3);
        $values["dataProvaNE1"] = $this->sumDate($values["dataProva1"], 5);
        $values["dataProvaNE2"] = $this->sumDate($values["dataProva2"], 5);
//        }

        $data = json_encode($values);
        return parent::updateCalculatedFields($data);
    }

    protected function sumDate($date, $days, $months = 0, $years = 0, $sep = "-")
    {
        if (!is_numeric($days) || !is_numeric($months) || !is_numeric($years)) {
            return "[ERROR! paràmetres incorrectes ($days, $months, $years)]"; //TODO: internacionalitzar
        }

        $newDate = $date;

        if ($days > 0) {
            $calculated = strtotime("+" . $days . " day", strtotime($date));
            $newDate = date("Y" . $sep . "m" . $sep . "d", $calculated);
        }

        if ($months > 0) {

            $calculated = strtotime("+" . $months . " month", strtotime($newDate));
            $newDate = date("Y" . $sep . "m" . $sep . "d", $calculated);
        }

        if ($years > 0) {
            $calculated = strtotime("+" . $years . " year", strtotime($newDate));
            $newDate = date("Y" . $sep . "m" . $sep . "d", $calculated);
        }

        return $newDate;
    }



}