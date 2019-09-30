<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 02/04/2019
 * Time: 10:04
 */

namespace src\views\forms;

use src\creator\widget\Body;
use src\creator\widget\Component;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\Header;
use src\creator\widget\Options;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\AgendaETT;
use src\entity\AgendaGUI;
use src\services\Transact;
use src\views\ControladoraFORM;

class agendaFORM implements ControladoraFORM
{
    public function createSearch()
    {
        // instancia a entidade
        $gui = new AgendaGUI(0);
        $gui->setPesquisa();
        $gui->fetch();
        $gui = $gui->itens[0];

        $widget = new Widget();
        $widget->header->title = "Agenda";
        $widget->header->icon = "fa fa-persons";
        $widget->includes[] = "src/public/js/cadastro/agenda.js";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        //$tabs->form->action = "page.php?pagina=pessoa";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Data Inicial";
        $field->name = "data_inicial";
        $field->class = "datepicker-date";
        $field->property = "data_inicial";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Data Final";
        $field->name = "data_final";
        $field->class = "datepicker-date";
        $field->property = "data_final";
        $field->size = 2;
        $tabs->form->field[] = $field;

        if(__EDUCACIONAL__){
            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            //$field->description = "Aluno";
            $field->name = "pessoa";
            $field->property = "pessoa";
            $field->description = "Aluno";
            $field->size = 6;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Cód";
            $field->name = "cod_pessoa";
            $field->property = "cod_pessoa";
            $field->size = 2;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Região";
            $field->name = "regiao";
            $field->property = "cod_regiao";
            $field->options[] = new Options($gui->cod_regiao, $gui->regiao);  // trocar por valor default
            $field->size = 4;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            //$field->description = "Escola";
            $field->name = "escola";
            $field->property = "cod_escola";
            $field->options[] = new Options($gui->cod_escola, $gui->escola);
            $field->size = 4;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Turma";
            $field->name = "turma";
            $field->property = "cod_turma";
            $field->options[] = new Options($gui->cod_turma, $gui->turma);
            $field->size = 4;
            $tabs->form->field[] = $field;


            /* cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Evento Público";
            $field->name = "evento_publico";
            $field->property = "evento_publico";
            $field->options[] = new Options(" ", " ");
            $field->options[] = new Options("1", "Sim");
            $field->options[] = new Options("0", "Não");
            $field->size = 3;
            $div->field[] = $field;*/

        }
        elseif(!__EDUCACIONAL__){
            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            //$field->description = "Aluno";
            $field->name = "pessoa";
            $field->property = "pessoa";
            $field->size = 6;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Cód";
            $field->name = "cod_pessoa";
            $field->property = "cod_pessoa";
            $field->size = 2;
            $tabs->form->field[] = $field;

            // cria novo campo
            $listas = AgendaETT::getResponsavel();
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Responsavel";
            $field->property = "responsavel";
            $field->options = Options::byArray($listas["handle"], $listas["nome"]);
            $field->size = 4;
            $tabs->form->field[] = $field;
        }

        Tools::footerSearch($tabs->form, 6);

        $widget->body->tabs[] = $tabs; // colocar o nome da tab


        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        //cria tabelas
        $tabs->table->name = "agenda";
        $tabs->table->target = "?pagina=agenda";
        $tabs->table->entity = AgendaGUI::class;               // passar a classe/entidade para invocar

        $widget->body->tabs["Lista"] = $tabs;

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        $div = new Component();
        $div->setAgenda();
        $tabs->children[] = $div;

        $widget->body->tabs["Calendario"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=agenda&pesq_num=0')";
        $tabs->icon = "fa fa-plus";
        $widget->body->tabs["Inserir"] = $tabs;

        $widget->setDefaults();


        return $widget;
    }

    public function createForm($handle = null)
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            // instancia a entidade
            $gui = new AgendaETT();
        }
        else {
            // instancia a entidade
            $gui = new AgendaGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        $widget = new Widget();

        $widget->includes[] = "src/public/js/cadastro/agenda.js";

        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Agenda Educacional #{$gui->handle}";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "agenda";
        $tabs->form->action = _pasta . "actions.php?pagina=agenda";

        // carrega calendario
        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "col-md-9");

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::HIDDEN;
        $field->property = "handle";
        $div->field[] = $field;

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::LABEL;
        $field->property = "handle";
        $field->size = 3;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Titulo do Evento";
        $field->name = "titulo_evento";
        $field->property = "titulo";
        $field->size = 9;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Ativo";
        $field->name = "evento_ativo";
        $field->property = "evento_ativo";
        $field->options[] = new Options("S", "Sim");
        $field->options[] = new Options("N", "Não");
        $field->size = 3;
        $div->field[] = $field;

