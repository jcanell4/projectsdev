<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "pblactivity"
 * @author Rafael Claver
 */
namespace pblactivity;

if (!defined('DOKU_INC')) die();

class FactoryAuthorization extends \ProjectFactoryAuthorization {

    const PROJECT_AUTH = __DIR__ . "/";

    public function __construct() {
        parent::__construct(self::PROJECT_AUTH);
    }

}
