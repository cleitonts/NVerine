<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 02/04/2019
 * Time: 09:59
 */


namespace src\views\controller;

use DateTime;
use src\creator\widget\Tools;
use src\views\ControladoraCONTROLLER;
use src\views\forms\agendaFORM;
use src\views\relatorios\AgendaRelatorio;


global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Principal';
$__PAGINA__ = 'Agenda';


class relatorioAgendaController implements ControladoraCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new AgendaRelatorio();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new AgendaRelatorio();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {

    }
}