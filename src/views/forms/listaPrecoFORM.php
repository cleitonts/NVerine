<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 11/02/2019
 * Time: 13:23
 */

namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Header;
use src\creator\widget\Options;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Widget;
use src\entity\ListaPrecoETT;
use src\entity\ListaPrecoGUI;
use src\entity\ListaPrecoItemETT;
use src\views\ControladoraFORM;

class listaPrecoFORM implements ControladoraFORM
{

    public function createForm($handle = 0): Widget
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            // instancia a entidade
            $gui = new ListaPrecoETT();
        }
        else {
            // instancia a entidade
            $gui = new ListaPrecoGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        $template = new ListaPrecoItemETT();
        array_unshift($gui->produtos, $template);

        $widget = new Widget();
        $widget->includes[] = "src/public/js/faturamento/listapreco.js";
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Lista #{$gui->indice}";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_listaPreco";
        $tabs->form->action = _pasta . "actions.php?pagina=listaPreco";

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::HIDDEN;
        $field->property = "handle";
        $tabs->form->field[] = $field;

        $field = new Fields();
        $field->name = "tipo";
        $field->type = $field::HIDDEN;
        $field->property = "cod_tipo";
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->name = "índice";
        $field->type = $field::LABEL;
        $field->property = "indice";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "nome da lista";
        $field->property = "nome";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Data inicio";
        $field->property = "data_inicio";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "data final";
        $field->property = "data_fim";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "global";
        $field->property = "ativo";
        $field->options[] = new Options("S", "SIM");
        $field->options[] = new Options("N", "NÃO");
        $field->size = 1;
        $tabs->form->field[] = $field;

        // instancia a tabela dinamica
        $table = new FormTable("produtos");
        $table->after = "global";
        $table->view = $table::TABLE_DYNAMIC;

        foreach ($gui->produtos as $r) {
            $row = new FormTableRow();
            $row->entity = $r;

            //$row->after = "numero_correios"; // passar o nome do campo

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "prod_cod";
            $field->property = "produto";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->property = "handle";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "nome";
            $field->class = "larga";
            $field->property = "prod_nome";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "cód.";
            $field->property = "produto";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Valor original";
            $field->property = "valor_bruto";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "% desconto";
            $field->property = "perc_desconto";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "valor promoção";
            $field->property = "valor";
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar()";

        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;
    }

    public function createSearch(): Widget
    {
        $widget = new Widget();
        //$widget->includes
        $widget->header = new Header();
        $widget->header->title = "Lista de Preço";
        $widget->header->icon = "fa fa-dollar";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "expedicao";
        $tabs->table->target = "?pagina=listapreco";
        $tabs->table->entity = ListaPrecoGUI::class;               // passar a classe/entidade para invocar

        $widget->body->tabs["PESQUISAR"] = $tabs; // colocar o nome da tab
        $tab = new Tabs();
        $tab->function = "Tools.redirect('?pagina=listapreco&pesq_num=0')";
        $tab->icon = "fa fa-plus";

        $widget->body->tabs["inSeRir"] = $tab;

        return $widget;
    }
}