<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/02/2019
 * Time: 21:59
 */

namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Header;
use src\creator\widget\Options;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\EstoqueETT;
use src\entity\ExpedicaoETT;
use src\entity\ExpedicaoGUI;
use src\views\ControladoraFORM;

class expedicaoFORM implements ControladoraFORM
{

    /**
     * @return Widget
     */
    public function createSearch() : Widget
    {
        $widget = new Widget();
        $widget->includes[] = "src/public/js/faturamento/exportacao.js?bosta";
        $widget->header->title = "Últimos pedidos";
        $widget->header->icon = "fa fa-truck";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        $tabs->form->action = "?pagina=expedicao";

        //cria tabelas
        $tabs->table->name = "expedicao";
        $tabs->table->target = "?pagina=expedicao";
        $tabs->table->entity = ExpedicaoGUI::class;               // passar a classe/entidade para invocar

        // cria novo campo
        $field = new Fields();
        $field->name = "DATA INICIAL";
        $field->type = $field::TEXT;
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->now($field::TEXT, 2, "data final");
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->name = "Tipo nota";
        $field->type = $field::SELECT;
        $field->size = 2;
        $field->options[] = new Options("E", "Compra");
        $field->options[] = new Options("S", "Venda");
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->now($field::TEXT, 3, "Nº orçamento");
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->now($field::TEXT, 3, "Nº nota fiscal");
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 6);
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $widget->setDefaults();
        return $widget;
    }

    /**
     * @return Widget
     */
    public function createForm($pesq_num = null) : Widget
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($pesq_num == null){
            // instancia a entidade
            $gui = new ExpedicaoETT();

        }
        else {
            // instancia a entidade
            $gui = new ExpedicaoGUI($pesq_num);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        // instancia somente 1x para ser usada varias
        $endereco = new EstoqueETT();
        $endereco->fetch();

        $widget = new Widget();
        $widget->includes[] = "src/public/js/faturamento/exportacao.js";
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Pedidos #{$gui->numero}";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "i icon-edit";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_expedicao";
        $tabs->form->action = _pasta . "actions.php?pagina=expedicao";

        // cria novo campo
        $field = new Fields();
        $field->now($field::HIDDEN, 0, "url_retorno", "?pagina=expedicao");
        $tabs->form->field[] = $field;

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
        $field->name = "cliente";
        $field->type = $field::LABEL;
        $field->property = "pessoa";
        $field->size = 6;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "numero_correios";
        $field->property = "cod_correios";
        $field->description = "rastreamento(Correios)";
        $field->size = 6;
        $tabs->form->field[] = $field;

        // instancia a tabela dinamica
        $table = new FormTable("produtos");
        $table->after = "numero_correios";

        $i = 0; // contador das rows
        // as tabelas sempre veem depois de todos os outros campos
        foreach ($gui->produtos as $r) {
            // cria a tabela com campos

            $row = new FormTableRow();
            $row->entity = $r;
            $row->after = "numero_correios"; // passar o nome do campo

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "prod_cod_produto";
            $field->property = "prod_cod_produto";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "prod_handle";
            $field->property = "handle";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "prod_quantidade";
            $field->property = "quantidade";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "PRODUTO";
            $field->property = "produto";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "prod_data_expedicao";
            $field->description = "data expedição";
            $field->property = "data_expedicao";
            $field->value = converteDataSQL($r->data_expedicao);
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Und.";
            $field->property = "unidade";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Qtd.";
            $field->property = "quantidade";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "entrega_qtd_entregue";
            $field->description = "Qtd. entregue";
            $field->property = "quantidade_entregue";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "entrega_qtd_baixada";
            $field->description = "Qtd. baixada";
            $field->property = "quantidade_baixada";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "entrega_qtd_saldo";
            $field->description = "Qtd. saldo";
            $field->property = "quantidade_saldo";
            $row->field[] = $field;

            $field = $endereco->toSelect("entrega_endereco");
            $field->property = "cod_endereco";
            $field->description = "Endereço";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "Qtd. disponível";
            $field->value = "--";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "entrega_lote";
            $field->description = "Lote";
            $field->property = "lote";
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
        $tabs->icon = "i icon-undo";
        $tabs->function = "retorno()";

        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;
    }
}