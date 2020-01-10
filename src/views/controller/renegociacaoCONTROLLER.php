<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 28/02/2019
 * Time: 09:33
 */

namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = "Financeiro";
$__PAGINA__ = "Renegociação";

use src\views\ControladoraCONTROLLER;

class renegociacaoCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        // TODO: Implement pesquisaGUI() method.
        return;
    }

    public function singleGUI()
    {
        global $__PAGINA__;
        $__PAGINA__ .= " #{$_REQUEST['pesq_num']}";
        $form = new listaPrecoFORM();
        print_r(Tools::toJson($form->createForm(intval($_REQUEST["pesq_num"]))));

    }

    public function persist($obj)
    {
        // TODO: Implement persiste() method.
    }
}