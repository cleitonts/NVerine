<?php
/**
* Created by PhpStorm.
* User: Cleiton
* Date: 09/10/2019
* Time: 12:06
*/
namespace src\views\forms;

use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\EducacionalAvaliacaoETT;
use src\entity\EducacionalAvaliacaoGUI;
use src\entity\EducacionalBoletimGUI;
use src\entity\EducacionalTurmaListaGUI;
use src\views\ControladoraFORM;


class educacionalavaliacaoFORM implements ControladoraFORM
{

    public function createSearch()
    {
        $widget = new Widget();
        //$widget->includes[] = "src/public/js/faturamento/exportacao.js?";
        $widget->header->title = "Avaliações";
        $widget->header->icon = "fa fa-book";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        $tabs->form->action = "?pagina=educacionalavaliacao";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Data inicial";
        $field->class = "datepicker-date";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Data final";
        $field->class = "datepicker-date";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        // $tabs->form->field[] = Fields::fromTable(Fields::SELECT, 4, "Professor", "K_FN_PESSOA", "NOME", "HANDLE", "WHERE PROFESSOR = 'S'");

        // cria novo campo
        $tabs->form->field[] = Fields::fromTable(Fields::SELECT, 3, "Disciplina", "K_DISCIPLINA", "NOME", "HANDLE");

        // cria novo campo
        $tabs->form->field[] = Fields::fromTable(Fields::SELECT, 3, "Turma", "K_TURMA", "NOME", "HANDLE");

        Tools::footerSearch($tabs->form, 8);

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "educacionalturma";
        $tabs->table->target = "?pagina=educacionalavaliacao";
        $tabs->table->entity = EducacionalAvaliacaoGUI::class;               // passar a classe/entidade para invocar
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        //        $tab = new Tabs();
        //        $tab->function = "destinoMenu('educacional_turma_relatorios&retorno=".urlencode(getUrlRetorno("index2.php"))."')";
        //        $tab->icon = "fa fa-bar-chart";

        //$widget->body->tabs["Relatórios"] = $tab;

        $tab = new Tabs();
        $tab->function = "Tools.redirect('?pagina=educacionalavaliacao&pesq_num=0')";
        $tab->icon = "fa fa-plus";

        $widget->body->tabs["inserir"] = $tab;

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if ($handle == 0) {
            // instancia a entidade
            $gui = new EducacionalAvaliacaoETT();
        } else {
            // instancia a entidade
            $gui = new EducacionalAvaliacaoGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        if (empty($gui)) {
            return Tools::returnError("Registro não encontrado.");
        }

        $widget = new Widget();
        //$widget->includes[] = "src/public/js/faturamento/exportacao.js?";
        $widget->header->title = "Avaliações";
        $widget->header->icon = "fa fa-graduation-cap";
        $widget->entity = $gui;

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "avaliavao";
        $tabs->form->action = _pasta . "actions.php?pagina=educacionalavaliacao";

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::HIDDEN;
        $field->property = "handle";
        $tabs->form->field[] = $field;

        $field = new Fields();
        $field->name = "Cód.";
        $field->type = $field::LABEL;
        $field->property = "handle";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Nome";
        $field->name = "titulo_evento";
        $field->property = "nome";
        $field->size = 5;
        $tabs->form->field[] = $field;

        $select_turma = Fields::fromTable(Fields::SELECT, 4, "Turma", "K_TURMA", "NOME", "HANDLE", "", "cod_turma");
        $select_disciplina = Fields::fromTable(Fields::SELECT, 4, "Disciplina", "K_DISCIPLINA", "NOME", "HANDLE", "", "cod_disciplina");

        if ($handle > 0) {
            $select_disciplina->type = Fields::DISABLED_SELECT;
            $select_turma->type = Fields::DISABLED_SELECT;
        }

        $tabs->form->field[] = $select_turma;
        $tabs->form->field[] = $select_disciplina;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Data";
        $field->class = "datepicker-date";
        $field->property = "data";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Peso";
        $field->property = "peso";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::AREA;
        $field->description = "Descrição";
        $field->name = "descricao";
        $field->property = "descricao";
        $field->size = 6;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::AREA;
        $field->description = "Conteudo";
        $field->name = "conteudo";
        $field->property = "conteudo";
        $field->size = 6;
        $tabs->form->field[] = $field;

        // se for cadastro interrompe aqui
        if ($handle == 0) {
            $field = new Fields();
            $field->type = $field::SUBMIT;
            $field->name = "enviar";
            $field->class = "float-right mt-3";
            $tabs->form->field[] = $field;

            $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

            $tabs = new Tabs();
            $tabs->icon = "fa fa-undo";
            $tabs->function = "Tools.retornar('pagina=educacionalavaliacao')";
            $widget->body->tabs["Retornar"] = $tabs;

            $widget->setDefaults();                 // pega todos os valores das entidades e popula
            return $widget;
        }

        $widget->includes[] = "src/public/js/educacional/avaliacao.js";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->name = "alunosanchor";
        $field->size = 6;
        $tabs->form->field[] = $field;

        $alunos = new EducacionalTurmaListaGUI();
        $alunos->pesquisa["pesq_lista"] = $gui->cod_turma;
        $alunos->fetch();

        $notas = new EducacionalBoletimGUI();
        $notas->pesquisa["pesq_avaliacao"] = $handle;
        $notas->fetch();

        dumper($notas);

        // instancia a tabela dinamica
        $table = new FormTable("grade_horaria");
        $table->after = "alunosanchor";
        $table->name = "alunos";
        $table->view = $table::TABLE_STATIC;

        // de acordo com a ordenalçao é possível cruzar os dados desta maneira
        $i = 0;

        // itera cada um dos itens
        foreach ($alunos->itens as $r) {
            $row = new FormTableRow();
            //$row->entity = $r;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->value = $notas->itens[$i]->handle;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "cod_aluno";
            $field->value = $r->cod_aluno;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Número";
            $field->value = $r->numero;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Aluno";
            $field->value = $r->aluno;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            if (empty($notas->itens[$i]->handle)) {
                $field->type = $field::TEXT;
            }
            $field->name = "Nota";
            $field->value = $notas->itens[$i]->nota;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            if (empty($notas->itens[$i]->handle)) {
                $field->type = $field::LABEL;
            }
            $field->name = "Nota revisão";
            $field->value = $notas->itens[$i]->nota_revisao;
            $row->field[] = $field;

            $table->rows[] = $row;
            $i++;
        }

        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=educacionalavaliacao')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula
        return $widget;
    }
}