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

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Principal';
$__PAGINA__ = 'Agenda';


class agendaCONTROLLER implements ControladoraCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new agendaFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new agendaFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        global $mensagens;
        $mensagens->retorno = '?pagina=agenda';

        // executa loop de recorrencia
        if(!empty($_REQUEST["recorrente"])) {

            // guarda valores originais
            $data_inicio_original = new DateTime($obj->data_inicial);
            $data_fim_original = new DateTime($obj->data_final);

            // executa para cada dia selecionado
            foreach ($_REQUEST["recorrente"] as $k => $r) {
                $data_inicio = clone $data_inicio_original;
                $data_fim = clone $data_fim_original;

                // executa durante todo o intervalo de tempo
                while ($data_inicio <= $data_fim) {
                    if ($data_inicio->format('w') == $k -1) {      // se $k == 1 então é domingo e sucessivamente...
                        $obj->data_inicial = $data_inicio->format('Y-m-d');
                        $obj->data_final = $obj->data_inicial;

                        // esta rotina é somente para cadastro
                        $obj->cadastra();
                    }
                    $data_inicio->modify('+1 day');
                }
            }
            return;
        }

        // se chegou ate aqui a rotina deve seguir normal
        if(empty($obj->handle)){
            $obj->cadastra();
        }
        else{
            $obj->atualiza();
        }
    }
}