<?php
namespace src\views\controller;

use src\creator\widget\Tools;
use src\entity\ProdutoETT;
use src\entity\ProdutoGUI;
use src\views\ControladoraCONTROLLER;
use src\views\forms\produtoFORM;



global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Cadastros';
$__PAGINA__ = 'Produto';

class ProdutoCONTROLLER implements ControladoraCONTROLLER{

    public function pesquisaGUI(){
        $pesquisa = new ProdutoFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI(){
        global $__PAGINA__;
        $form = new ProdutoFORM();
        Tools::render($form->createForm(intval($_REQUEST['pesq_num'])));
    }

    public function persist($obj){
        global $mensagens;

        $mensagens->retorno = '?pagina=produto';

        /*propriedades do produto*/

        isset($_REQUEST['propriedade']['0']) ? $obj->ativo = "1" : "0";
        isset($_REQUEST['propriedade']['1']) ? $obj->loja_virtual = "1" : "0";
        isset($_REQUEST['propriedade']['2']) ? $obj->destaque = "1" : "0";
        isset($_REQUEST['propriedade']['3']) ? $obj->controla_estoque = "1" : "0";
        isset($_REQUEST['propriedade']['4']) ? $obj->controla_saldo = "1" : "0";
        isset($_REQUEST['propriedade']['5']) ? $obj->reserva_estoque = "1" : "0";
        isset($_REQUEST['propriedade']['6']) ? $obj->lote = "1" : "0";
        isset($_REQUEST['propriedade']['7']) ? $obj->terceiro = "1" : "0";

        /*realzar cadastro do produto*/
        if(empty($obj->handle)){
            $obj->cadastra();
        }else{
            $obj->atualiza();
        }

        /*realizar cadastro da tabela de preços , a função irá deletar os dados existentes deste produto
        e cadastrar novamente*/
        if(!empty($obj->tabela)){
            $i = 0;
            foreach($obj->tabela as $d){
                if(empty($d->handle)) {
                    /*defindo o codigo como a handle do produto*/
                    $d->codigo = $obj->handle;

                    if($i == 0){
                        $d->limpa();
                    }

                    $i++;

                    $d->cadastra();
                }
            }
        }


    }
}