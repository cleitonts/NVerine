<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 18/01/2019
 * Time: 13:43
 */

namespace src\views\controller;
global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = "Cadastros";
$__PAGINA__ = "Produto";

use src\services\Dumper;
use src\views\ControladoraCONTROLLER;
use src\creator\widget\Tools;
use src\views\forms\expedicaoFORM;

class expedicaoCONTROLLER implements ControladoraCONTROLLER
{
    // lista de pesquisa
    public function pesquisaGUI()
    {
        $pesquisa = new expedicaoFORM();
        Dumper::dump("teste");
        Tools::render($pesquisa->createSearch());
    }

    // edição
    public function singleGUI()
    {
        $form = new expedicaoFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        // TODO: Implement persiste() method.
    }
}