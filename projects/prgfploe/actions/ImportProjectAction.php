<?php
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends ProjectMetadataAction {

    public function responseProcess() {
        //
        // 1. Comprobar que el tipo de proyecto a importar es adecuado
        //
        $model = $this->getModel();
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['id'], "management", $this->params['projectType']);
        $importProjectType = $metaDataQuery->getProjectType($this->params['project_import']);

        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['id'], "management", $this->params['projectType']);
        $data_management = $metaDataQuery->getDataProject();
        $jsonConfig = $model->getMetaDataJsonFile(FALSE, "workflow.json", $data_management['workflow']['currentState']);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $validProjectTypes = $jsonConfig['actions'][$actionCommand]['button']['parms']['DJO']['projectType'];

        if ($importProjectType == $validProjectTypes || in_array($importProjectType, $validProjectTypes)) {
            //
            // 2. Verificar permisos sobre el proyecto a importar
            //
            $permissions = $jsonConfig['actions'][$actionCommand]['permissions'];
            $import_modelManager = AbstractModelManager::Instance($importProjectType);
            $import_authorization = $import_modelManager->getAuthorizationManager('editProject');
            $has_perm_group = $this->array_in_array($permissions['groups'], $import_authorization->getAllowedGroups());
            $has_perm_rol = $this->array_in_array($permissions['rols'], $import_authorization->getAllowedRoles());

            if ($has_perm_group || $has_perm_rol) {
                $dataProject = $model->getCurrentDataProject();
                $import_metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['project_import'], "main", $importProjectType);
                $import_data = $import_metaDataQuery->getDataProject($this->params['project_import'], $importProjectType, "main");

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
                $B = ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"];
                $T = ['bloc' => array_search($import_data['tipusBlocModul'], $B),
                      'horesBloc' => $import_data['durada']];
                $dataProject['taulaDadesBlocs'][] = $T;
                //taulaDadesUF
                foreach ($import_data['taulaDadesUF'] as $key => $value) {
                    $T = $value;
                    $T['horesMinimes'] = $value['hores'];
                    $T['horesLLiureDisposicio'] = 0;
                    $T['ordreImparticio'] = $key + 1;
                    $dataProject['taulaDadesUF'][] = $T;
                }
                //taulaAvaluacioInicialUF
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
                foreach ($import_data['taulaDadesUnitats'] as $value) {
                    $U['unitat formativa'] = $value['unitat formativa'];
                    $U['nucli formatiu'] = $value['unitat'];
                    $U['nom'] = $value['nom'];
                    $U['hores'] = $value['hores'];
                    $dataProject['taulaDadesNuclisFormatius'][] = $U;
                }
                //resultatsAprenentatge
                $B = json_decode($import_data['resultatsAprenentatge'], TRUE);
                foreach ($B as $value) {
                    if (preg_match("Uf(x)[._]RA(y)", $value['id'], $match)) {
                        $Z['uf'] = $match[1];
                        $Z['ra'] = $match[2];
                    }
                    if (preg_match("RA(y)[.|](UF)?(x)", $value['id'], $match)) {
                        $Z['uf'] = $match[2];
                        $Z['ra'] = $match[1];
                    }
                    $Z['descripcio'] = $value['descripcio'];
                    $dataProject['resultatsAprenentatge'][] = $Z;
                }
                throw new Exception("Petición correcta: $importProjectType");
            }
        }else {
            throw new Exception("El tipus de projecte $importProjectType no és un tipus vàlid per a la importació.");
        }
        throw new Exception("Action no definida. Petición: $importProjectType");
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
