<?php
/**
 * Component: Project / MetaData
 */
namespace prgfploe;
require_once(DOKU_PLUGIN . 'projectsdev/metadata/MetaDataRenderAbstract.php');

class MetaDataRender extends \MetaDataRenderAbstract {

    protected function processValues($values){
        return $values;
    }
}
