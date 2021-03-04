<?php
/**
 * ImportProjectAction: importa les dades d'un altre projecte
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends ProjectAction {

    public function responseProcess() {
        //
        // 1. Comprobar que el tipo de proyecto a importar es adecuado
        //
        $model = $this->getModel();
        $projectID = $this->params[ProjectKeys::KEY_ID];
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($projectID, "management", $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $importProjectType = $metaDataQuery->getProjectType($this->params['project_import']);

        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($projectID, "management", $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $data_management = $metaDataQuery->getDataProject();
        $action = $model->getMetaDataActionWorkflowFile($data_management['workflow']['currentState'], $actionCommand);
        $validProjectTypes = $action['button']['parms']['DJO'][ProjectKeys::KEY_PROJECT_TYPE];

        if ($importProjectType == $validProjectTypes || in_array($importProjectType, $validProjectTypes))   {
            //
            // 2. Verificar permisos sobre el proyecto a importar
            //
            $permissions = $action['permissions'];
            $import_modelManager = AbstractModelManager::Instance($importProjectType);
            $import_authorization = $import_modelManager->getAuthorizationManager('editProject');
            $has_perm_group = $this->array_in_array($permissions['groups'], $import_authorization->getAllowedGroups());
            $has_perm_rol = $this->array_in_array($permissions['rols'], $import_authorization->getAllowedRoles());

            if ($has_perm_group || $has_perm_rol) {
                $dataProject = $model->getCurrentDataProject();
                $import_metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['project_import'], "main", $importProjectType);
                $import_data = $import_metaDataQuery->getDataProject($this->params['project_import'], $importProjectType, "main");

                // 3. Verificar una importació anterior
                if (empty($import_data['nsProgramacio'])) {
                    //JOSEP: TODO- Caldrà plantejar una importació diferrnt en funció del tipus de pojecte (ptfploe, sintesi o fct). Aquesta correspon a ptfploe.
                    // Taula d'importació
                    $dataProject['cicle']                     = $import_data['cicle'];
                    $dataProject['modulId']                   = $import_data['modulId'];
                    $dataProject['modul']                     = $import_data['modul'];
                    $dataProject['duradaCicle']               = $import_data['duradaCicle'];
                    $dataProject['notaMinimaAC']              = $import_data['notaMinimaAC'];
                    $dataProject['notaMinimaEAF']             = $import_data['notaMinimaEAF'];
                    $dataProject['notaMinimaJT']              = $import_data['notaMinimaJT'];
                    $dataProject['notaMinimaPAF']             = $import_data['notaMinimaPAF'];
                    $dataProject['taulaInstrumentsAvaluacio'] = $import_data['dadesQualificacioUFs'];
                    //taulaDadesBlocs
                    if (!is_array($dataProject['taulaDadesBlocs'])) $dataProject['taulaDadesBlocs'] = json_decode($dataProject['taulaDadesBlocs'], TRUE);
                    $B = ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"];
                    $T = ['bloc' => array_search($import_data['tipusBlocModul'], $B),
                          'horesBloc' => $import_data['durada']];
                    $dataProject['taulaDadesBlocs'][] = $T;
                    //taulaDadesUF
                    if (!is_array($dataProject['taulaDadesUF'])) $dataProject['taulaDadesUF'] = json_decode($dataProject['taulaDadesUF'], TRUE);
                    foreach ($import_data['taulaDadesUF'] as $key => $value) {
                        $T = $value;
                        $T['horesMinimes'] = $value['hores'];
                        $T['horesLLiureDisposicio'] = 0;
                        $T['ordreImparticio'] = $key + 1;
                        $dataProject['taulaDadesUF'][] = $T;
                    }
                    //taulaAvaluacioInicialUF
                    if (!is_array($dataProject['taulaAvaluacioInicialUF'])) $dataProject['taulaAvaluacioInicialUF'] = json_decode($dataProject['taulaAvaluacioInicialUF'], TRUE);
                    $APT = ["NO", "INICI", "PER_UF"];
                    $APR = ["No en té", "A l'inici del semestre", "A l'inici de la UF"];
                    foreach ($import_data['taulaDadesUF'] as $key => $value) {
                        $T = ['unitat formativa' => $value['unitat formativa']];
                        if ($import_data['avaluacioInicial'] === $APT[1] && $key = 0)
                            $T['tipus'] = $APR[1];
                        elseif ($import_data['avaluacioInicial'] === $APT[2])
                            $T['tipus'] = $APR[2];
                        else
                            $T['tipus'] = $APR[0];
                        $dataProject['taulaAvaluacioInicialUF'][] = $T;
                    }
                    //taulaDadesNuclisFormatius
                    if (!is_array($dataProject['taulaDadesNuclisFormatius'])) $dataProject['taulaDadesNuclisFormatius'] = json_decode($dataProject['taulaDadesNuclisFormatius'], TRUE);
                    foreach ($import_data['taulaDadesUnitats'] as $value) {
                        $U['unitat formativa'] = $value['unitat formativa'];
                        $U['nucli formatiu'] = $value['unitat'];
                        $U['nom'] = $value['nom'];
                        $U['hores'] = $value['hores'];
                        $dataProject['taulaDadesNuclisFormatius'][] = $U;
                    }
                    //resultatsAprenentatge
                    if (!is_array($dataProject['resultatsAprenentatge'])) $dataProject['resultatsAprenentatge'] = json_decode($dataProject['resultatsAprenentatge'], TRUE);
                    $B = json_decode($import_data['resultatsAprenentatge'], TRUE);
                    foreach ($B as $value) {
                        if (preg_match('/((UF(\d)){0,1}.{0,1})RA(\d)/', $value['id'], $match)) {
                            $Z['uf'] = $match[3];
                            $Z['ra'] = $match[4];
                        }elseif (preg_match('/RA(\d)(.{0,1}(UF(\d)){0,1})/', $value['id'], $match)) {
                            $Z['uf'] = $match[4];
                            $Z['ra'] = $match[1];
                        }else {
                            $Z['uf'] = "";
                            $Z['ra'] = "";
                        }
                        $Z['descripcio'] = $value['descripcio'];
                        $Z['ponderacio'] = "";
                        $Z['hores'] = 0;
                        $dataProject['resultatsAprenentatge'][] = $Z;
                    }

                    $summary = "Importació de dades correcta des del projecte '{$this->params['project_import']}'";
                    if ($model->setDataProject(json_encode($dataProject), $summary)) {
                        $response['info'] = self::generateInfo("info", $summary, $projectID);
                    }else {
                        $response['info'] = self::generateInfo("error", "Error en la $summary", $projectID);
                        $response['alert'] = "Error en la $summary";
                    }

                    $import_data['nsProgramacio'] = $projectID;
                    if (! $import_metaDataQuery->setMeta(json_encode($import_data), "main", "Dades importades pel projecte '$projectID'")) {
                        $response['info'] = self::generateInfo("error", "Error en l'actualització de les dades a '{$this->params['project_import']}' després de la importació.", $projectID);
                    }
                }else {
                    $response['info'] = self::generateInfo("error", "El projecte '{$this->params['project_import']}' ja ha estat importat anteriorment.", $projectID);
                    $response['alert'] = "El projecte '{$this->params['project_import']}' ja ha estat importat anteriorment.";
                }
            }else {
                $response['alert'] = "No tens permissos per a importar dades del projecte '{$this->params['project_import']}'.";
            }
        }else {
            $response['alert'] = "El tipus de projecte $importProjectType no és un tipus vàlid per a la importació.";
        }

        return $response;
    }

    // Busca si algún elemento de $array1 está incluido en $array2
    public function array_in_array($array1, $array2) {
        $has = FALSE;
        foreach ($array1 as $elem) {
            if (in_array($elem, $array2)) {
                $has = TRUE;
                break;
            }
        }
        return $has;
    }

}
