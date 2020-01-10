<?php


namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\Options;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\SuporteDiagETT;
use src\entity\SuporteDiagGUI;
use src\views\ControladoraFORM;

class suportediagFORM implements ControladoraFORM
{

    public function createSearch()
    {        
        $widget = new Widget();
        $widget->header->title = "Diag";
        $widget->header->icon = "fa fa-desktop";
        //$widget->includes[] = "src/public/js/cadastro/agenda.js";

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
        $field->name = "Nome";
        $field->size = 6;
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 6);

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "suportediag";
        $tabs->table->target = "?pagina=suportediag";
        $tabs->table->entity = SuporteDiagGUI::class;               // passar a classe/entidade para invocar
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=suportegit')";
        $tabs->icon = "fa fa-bitbucket";
        $widget->body->tabs["GIT"] = $tabs;
        
        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=suportediag&pesq_num=0')";
        $tabs->icon = "fa fa-plus";
        $widget->body->tabs["Inserir"] = $tabs;

        $widget->setDefaults();

        return $widget;
    }

    public function createForm($handle = null)
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if (strlen($handle) <= 1) {
            // instancia a entidade
            $gui = new SuporteDiagETT();
        } else {
            // instancia a entidade
            $gui = new SuporteDiagGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        if (empty($gui)) {
            return Tools::returnError("Registro não encontrado.");
        }

        $widget = new Widget();
        //$widget->includes[] = "src/public/js/faturamento/exportacao.js?";
        $widget->header->title = "Diag";
        $widget->header->icon = "fa fa-desktop";
        $widget->entity = $gui;

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "diag";
        $tabs->form->action = _pasta . "actions.php?pagina=suportediag";

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
        $field->name = "Nome";
        $field->property = "nome";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Contrato";
        $field->property = "contrato";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Banco de dados";
        $field->property = "banco_dados";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Senha mestre";
        $field->property = "senha";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        if (strlen($handle) <= 1) {
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Segmento";
            $field->property = "segmento";
            //$field->options[] = new Options("", "Default");
            $field->options[] = new Options("educacional", "Educacional");
            $field->options[] = new Options("empresarial", "Empresarial");
            $field->size = 4;
            $tabs->form->field[] = $field;
        }
        
        //$select_turma = Fields::fromTable(Fields::SELECT, 4, "Turma", "K_TURMA", "NOME", "HANDLE", "", "cod_turma");

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        if (strlen($handle) <= 1) {
            $field->name = "instalar";
            $field->class = "btn-danger float-right mt-3";
        }
        $tabs->form->field[] = $field;
        
        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=suportediag')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula
        return $widget;
    }
}