        if(__EDUCACIONAL__){
            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Aluno";
            $field->name = "pessoa";
            $field->property = "pessoa";
            $field->size = 6;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Cód";
            $field->name = "cod_pessoa";
            $field->property = "cod_pessoa";
            $field->size = 3;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Região";
            $field->name = "regiao";
            $field->property = "cod_regiao";
            $field->options[] = new Options($gui->cod_regiao, $gui->regiao);  // trocar por valor default
            $field->size = 4;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            //$field->description = "Escola";
            $field->name = "escola";
            $field->property = "cod_escola";
            $field->options[] = new Options($gui->cod_escola, $gui->escola);
            $field->size = 4;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Turma";
            $field->name = "turma";
            $field->property = "cod_turma";
            $field->options[] = new Options($gui->cod_turma, $gui->turma);
            $field->size = 4;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::AREA;
            $field->description = "Notas";
            $field->name = "editor_conteudo";
            $field->property = "conteudo";
            $field->size = 12;
            $div->field[] = $field;

            /* cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Evento Público";
            $field->name = "evento_publico";
            $field->property = "evento_publico";
            $field->options[] = new Options(" ", " ");
            $field->options[] = new Options("1", "Sim");
            $field->options[] = new Options("0", "Não");
            $field->size = 3;
            $div->field[] = $field;*/

        }elseif(!__EDUCACIONAL__){
            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            //$field->description = "Aluno";
            $field->name = "pessoa";
            $field->property = "pessoa";
            $field->size = 6;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Cód";
            $field->name = "cod_pessoa";
            $field->property = "cod_pessoa";
            $field->size = 3;
            $div->field[] = $field;

            // cria novo campo
            $listas = AgendaETT::getResponsavel();
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Responsavel";
            $field->property = "responsavel";
            $field->options = Options::byArray($listas["handle"], $listas["nome"]);
            $field->size = 5;
            $div->field[] = $field;

            // cria novo campo
            $listas = AgendaETT::getCRMStatus();
            $field = new Fields();
            $field->type = $field::DISABLED_SELECT;
            $field->name = "Status";
            $field->property = "status";
            $field->options = Options::byArray($listas["handle"], $listas["nome"]);
            $field->size = 3;
            $div->field[] = $field;

            // cria novo campo
            $eventos = AgendaETT::getTipoAssunto();
            $field = new Fields();
            $field->type = $field::DISABLED_SELECT;
            $field->name = "Tipo evento";
            $field->property = "tipo_evento";
            $field->options = Options::byArray(array_keys($eventos), array_values($eventos));
            $field->size = 4;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::AREA;
            $field->description = "Assunto";
            $field->name = "editor_conteudo";
            $field->property = "conteudo";
            $field->size = 12;
            $div->field[] = $field;
        }

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $div->field[] = $field;

        $tabs->form->children[] = $div;

        // carrega calendario
        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "col-md-3");

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Data Inicial";
        $field->name = "data_inicial";
        $field->class = "datepicker-date";
        $field->property = "data_inicial";
        $field->size = 6;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Hora Inicial";
        $field->name = "hora_inicial";
        $field->class = "datepicker-time";
        $field->property = "hora_inicial";
        $field->size = 6;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Data Final";
        $field->name = "data_final";
        $field->class = "datepicker-date";
        $field->property = "data_final";
        $field->size = 6;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Hora Final";
        $field->name = "hora_final";
        $field->class = "datepicker-time";
        $field->property = "hora_final";
        $field->size = 6;
        $div->field[] = $field;

        if(__EDUCACIONAL__){
            $eventos = AgendaETT::getNomeEvento(0, true);

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Tipo evento";
            $field->property = "tipo_evento";
            $field->options = Options::byArray(array_keys($eventos), array_values($eventos));
            $field->size = 12;
            $div->field[] = $field;
        }

        // só existe se for cadastro
        if($handle == 0) {
            // cria novo campo
            $field = new Fields();
            $field->type = $field::CHECKBOX;
            $field->name = "Recorrente";
            //$field->property = "recorrente";
            $field->options[] = new Options("1", "Domingo");
            $field->options[] = new Options("2", "Segunda");
            $field->options[] = new Options("3", "Terça");
            $field->options[] = new Options("4", "Quarta");
            $field->options[] = new Options("5", "Quinta");
            $field->options[] = new Options("6", "Sexta");
            $field->options[] = new Options("6", "Sábado");
            $field->size = 12;
            $div->field[] = $field;
        }

        $tabs->form->children[] = $div;

        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=agenda')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;

    }


}