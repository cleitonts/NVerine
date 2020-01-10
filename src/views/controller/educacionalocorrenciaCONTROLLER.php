<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/10/2019
 * Time: 10:29
 */

namespace src\views\controller;


use src\creator\widget\Tools;
use src\views\ControladoraCONTROLLER;
use src\views\forms\educacionalocorrenciaFORM;


global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Educacional';
$__PAGINA__ = 'Ocorrências';


class educacionalocorrenciaCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        $pesquisa = new educacionalocorrenciaFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new educacionalocorrenciaFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        // TODO: Implement persist() method.
    }
}