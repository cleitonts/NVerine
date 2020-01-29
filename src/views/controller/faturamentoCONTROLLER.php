<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 16:36
 */

namespace src\views\controller;

use src\creator\widget\Tools;
use src\entity\FaturamentoETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\faturamentoFORM;

class faturamentoCONTROLLER implements ControladoraCONTROLLER
{
    // lista de pesquisa
    public function pesquisaGUI()
    {
        $pesquisa = new faturamentoFORM();
        Tools::render($pesquisa->createSearch());
    }

    // edi��o
    public function singleGUI()
    {
        $form = new faturamentoFORM();
        Tools::render($form->createForm(intval($_REQUEST['pesq_num'])));
    }

    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=faturamento&pesq_tipo='.$obj->cod_tipo;

        $obj->status->handle = $_REQUEST["status"];
        $obj->cod_origem = FaturamentoETT::ORIGEM_SISTEMA;
        $obj->nota_fiscal->modelo = $_REQUEST["modelo"];
        $obj->nota_fiscal->natureza_operacao = $_REQUEST["natureza_operacao"];
        $obj->nota_fiscal->informacoes_fisco = $_REQUEST["informacoes_fisco"];
        $obj->nota_fiscal->chave_referencia = $_REQUEST["chave_referencia"];

        //dumper($obj);
        //dumper($_REQUEST);

        // se chegou ate aqui a rotina deve seguir normal
        if(empty($obj->nota)){
            $obj->cadastra();
        }
        else{
            $obj->atualiza();
        }

        foreach ($obj->produtos as $k => $produto){

            if(empty($produto->handle)) {
                $produto->setNota($obj->handle);
                $produto->cadastra();
            }
            else {
                // so cancela se for or�amento
//                if(isset($_REQUEST["prod_cancela_{$i}"]) && $nota->status->handle <= 2) { // apagar produtos
//                    $produto->cancela();
//                }
                //else{
                $produto->atualiza();
                //}
            }
        }

        foreach ($obj->duplicatas as $duplicata){
            if(empty($duplicata->handle)) {
                $duplicata->setNota($obj->handle);
                $duplicata->cadastra();
            }
            else {
                // a��o a realizar
//                if(isset($_REQUEST["propriedade_2"])) { // apagar duplicatas
//                    $duplicata->cancela();
//                }
//                else {
                    $duplicata->atualiza();
                //}
            }
        }

        $mensagens->retorno .= '&pesq_num='.$obj->handle;
    }
}