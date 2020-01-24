<?php


namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

use src\creator\widget\Tools;
use src\services\UAC\PermissoesETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\permissoesFORM;

$__MODULO__ = 'Administração';
$__PAGINA__ = 'Permissões';

class permissoesCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        $pesquisa = new permissoesFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $pesquisa = new permissoesFORM();
        Tools::render($pesquisa->createForm(intval($_REQUEST['pesq_num'])));
    }

    public function persist($obj)
    {
        global $mensagens;

        // retorna para a index para não causar erro
        $mensagens->retorno = "?pagina=permissoes";

        $permissoes = new PermissoesETT();
        $permissoes->grupo = $_REQUEST["handle"];
        $permissoes->limpa();
        $permissoes->cadastra($_REQUEST["permissoes"]);
    }
}