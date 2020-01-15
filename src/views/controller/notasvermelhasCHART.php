<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 07/03/2019
 * Time: 15:46
 */

namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Principal';
$__PAGINA__ = 'Agenda';

use src\creator\widget\Chart;
use src\creator\widget\Fields;
use src\creator\widget\Options;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\relatorios\notasVermelhasREL;
use src\views\ControladoraCHART;

class notasvermelhasCHART implements ControladoraCHART
{

    public function chartGUI()
    {
        $widget = new Widget();
        $widget->includes[] = "src/public/js/relatorio/notasvermelhasCHART.js";
        $widget->header->title = "Quantidade de alunos com notas abaixo da média";
        //$widget->header->icon = "fa fa-truck";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        //$tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        $tabs->form->action = "?pagina=notasVermelhas";

        //cria tabelas
        $chart = new Chart();
        $chart->name = "notas";
        $chart->type = Chart::STACKED_GROUP;
        $chart->entity = notasVermelhasREL::class;               // passar a classe/entidade para invocar
        $chart->size = 6;
        $tabs->charts[] = $chart;


        $ano = range(2010, 2025);
        array_unshift($ano, "");

        // cria novo campo
        $field = new Fields();
        $field->name = "pesq_ano";
        $field->description = "Ano";
        $field->type = $field::SELECT;
        $field->options = Options::byArray($ano);
        $field->size = 3;
        $tabs->form->field[] = $field;

//
//        // cria novo campo
//        $field = new Fields();
//        $field->name = "pesq_turma";
//        $field->description = "Turma";
//        $field->type = $field::TEXT;
//        $field->size = 3;
//        $tabs->form->field[] = $field;

        $segmento_nome = array("", "Infantil/Pré-escolar", "Fundamental I", "Fundamental II", "Ensino médio", "Ensino superior", "Técnico", "Creche", "Outros");
        $segmento_codigo = array("", 1, 2, 3, 4, 5, 6, 8, 7);

        // cria novo campo
        $field = new Fields();
        $field->name = "pesq_segmento";
        $field->description = "Segmento";
        $field->type = $field::SELECT;
        $field->options = Options::byArray($segmento_codigo, $segmento_nome);
        $field->size = 3;
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 12);
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "window.location.href = '"._pasta."'";
        $widget->body->tabs["Retornar"] = $tabs; // colocar o nome da tab

        $widget->setDefaults();

        Tools::render($widget);
    }
}