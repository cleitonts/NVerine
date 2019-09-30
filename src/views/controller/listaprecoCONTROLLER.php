<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 11/02/2019
 * Time: 13:21
 */

namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = "Cadastros";
$__PAGINA__ = "Lista de Preço";

use src\creator\widget\Tools;
use src\entity\ListaPrecoETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\listaPrecoFORM;

class listaprecoCONTROLLER implements ControladoraCONTROLLER
{
    // lista de pesquisa
    public function pesquisaGUI()
    {
        global $__PAGINA__;
        $__PAGINA__ .= " Lista";
        $pesquisa = new listaPrecoFORM();
        Tools::render($pesquisa->createSearch());
    }

    // edição
    public function singleGUI()
    {
        global $__PAGINA__;
        $__PAGINA__ .= " #{$_REQUEST['pesq_num']}";
        $form = new listaPrecoFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    /**
     * @param $traduzido
     * Salva os dados, caso não tenha indice é cadastro novo, caso tenha ele exclui os antigos
     */
    public function persist($obj)
    {
        global $mensagens;

        // dispara retorno
        $mensagens->retorno = "?pagina=listaPreco";

        $lista = new ListaPrecoETT();
        $indice = $lista->limpa($obj->indice);

        // cadastra novos itens
        foreach ($obj->produtos as $r){
            $r->indice = $indice;
            $r->data_fim = $obj->data_fim;
            $r->data_inicio = $obj->data_inicio;
            $r->nome = $obj->nome;
            $r->ativo = $obj->ativo;
            $r->cadastra();
        }
    }
}