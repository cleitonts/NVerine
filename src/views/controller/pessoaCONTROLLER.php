<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/05/2019
 * Time: 14:38
 */

namespace src\views\controller;

use src\creator\widget\Tools;
use src\views\ControladoraCONTROLLER;
use src\views\forms\pessoaFORM;

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Cadastros';
$__PAGINA__ = 'Pessoas';

class pessoaCONTROLLER implements ControladoraCONTROLLER
{
    public function pesquisaGUI(){
        $pesquisa = new pessoaFORM();
        Tools::render($pesquisa->createSearch());
    }
    public function singleGUI(){
        $form = new pessoaFORM();
        Tools::render($form->createForm(intval($_REQUEST['pesq_num'])));
    }
    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=pessoa';

        // mapeia os checkbox
        $obj->ativo = (isset($_REQUEST["funcoes"][1]))? 'S' : 'N';
        $obj->cliente = (isset($_REQUEST["funcoes"][2]))? 'S' : 'N';
        $obj->fornecedor = (isset($_REQUEST["funcoes"][3]))? 'S' : 'N';
        $obj->funcionario = (isset($_REQUEST["funcoes"][4]))? 'S' : 'N';
        $obj->empresa = (isset($_REQUEST["funcoes"][5]))? 'S' : 'N';
        $obj->contribuinte_icms = (isset($_REQUEST["funcoes"][6]))? 'S' : 'N';
        $obj->aluno = (isset($_REQUEST["funcoes"][7]))? 'S' : 'N';
        $obj->professor = (isset($_REQUEST["funcoes"][8]))? 'S' : 'N';
        $obj->credito->bloqueio = (isset($_REQUEST["funcoes"][9]))? 'S' : 'N';
        $obj->credito->restricao = (isset($_REQUEST["funcoes"][10]))? 'S' : 'N';
        $obj->transportador = (isset($_REQUEST["funcoes"][11]))? 'S' : 'N';

        if(empty($obj->handle)){
            $obj->cadastra();
        }

        // precisa rodar o atualiza
        $obj->atualiza();

        if(!empty($obj->contatos)) {
            $i = 1;
            foreach ($obj->contatos as $r){
                // se entrou aqui é pq o objeto existe
                $existe = $_REQUEST["contatos"][$i]["existe_".$i];
                $r->cod_pessoa = $obj->handle;

                if(!empty($r->handle)){
                    if($existe != 1){
                        $r->delete();
                    }
                    else{
                        $r->atualiza();
                    }
                }
                else{
                    $r->cadastra();
                }
                $i++;
            }
        }

        if(!empty($obj->enderecos)) {
            $i = 1;
            foreach ($obj->enderecos as $r){
                // se entrou aqui é pq o objeto existe
                $existe = $_REQUEST["enderecos"][$i]["existe_".$i];
                $r->cod_pessoa = $obj->handle;

                if(!empty($r->handle)){
                    if($existe != 1){
                        $r->delete();
                    }
                    else{
                        $r->atualiza();
                    }
                }
                else{
                    $r->cadastra();
                }
                $i++;
            }
        }
    }
}