<?php


namespace src\views\controller;

use src\creator\widget\Tools;
use src\views\ControladoraCONTROLLER;
use src\views\forms\suportediagFORM;


global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Suporte';
$__PAGINA__ = 'Diag';

class suportediagCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        $pesquisa = new suportediagFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new suportediagFORM();
        Tools::render($form->createForm($_REQUEST["pesq_num"]));
    }

    public function persist($obj)
    {
        global $mensagens;
        $mensagens->retorno = '?pagina=suportediag';
        
        if(empty($obj->handle)){
            $obj->cadastra();
        }
        else{
            $obj->atualiza();
        }
    }
}