<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/06/2019
 * Time: 14:54
 */

namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Options;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\EducacionalTurmaETT;
use src\entity\EducacionalTurmaGUI;
use src\entity\EducacionalTurmaListaETT;
use src\views\ControladoraFORM;

class educacionaldiarioFORM implements ControladoraFORM
{
    public function createSearch()
    {
        global $permissoes;

        $widget = new Widget();
        //$widget->includes[] = "src/public/js/faturamento/exportacao.js?";
        $widget->header->title = "Diário de classe";
        $widget->header->icon = "fa fa-book";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        $tabs->form->action = "?pagina=educacionalturma";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "Vigente";
        $field->options[] = new Options("1", "Sim");
        $field->options[] = new Options("2", "Não");
        $field->size = 4;
        //$tabs->form->field[] = $field;

        $field = Fields::fromTable(Fields::SELECT, 6, "Professor", "K_FN_PESSOA", "NOME", "HANDLE", "WHERE PROFESSOR = 'S'");

        if($permissoes->grupo == "Professor"){
            $field->type = Fields::DISABLED_SELECT;
            $field->value = $_SESSION["PESSOA"];
        }

        Tools::footerSearch($tabs->form, 6);

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "educacionalturma";
        $tabs->table->target = "?pagina=educacionaldiario";
        $tabs->table->entity = EducacionalTurmaGUI::class;               // passar a classe/entidade para invocar
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tab = new Tabs();
        $tab->function = "destinoMenu('educacional_presenca&retorno=".urlencode(getUrlRetorno("index2.php"))."')";
        $tab->icon = "fa fa-bar-chart";
        $widget->body->tabs["Relatórios"] = $tab;

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        global $permissoes;

        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            if(!__NO_ERROR_VIEW__){
                return Tools::returnError("Turma não selecionada.", "educacionaldiario");
            }
            $turma = new EducacionalTurmaETT();
            $template = new EducacionalTurmaListaETT();
            array_unshift($turma->turma_aluno, $template);
        }
        else {
            // instancia a entidade
            $turma = new EducacionalTurmaGUI($handle);
            $turma->setPesquisa();
            $turma->fetch();
            $turma = $turma->itens[0];
            if(empty($turma->turma_aluno)){
                return Tools::returnError("Turma não possui alunos matriculados.", "educacionaldiario");
            }
        }

        $widget = new Widget();
        $widget->includes[] = "src/public/js/educacional/diario.js?";
        $widget->header->title = "Diário de classe";
        $widget->entity = $turma;
        $widget->header->icon = "fa fa-book";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();

        // cria form
        $tabs->form->method = "POST";
        $tabs->form->name = "form_diario";
        $tabs->form->action = _pasta . "actions.php?pagina=educacionaldiario";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Data Diário";
        $field->class = "datepicker-date";
        $field->size = 6;
        $tabs->form->field[] = $field;
        $field = Fields::fromTable(Fields::SELECT, 6, "Professor", "K_FN_PESSOA", "NOME", "HANDLE", "WHERE PROFESSOR = 'S'");

        if($permissoes->grupo == "Professor"){
            $field->type = Fields::DISABLED_SELECT;
            $field->value = $_SESSION["PESSOA"];
        }

        $widget->body->tabs[] = $tabs;

        // Presença
        // =============================================================================================================
        $tabs = new Tabs();
        $tabs->icon = "fa fa-hand-paper-o";

        // cria form
        $tabs->form->method = "POST";
        $tabs->form->name = "form_diario";
        $tabs->form->action = _pasta . "actions.php?pagina=educacionaldiario";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->name = "handle_turma";
        $field->property = "handle";
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->name = "anchorpresenca";
        $tabs->form->field[] = $field;

        // instancia a tabela dinamica
        $table = new FormTable("turma_aluno");
        $table->after = "anchorpresenca";
        $table->view = $table::TABLE_STATIC;
        $table->delete_block = true;

        foreach ($turma->turma_aluno as $key => $r) {
            $row = new FormTableRow();
            $row->entity = $r;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->property = "handle";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Número";
            $field->property = "numero";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Aluno";
            $field->property = "aluno";
            $field->class = "larger";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "cod_aluno";
            $field->property = "cod_aluno";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "cod_horario";
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "Observações";
            $field->class = "larger";
            $row->field[] = $field;

            $table->rows[] = $row;
        }
        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Presença"] = $tabs;

        // conteudo
        // =============================================================================================================
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form->method = "POST";
        $tabs->form->name = "form_diario";
        $tabs->form->action = _pasta . "actions.php?pagina=educacionaldiario";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->name = "anchorconteudo";
        $tabs->form->field[] = $field;

        // instancia a tabela dinamica
        $table = new FormTable("conteudo");
        $table->after = "anchorconteudo";
        $table->view = $table::LIST_DYNAMIC;
        $table->delete_block = true;

        $row = new FormTableRow();
        $row->entity = $turma;

        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->name = "handle";
        $row->field[] = $field;

        $field = new Fields();
        $field->type = $field::LABEL;
        $field->name = "Horário";
        $field->size = 3;
        $row->field[] = $field;

        $field = new Fields();
        $field->type = $field::LABEL;
        $field->name = "Disciplina";
        $field->size = 3;
        $row->field[] = $field;

        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->name = "cod_professor";
        $row->field[] = $field;

        $field = new Fields();
        $field->type = $field::LABEL;
        $field->name = "Professor";
        $field->size = 6;
        $row->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::AREA;
        $field->name = "Conteúdo";
        $field->size = 12;
        $row->field[] = $field;

        $table->rows[] = $row;
        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Conteúdo"] = $tabs;

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar()";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;

    }
}