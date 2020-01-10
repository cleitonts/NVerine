<?php
namespace src\views\controller;

use src\creator\widget\Tools;
use src\entity\ProdutoEstruturadoETT;
use src\entity\ProdutoETT;
use src\entity\ProdutoFornecedorETT;
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
        $form = new ProdutoFORM();
        Tools::render($form->createForm(intval($_REQUEST['pesq_num'])));
    }

    public function persist($obj){
        global $mensagens;

        $mensagens->retorno = '?pagina=produto';
        
        /*propriedades do produto*/
        isset($_REQUEST['propriedade']['0']) ? $obj->ativo = "S" : "N";
        isset($_REQUEST['propriedade']['1']) ? $obj->loja_virtual = "S" : "N";
        isset($_REQUEST['propriedade']['2']) ? $obj->destaque = "S" : "N";
        isset($_REQUEST['propriedade']['3']) ? $obj->controla_estoque = "S" : "N";
        isset($_REQUEST['propriedade']['4']) ? $obj->controla_saldo = "S" : "N";
        isset($_REQUEST['propriedade']['5']) ? $obj->reserva_estoque = "S" : "N";
        isset($_REQUEST['propriedade']['6']) ? $obj->lote = "S" : "N";
        isset($_REQUEST['propriedade']['7']) ? $obj->terceiro = "S" : "N";
        
        if ($_REQUEST["duplicar"]){
            $obj->handle = null;    
        }
        
        /*realzar cadastro do produto*/
        if(empty($obj->handle)){
            $obj->cadastra();
        }else{
            $obj->atualiza();
        }

        /*realizar cadastro da tabela de preços , a função irá deletar os dados existentes deste produto
        e cadastrar novamente*/
        if(!empty($obj->tabela)) {
            $i = 0;
            foreach ($obj->tabela as $k => $t) {
                $t->codigo = $obj->handle;
                $t->tabela_preco = $i+1;
                
                // limpa somente 1x
                if (!empty($t->handle) && $i == 0 && !isset($_REQUEST["duplicar"])) {
                    $t->limpa();
                }

                if($_REQUEST["tabela"][$k+1]["existe_" . ($k+1)] == 0){
                    continue;
                }
                
                $t->cadastra();
                $i++;
            }
        }

        // produto estruturado
        if(!empty($obj->tabela_estruturada)) {
            $limpa = new ProdutoEstruturadoETT();
            $limpa->cod_pai = $obj->handle;
            $limpa->limpa();
            
            foreach ($obj->tabela_estruturada as $k => $estrutura){
                // se esta marcado como false eu nao quero inserir novamente
                if($_REQUEST["tabela_estruturada"][$k+1]["existe_" . ($k+1)] == 0){
                    continue;
                }
                
                $estrutura->cod_pai = $obj->handle;
                $estrutura->cadastra();
            }
        }

        dumper($obj);
        dumper($_REQUEST);
        
        // fornecedores
        if(!empty($obj->fornecedores)) {
            $limpa = new ProdutoFornecedorETT();
            $limpa->cod_produto = $obj->handle;
            $limpa->limpa();

            foreach ($obj->fornecedores as $k => $f){
                // se esta marcado como false eu nao quero inserir novamente
                if($_REQUEST["fornecedores"][$k+1]["existe_" . ($k+1)] == 0){
                    continue;
                }

                $f->cod_produto = $obj->handle;
                $f->cadastra();
            }
        }
    }
}