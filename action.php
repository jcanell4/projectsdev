<?php
/**
 * Aquest action de plugin és disparat pel procés init
 * La lògica comuna dels actions dels projects d'aquest plugin es troba a WikiIocPluginAction
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class action_plugin_projectsdev extends WikiIocPluginAction {

    function register(Doku_Event_Handler $controller) {
        parent::register($controller);
    }

}
