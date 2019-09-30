<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 27/09/2019
 * Time: 12:36
 */

namespace src\views\controller;

use src\creator\widget\Tools;
use src\views\ControladoraCONTROLLER;
use src\views\forms\cadastroFORM;

class cadastroCONTROLLER implements ControladoraCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new cadastroFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $pesquisa = new cadastroFORM();
        Tools::render($pesquisa->createForm(intval($_REQUEST['pesq_num'])));
    }

    public function persist($obj)
    {
        dumper($obj);
        dumper($_REQUEST);
    }
}