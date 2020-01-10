<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 31/07/2019
 * Time: 13:44
 */

namespace src\views\controller;


use src\creator\widget\Component;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\GaleriaETT;
use src\entity\SuporteChamadoETT;
use src\entity\SuporteChamadoGUI;
use src\entity\SuporteHistoricoETT;
use src\entity\UsuarioGUI;
use src\views\ControladoraCONTROLLER;

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Suporte';
$__PAGINA__ = 'Kanban';


class suportekanbanCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        $widget = new Widget();
        $widget->includes[] = "src/public/js/suporte/kanban.js";
        $widget->header->title = "Kanban";
        $widget->header->icon = "fa fa-persons";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // carrega kanban
        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "row flex-row flex-sm-nowrap", "id" => "gera_kanban");

        $div2 = new Component();
        $div2->tag = "div";
        $div2->attr = array(
            "id" => "wrapper_galeria",
            "acao" => "gera_galeria",
            "class" => "d-none",
            "target" => GaleriaETT::TARGET_SUPORTE,
            "referencia" => 0
        );

        $tabs->form->children[] = $div;
        $tabs->form->children[] = $div2;
        $widget->body->tabs["Editar"] = $tabs;

        $widget->setDefaults();
        Tools::render($widget);
    }

    public function singleGUI()
    {
        Tools::render(Tools::returnError("Não é possível editar kanban."));
    }

    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=suportekanban';

        if (!empty($_REQUEST["novo"])) {
            $chamado = new SuporteChamadoETT();
            $chamado->cod_tipo = $chamado::TIPO_DIVERSOS;
            $chamado->cod_status = $chamado::STATUS_TRIAGEM;
            $chamado->cod_prioridade = $chamado::PRIORIDADE_MINIMA;
            $chamado->cod_cliente = 2;
            $chamado->assunto = "Novo chamado no kanban";
            $chamado->cadastra();
            return;
        }

        // alteração de status
        if (!empty($_REQUEST["onlyStatus"])) {

            // não precisa diferenciar, entao jogo td em um unico array
            $arr = $_REQUEST["changes"]["original"];
            
            if(!is_array($arr)){
                $arr = array();
            }

            $arr = array_merge($arr, $_REQUEST["changes"]["atual"]);

            foreach ($arr as $r) {
                // monta chamado antigo para detectar alterações
                $chamado = new SuporteChamadoGUI();
                $chamado->pesquisa["pesq_chamado"] = $r["handle"];
                $chamado->fetch();

                if (count($chamado->itens) <> 1) {
                    mensagem("Handle parace inconsistente", MSG_ERRO);
                    finaliza();
                }

                $chamado = $chamado->itens[0];

                // apenas o principal guarda alteração de status
                if ($r["handle"] == $_REQUEST["changes"]["card"]) {
                    $string = "";

                    if ($chamado->cod_status != $r["status"])
                        $string .= "<p>Alterou o status para: <strong>" . SuporteChamadoETT::getNomeStatus($r["status"]) . ".</strong></p>";

                    $historico = new SuporteHistoricoETT();
                    $historico->chamado = $chamado->handle;
                    $historico->hora = date("H:i:s");
                    $historico->observacao_sistema = $string;
                    $historico->revisao = $_REQUEST["revisao"];
                    $historico->data = date("Y-m-d");
                    $historico->status_chamado = $chamado->cod_status;

                    if (!empty($string)) {
                        $historico->cadastra();
                    }
                }

                $chamado->after = $r["i"];
                $chamado->cod_status = $r["status"];
                $chamado->atualiza(true);
            }
            $mensagens->retorno = '?pagina=suportekanban';
            return;
        }

        // monta chamado antigo para detectar alterações
        $chamado = new SuporteChamadoGUI();
        $chamado->pesquisa["pesq_chamado"] = $_REQUEST["handle"];
        $chamado->fetch();

        if (count($chamado->itens) <> 1) {
            mensagem("Handle parace inconsistente", MSG_ERRO);
            finaliza();
        }

        $chamado = $chamado->itens[0];
        $string = "";

        if ($chamado->cod_status == SuporteChamadoETT::STATUS_TRIAGEM && !empty($_REQUEST["responsavel"])) {
            $chamado->cod_status = SuporteChamadoETT::STATUS_CONFIRMADO;
            $string .= "<p>Alterou o status para: <strong>" . SuporteChamadoETT::getNomeStatus(SuporteChamadoETT::STATUS_CONFIRMADO) . ".</strong></p>";
        }

        if ($chamado->assunto != $_REQUEST["assunto"])
            $string .= "<p>Alterou o assunto.</p>";

        if (converteDataSql($chamado->prazo) != $_REQUEST["prazo"])
            $string .= "<p>Definiu conclusão de <strong>" . $_REQUEST["prazo"] . ".</strong></p>";

        if ($chamado->cod_responsavel != $_REQUEST["responsavel"]) {
            $responsavel = new UsuarioGUI();
            $responsavel->handle = $_REQUEST["responsavel"];
            $responsavel->fetch();
            $string .= "<p>Alterou o responsável para: <strong>" . $responsavel->itens[0]->nome . ".</strong></p>";
        }

        if ($chamado->cod_prioridade != $_REQUEST["prioridade"])
            $string .= "<p>Alterou a prioridade para: <strong>" . SuporteChamadoETT::getNomePrioridade($_REQUEST['prioridade']) . ".</strong></p>";

        if ($chamado->cod_tipo != $_REQUEST["tipo"])
            $string .= "<p>Alterou o tipo do chamado.</p>";

        if ($chamado->cod_cliente != $_REQUEST["cod_cliente"])
            $string .= "<p>Alterou o cliente para: <strong>" . $_REQUEST["cliente"] . ".</strong></p>";

        if (!empty($string)) {
            $historico = new SuporteHistoricoETT();
            $historico->chamado = $chamado->handle;
            $historico->hora = date("H:i:s");
            $historico->revisao = $_REQUEST["revisao"];
            $historico->data = date("Y-m-d");
            $historico->status_chamado = $chamado->cod_status;
            $historico->observacao_sistema = $string;
            $historico->cadastra();
        }

        // cadastra novamente
        if (!empty($_REQUEST["editor_comentarios"])) {
            $historico = new SuporteHistoricoETT();
            $historico->chamado = $chamado->handle;
            $historico->hora = date("H:i:s");
            $historico->revisao = $_REQUEST["revisao"];
            $historico->data = date("Y-m-d");
            $historico->status_chamado = $chamado->cod_status;
            $historico->comentarios = $_REQUEST["editor_comentarios"];
            $historico->cadastra();
        }

        // atualiza o chamado em si
        $update = new SuporteChamadoETT();
        $update->handle = $chamado->handle;
        $update->cod_tipo = $_REQUEST["tipo"];
        $update->cod_status = $chamado->cod_status;
        $update->cod_prioridade = $_REQUEST["prioridade"];
        $update->cod_cliente = $_REQUEST["cod_cliente"];
        $update->cod_responsavel = $_REQUEST["responsavel"];
        $update->contato_nome = utf8_decode($_REQUEST["nome_contato"]);
        $update->contato_email = utf8_decode($_REQUEST["email"]);
        $update->contato_telefone = $_REQUEST["telefone"];
        $update->copia_carbono = utf8_decode($_REQUEST["cc"]);
        $update->assunto = utf8_decode($_REQUEST["assunto"]);
        $update->prazo = converteData($_REQUEST["prazo"]);
        $update->duplicado = $_REQUEST["duplicado"];
        $update->atualiza();

        $mensagens->retorno = '?pagina=suportekanban';
    }
}