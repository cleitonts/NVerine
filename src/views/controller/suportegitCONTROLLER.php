<?php


namespace src\views\controller;


use src\creator\widget\Tools;
use src\views\forms\suportegitFORM;


global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Suporte';
$__PAGINA__ = 'GIT';

class suportegitCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new suportegitFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new suportegitFORM();
        Tools::render($form->createForm($_REQUEST["pesq_num"]));
    }

    public function persist($obj)
    {
        global $mensagens;
        $mensagens->retorno = '?pagina=suportegit';
        $obj->atualiza();
    }
}