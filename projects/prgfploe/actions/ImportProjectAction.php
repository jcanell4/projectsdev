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
                $dataProject['taulaDadesUF']['bloc']             = $import_data['taulaDadesUF']['bloc'];
                $dataProject['taulaDadesUF']['unitat formativa'] = $import_data['taulaDadesUF']['unitat formativa'];
                $dataProject['taulaDadesUF']['nom']              = $import_data['taulaDadesUF']['nom'];
                $dataProject['taulaDadesUF']['hores']            = $import_data['taulaDadesUF']['hores'];
                $dataProject['taulaDadesUF']['ponderació']       = $import_data['taulaDadesUF']['ponderació'];
                //taulaAvaluacioInicialUF

                //taulaDadesNuclisFormatius

                //resultatsAprenentatge

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
