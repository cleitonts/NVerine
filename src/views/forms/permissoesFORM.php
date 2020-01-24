<?php


namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Header;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\services\UAC\PermissoesGUI;
use src\services\UAC\UACGUI;
use src\views\ControladoraFORM;

class permissoesFORM implements ControladoraFORM
{

    public function createSearch()
    {
        $widget = new Widget();
        //$widget->includes[] = "src/public/js/cadastro/pessoa.js";
        $widget->header->title = "Permissões";
        $widget->header->icon = "fa fa-key";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        //$tabs->form->action = "page.php?pagina=pessoa";

        //cria tabelas
        $tabs->table->name = "permissoes";
        $tabs->table->target = "?pagina=permissoes";
        $tabs->table->entity = UACGUI::class;               // passar a classe/entidade para invocar

        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            return Tools::returnError("Metodo não implementado.");
        }
        else {
            // instancia a entidade
            $gui = new PermissoesGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        if(empty($gui)){
            return Tools::returnError("Registro não encontrado.", "produto");
        }

        $widget = new Widget();
        $widget->includes[] = "src/public/js/administracao/permissoes.js";

        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Permissões";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "permissoes";
        $tabs->form->action = _pasta . "actions.php?pagina=permissoes";

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::HIDDEN;
        $field->value = $handle;
        $tabs->form->field[] = $field;

        $field = new Fields();
        $field->name = "Nome";
        $field->type = $field::LABEL;
        $field->value = $gui[1]["grupo"];
        $field->size = 12;
        $tabs->form->field[] = $field;

        /*instacia a tabela dinamica*/
        $table = new FormTable("permissoes");
        $table->after = "nome";
        $table->view = $table::TABLE_STATIC;
        $table->delete_block = true;

        foreach ($gui as $r) {
            if(empty($r["nome"])){
                continue;
            }

            $row = new FormTableRow();
            //$row->entity = $r;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "alcada";
            $field->value = $r["alcada"];
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Nome";
            $field->class = "larger";
            $field->value = $r["nome"];
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Compartilhado";
            $field->value = $r["compartilhado"];
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::CHECKBOX;
            $field->name = "Vizualizar";
            $field->value = ($r["nivel"] > 0);
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::CHECKBOX;
            $field->name = "Editar";
            $field->value = ($r["nivel"] > 1);
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::CHECKBOX;
            $field->description = "Excluir/Total";
            $field->name = "total";
            $field->value = ($r["nivel"] > 2);
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->size = 12;
        $tabs->form->field[] = $field;

        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=permissoes')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;
    }
}