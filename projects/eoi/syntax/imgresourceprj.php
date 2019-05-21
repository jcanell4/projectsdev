<?php

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_projectsdev_projects_eoi_imgresourceprj extends DokuWiki_Syntax_Plugin
{

    const RESOURCE_PATH = DOKU_PLUGIN . "projectsdev/projects/eoi/exporter/resources/";


    function getInfo()
    {
        return array(
            'name' => 'IOC image resource project Plugin',
            'desc' => 'Plugin to parse gif, animated gif, png, jpeg and other image files',
            'sintax' => '<img-resource-prj>image path in the server</img-resource-prj>',
            'url' => 'http://ioc.gencat.cat/',
        );
    }

    function getType()
    {
        return 'substition';
    }

    function getPType()
    {
        return 'block';
    }

    function getSort()
    {
        return 43;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<img-resource-prj>(?=.*?</img-resource-prj>)', $mode, 'plugin_projectsdev_projects_eoi_imgresourceprj');
    }

    function postConnect()
    {
        $this->Lexer->addExitPattern('</img-resource-prj>', 'plugin_projectsdev_projects_eoi_imgresourceprj');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler)
    {
        return array($state, $match);
    }

    function render($mode, &$renderer, $data)
    {

        list($state, $text) = $data;

        if ($mode === 'wikiiocmodel_psdom') {
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    $node = new ImgResourcePrjNodeDoc();
                    $renderer->getCurrentNode()->addContent($node);
                    $renderer->setCurrentNode($node);
                    break;
                case DOKU_LEXER_UNMATCHED:
                    $instructions = get_latex_instructions($text);
                    //delete document_start and document_end instructions
                    array_shift($instructions);
                    array_pop($instructions);
                    //delete p_open and p_close instructions
                    array_shift($instructions);
                    array_pop($instructions);

                    $src = self::RESOURCE_PATH . $instructions[0][1][0];

                    $renderer->getCurrentNode()->setSource($src);

                    break;

                case DOKU_LEXER_EXIT:
                    $renderer->setCurrentNode($renderer->getCurrentNode()->getOwner());
                    break;
            }
            return TRUE;

        } elseif ($mode === 'iocxhtml' || $mode === 'xhtml' || $mode === 'wikiiocmodel_ptxhtml') {
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    break;
                case DOKU_LEXER_UNMATCHED :
                    $instructions = get_latex_instructions($text);
                    //delete document_start and document_end instructions
                    array_shift($instructions);
                    array_pop($instructions);
                    //delete p_open and p_close instructions
                    array_shift($instructions);
                    array_pop($instructions);


                    $renderer->doc .= '<img class="media" src="resources/' . $text . '"/>';

                    break;
                case DOKU_LEXER_EXIT :
                    break;
            }
            return TRUE;

        }

        return FALSE;
    }

}
