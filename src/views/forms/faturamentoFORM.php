<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 30/05/2019
 * Time: 08:51
 */


namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Component;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\HeaderMenuItem;
use src\creator\widget\Options;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\EstoqueETT;
use src\entity\FaturamentoDuplicataETT;
use src\entity\FaturamentoETT;
use src\entity\FaturamentoGUI;
use src\entity\FaturamentoProdutoServicoETT;
use src\entity\FaturamentoStatusETT;
use src\entity\FaturamentoStatusTransicaoETT;
use src\entity\PessoaEnderecoETT;
use src\entity\ProdutoETT;
use src\entity\UsuarioGUI;
use src\services\Transact;
use src\views\ControladoraFORM;

class faturamentoFORM implements ControladoraFORM
{

    public function createSearch()
    {
        if(empty($_REQUEST["pesq_tipo"])){
            return Tools::returnError("Tipo de nota não informado.");
        }

        $widget = new Widget();
        $widget->includes[] = "src/public/js/faturamento/faturamento.js";
        $widget->header->title = "Faturamento";
        $widget->header->icon = "fa fa-dolar";

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Processar nota";
        $menu->function = "processa()";
        $menu->icon = "fa fa-gears";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Imprimir orçamento";
        $menu->function = "relatorioItem()";
        $menu->icon = "fa fa-print";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Imprimir/Visualizar DANFE";
        $menu->function = "abreDANFE()";
        $menu->icon = "fa fa-file-o";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Duplicar nota";
        $menu->function = "duplica()";
        $menu->icon = "fa fa-copy";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Devolver nota";
        $menu->function = "devolucao()";
        $menu->icon = "fa fa-undo";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Cancelar pedido";
        $menu->function = "getModalCancelamento()";
        $menu->icon = "fa fa-ban";
        $widget->header->menu[] = $menu;

        // submenu interno
//        $menu = new HeaderMenuItem();
//        $menu->description = "Anexos";
//        $menu->function = "anexos()";
//        $menu->icon = "fa fa-paperclip";
//        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Consultar lote NF-e";
        $menu->function = "consultaLote()";
        $menu->icon = "fa fa-question";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Exportar XML NF-e";
        $menu->function = "exportaXML()";
        $menu->icon = "fa fa-save";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Relatório de comissões";
        $menu->function = "destinoMenu('faturamento_comissao_relatorio')";
        $menu->icon = "fa fa-file-o";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Relatório simplificado";
        $menu->function = "destinoMenu('faturamento_simples_relatorio&pesq_tipo=S')";
        $menu->icon = "fa fa-bar-chart";
        $widget->header->menu[] = $menu;

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        //$tabs->form->action = "?pagina=educacionalturma";

        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "col-md-2");

        $status = FaturamentoStatusETT::getStatus();
        // cria novo campo
        $field = new Fields();
        $field->type = $field::CHECKBOX;
        $field->name = "pesq_status";
        $field->description = "Status";
        $field->options = Options::byArray($status["handle"], $status["nome"]);
        $field->size = 12;
        $div->field[] = $field;

        $tabs->form->children[] = $div;

        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "col-md-2");

        $notas = array("Não emitida", "Aprovada", "Cancelada", "Denegada");
        // cria novo campo
        $field = new Fields();
        $field->type = $field::CHECKBOX;
        $field->description = "Nota fiscal";
        $field->name = "pesq_nota_discal";
        $field->options = Options::byArray(array_keys($notas), array_values($notas));
        $field->size = 12;
        $div->field[] = $field;

        $periodo = array("Orçamento", "Nota Fiscal", "Entrega");
        // cria novo campo
        $field = new Fields();
        $field->type = $field::RADIO;
        $field->class = "mt-3";
        $field->name = "pesq_periodo";
        $field->description = "Período de data";
        $field->options = Options::byArray(array_keys($periodo), array_values($periodo));
        $field->value = 0;
        $field->size = 12;
        $div->field[] = $field;

        $tabs->form->children[] = $div;


        // carrega calendario
        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "col-md-8");

        // cria novo campo
        $field = new Fields();
        $field->name = "pesq_tipo";
        $field->type = $field::HIDDEN;
        $field->value = $_REQUEST["pesq_tipo"];
        $div->field[] = $field;

        // cria novo campo
        $origem = FaturamentoETT::getNomeOrigem("", true);
        array_unshift($origem, "");
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "pesq_origem";
        $field->description = "Origem";
        $field->options = Options::byArray(array_keys($origem), array_values($origem));
        $field->size = 4;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Data inicial";
        $field->name = "pesq_data_inicial";
        $field->type = $field::TEXT;
        $field->size = 4;
        $field->class = "datepicker-date";
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Data inicial";
        $field->name = "pesq_data_final";
        $field->type = $field::TEXT;
        $field->size = 4;
        $field->class = "datepicker-date";
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Cliente/fornecedor";
        $field->name = "campo_pesq_pessoa";
        $field->type = $field::TEXT;
        $field->size = 6;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Cód.";
        $field->name = "pesq_cod_pessoa";
        $field->type = $field::TEXT;
        $field->size = 2;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Nº orçamento";
        $field->name = "pesq_codigo";
        $field->type = $field::TEXT;
        $field->size = 4;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Nº nota fiscal";
        $field->name = "pesq_num_nota";
        $field->type = $field::TEXT;
        $field->size = 6;
        $div->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Doc. fornecedor";
        $field->name = "pesq_doc_fornecedor";
        $field->type = $field::TEXT;
        $field->size = 6;
        $div->field[] = $field;

        $tabs->form->children[] = $div;

        Tools::footerSearch($tabs->form, 6);

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "faturamento";
        $tabs->table->target = "?pagina=faturamento";
        $tabs->table->check = true;
        $tabs->table->entity = FaturamentoGUI::class;               // passar a classe/entidade para invocar
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tab = new Tabs();
        $tab->function = "destinoMenu('faturamento_notas_relatorios&pesq_tipo={$_REQUEST["pesq_tipo"]}&retorno=".urlencode(Transact::getUrlRetorno("index.php"))."')";
        $tab->icon = "fa fa-bar-chart";

        $widget->body->tabs["Relatórios"] = $tab;

        $tab = new Tabs();
        $tab->function = "Tools.redirect('?pagina=faturamento&pesq_num=0&pesq_tipo={$_REQUEST['pesq_tipo']}')";
        $tab->icon = "fa fa-plus";

        $widget->body->tabs["inserir"] = $tab;

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        global $__MODULO__;
        global $__PAGINA__;

        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if ($handle == 0) {
            // instancia a entidade
            $gui = new FaturamentoETT();
            $gui->status->handle = 1;
            $gui->cod_tipo = $_REQUEST["pesq_tipo"];

            if(empty($gui->cod_tipo)){
                $gui->cod_tipo = $_REQUEST["tipo"];
            }

            if(empty($_REQUEST["pesq_tipo"]) && empty($_REQUEST["tipo"])) {
                return Tools::returnError("Tipo de nota não informado.", "faturamento");
            }
        }
        else {
            // instancia a entidade
            $gui = new FaturamentoGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];

            // atribui valor paranão ficar com erro
            $_REQUEST["pesq_tipo"] = $gui->cod_tipo;

            if(empty($gui)) {
                return Tools::returnError("Nota/orçamento não encontrado.", "faturamento");
            }
        }

        // opções de plano de contas
        $contas = new ContaGUI();

        // seta módulo e nome da página
        if($gui->cod_tipo == "S") {
            $__MODULO__ = $gui->finalidade == FaturamentoETT::FINALIDADE_DEVOLUCAO ? "Compras" : "Faturamento";
            $__PAGINA__ = $gui->finalidade == FaturamentoETT::FINALIDADE_DEVOLUCAO ? "Nota de compra" : "Nota de venda";
            $contas->pesquisa["pesq_contas_a_receber"] = 1;
        }
        elseif($gui->cod_tipo == "E") {
            $__MODULO__ = $gui->finalidade == FaturamentoETT::FINALIDADE_DEVOLUCAO ? "Faturamento" : "Compras";
            $__PAGINA__ = $gui->finalidade == FaturamentoETT::FINALIDADE_DEVOLUCAO ? "Nota de venda" : "Nota de compra";
            $contas->pesquisa["pesq_contas_a_pagar"] = 1;
        }
        else {
            return Tools::returnError("Tipo de nota não informado.", "faturamento");
        }

        // define nomes dinâmicos
        $nomes_entrada = array(
            "nome" => "Nota de entrada",
            "operação" => "Compra",
            "natureza" => "Devolução de venda",
            "pessoa" => "Fornecedor",
            "salesman" => "Comprador",
            "tipo_transicao" => "ENTRADA"
        );

        $nomes_saida = array(
            "nome" => "Nota de saída",
            "operação" => "Venda",
            "natureza" => "Venda",
            "pessoa" => "Cliente",
            "salesman" => "Vendedor",
            "tipo_transicao" => "SAIDA"
        );

        $nomes = array("E" => $nomes_entrada, "S" => $nomes_saida);

        $widget = new Widget();
        $widget->includes[] = "src/public/js/faturamento/faturamento_single.js";

        $widget->entity = $gui;
        $widget->header->title = "Nota # {$gui->handle}";

        // cria body e tabs
        $widget->body = new Body();


        // Totais
        if (1 == 1) {
            $tabs = new Tabs();

            $card = new Component();
            $card->tag = "div";
            $card->attr = array("class" => "card", "id" => "card_total");

            $header = new Component();
            $header->tag = "div";
            $header->attr = array("class" => "card-header row card-header-info card-header-success");

            $title = new Component();
            $title->tag = "h4";
            $title->attr = array("class" => "card-title");
            $title->text = "Totais";

            $div2 = new Component();
            $div2->tag = "div";
            $div2->attr = array("class" => "card-body");

            $div = new Component();
            $div->tag = "div";
            //$div->attr = array("class" => "row");

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "faturamento";
            $tabs->form->action = _pasta . "actions.php?pagina=faturamento";

            // cria novo campo
            $field = new Fields();
            $field->name = "anchortotal";
            //$field->description = "Endereço de entrega";
            $field->type = $field::HIDDEN;
            $field->size = 12;
            $div->field[] = $field;

            // tabela
            if (1 == 1) {
                // instancia a lista endereço de entrega
                $table = new FormTable("totais");
                $table->after = "anchortotal";
                $table->view = FormTable::TABLE_STATIC;

                $row = new FormTableRow();
                $row->entity = $gui;

                // cria novo campo
                $field = new Fields();
                $field->description = "Produtos/serviços";
                $field->name = "produtos";
                $field->type = $field::TEXT;
                $field->class = "btn btn-success border-0";
                $field->property = "total_produtos->valor_bruto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Descontos";
                $field->property = "total_produtos->valor_desconto";
                $field->class = "btn btn-warning border-0";
                $field->type = $field::TEXT;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "IPI";
                $field->type = $field::TEXT;
                $field->property = "total_produtos->valor_ipi";
                $field->class = "btn btn-success border-0";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "ICMS ST	";
                $field->type = $field::TEXT;
                $field->class = "btn btn-success border-0";
                $field->property = "total_produtos->valor_icms_st";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Total";
                $field->type = $field::TEXT;
                $field->class = "btn btn-success border-0";
                $field->property = "total_produtos->valor_total";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Duplicatas";
                $field->type = $field::TEXT;
                $field->property = "total_duplicatas->valor_total";
                $field->class = "btn btn-info border-0";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "ICMS";
                $field->type = $field::TEXT;
                $field->property = "total_produtos->valor_icms";
                $field->class = "btn btn-warning border-0";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Frete";
                $field->type = $field::TEXT;
                $field->property = "total_produtos->valor_frete";
                $field->class = "btn btn-warning border-0";
                $row->field[] = $field;

                $table->rows[] = $row;
                $tabs->form->table[] = $table;
            }

            $header->children[] = $title;
            $card->children[] = $header;
            $div2->children[] = $div;
            $card->children[] = $div2;
            $tabs->form->children[] = $card;
            $widget->body->tabs[] = $tabs;
        }

        // Cabeçalho
        if (1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-edit";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "faturamento";
            $tabs->form->action = _pasta . "actions.php?pagina=faturamento";

            if($handle > 0){
                // cria novo campo
                $field = new Fields();
                $field->type = $field::HIDDEN;
                $field->name = "nota";
                $field->property = "nota";
                $tabs->form->field[] = $field;
            }
            // cria novo campo
            $field = new Fields();
            $field->type = $field::DISABLED_SELECT;
            $field->name = "tipo";
            $field->property = "cod_tipo";
            $field->options[] = new Options("E", "Entrada");
            $field->options[] = new Options("S", "Saída");
            $field->size = 2;
            $tabs->form->field[] = $field;

            $field = new Fields();
            $field->name = "Modelo";
            $field->type = $field::SELECT;
            $field->options[] = new Options(55, "NF-e");
            $field->options[] = new Options(65, "NF Consumidor");
            $field->size = 2;
            //$field->property = "cod_modelo";
            $tabs->form->field[] = $field;

            $finalidade = FaturamentoETT::getFinalidade(0, true);

            // cria novo campo
            $field = new Fields();
            $field->name = "Finalidade";
            $field->type = $field::SELECT;
            $field->options = Options::byArray(array_keys($finalidade), array_values($finalidade));
            $field->size = 2;
            $field->property = "finalidade";
            $tabs->form->field[] = $field;

            $status = FaturamentoStatusTransicaoETT::getStatus($gui->status->handle, $nomes[$gui->cod_tipo]['tipo_transicao']);
            // cria novo campo
            $field = new Fields();
            $field->name = "Status";
            $field->type = $field::SELECT;
            $field->options = Options::byArray($status["handle"], $status["nome"]);
            $field->size = 2;
            $field->property = "status->handle";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->description = "Data de emissão";
            $field->name = "data_emissao";
            $field->type = $field::LABEL;
            $field->size = 2;
            $field->value = hoje();
            $field->class = "datepicker-date";
            $field->property = "data_emissao";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Nº nota fiscal";
            $field->name = "num_nf";
            $field->value = $gui->nota_fiscal->numero;
            $field->size = 2;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->description = $nomes[$gui->cod_tipo]["pessoa"];
            $field->name = "pessoa";
            $field->type = $field::TEXT;
            $field->size = 6;
            $field->property = "pessoa";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Cód. ".$nomes[$gui->cod_tipo]["pessoa"];
            $field->name = "cod_pessoa";
            $field->property = "cod_pessoa";
            $field->size = 2;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Tabela padrão";
            $field->value = 1;
            $field->size = 2;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::DISABLED_SELECT;
            $field->description = "Destino";
            $field->name = "campo_destino";
            $field->options = Options::byArray(array(1, 2, 3), array("Estadual", "Interestadual", "Exterior"));
            $field->property = "destino";
            $field->size = 2;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Histórico da venda";
            $field->name = "descricao";
            $field->property = "descricao";
            $field->size = ($gui->cod_tipo == "S")? 12 : 8;
            $tabs->form->field[] = $field;

            $vendedores = UsuarioGUI::getVendedor();
            array_unshift($vendedores["handle"], "");
            array_unshift($vendedores["nome"], "");

            // cria novo campo
            $field = new Fields();
            $field->name = "vendedor";
            $field->description = $nomes[$gui->cod_tipo]["salesman"];
            $field->type = $field::SELECT;
            $field->options = Options::byArray($vendedores["handle"], $vendedores["nome"]);
            $field->size = ($gui->cod_tipo == "S")? 4 : 2;
            $field->property = "cod_vendedor";
            $tabs->form->field[] = $field;

            if($gui->cod_tipo == "S"){
                $contas->fetch();

                $supervisores = UsuarioGUI::getSupervisor();
                array_unshift($supervisores["handle"], "");
                array_unshift($supervisores["nome"], "");

                // cria novo campo
                $field = new Fields();
                $field->name = "supervisor";
                $field->type = $field::SELECT;
                $field->options = Options::byArray($supervisores["handle"], $supervisores["nome"]);
                $field->size = 4;
                $field->property = "supervisor";
                $tabs->form->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "plano de contas";
                $field->type = $field::SELECT;
                $field->options = Options::byArray($contas->lista_values, $contas->lista_labels["N"]);
                $field->size = 4;
                $field->property = "cod_plano_contas";
                $tabs->form->field[] = $field;
            }
            else{
                // cria novo campo
                $field = new Fields();
                $field->type = $field::TEXT;
                $field->name = "Doc. fornecedor";
                //$field->name = "doc_fornecedor";
                $field->property = "doc_fornecedor";
                $field->size = 2;
                $tabs->form->field[] = $field;
            }


            $field = new Fields();
            $field->type = $field::SUBMIT;
            $field->name = "enviar";
            $field->class = "float-right";
            $tabs->form->field[] = $field;

            $widget->body->tabs["Cabeçalho"] = $tabs; // colocar o nome da tab
        }

        // Produtos
        if (1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-gift";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "faturamento";
            $tabs->form->action = _pasta . "actions.php?pagina=faturamento";

            $div = new Component();
            $div->tag = "div";
            //$div->attr = array("class" => "col-md-6 p-0");

            // ancora para o endereco
            $field = new Fields();
            $field->name = "produtoanchor";
            $field->type = $field::HIDDEN;
            $field->size = 2;
            $div->field[] = $field;

            $tabs->form->children[] = $div;

            // adiciono uma objeto vazio no final para servir de modelo para o novo
            $modelo = new FaturamentoProdutoServicoETT($gui->handle);
            array_unshift($gui->produtos, $modelo);

            // instancia a tabela dinamica
            $table = new FormTable("produtos");
            $table->after = "produtoanchor";
            $table->delete_block = false;
            $table->view = FormTable::TABLE_DYNAMIC;

            // itera as linhas
            foreach ($gui->produtos as $key => $r) {
                $row = new FormTableRow();
                $row->entity = $r;

                // cria novo campo
                $field = new Fields();
                $field->name = "fator_bc";
                $field->type = $field::HIDDEN;
                $field->property = "fator_bc_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "usa_st";
                $field->type = $field::HIDDEN;
                $field->property = "usa_substituicao_tributaria";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "mva";
                $field->type = $field::HIDDEN;
                $field->property = "margem_valor_agregado";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "cod_produto";
                $field->type = $field::HIDDEN;
                $field->property = "cod_produto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "handle";
                $field->type = $field::HIDDEN;
                $field->property = "handle";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "tabela";
                $field->type = $field::SELECT;
                $field->options = Options::byArray(range(1, 10), range(1, 10));
                $field->property = "tabela_preco";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "produto";
                $field->type = $field::TEXT;
                $field->class = "larger";
                $field->property = "produto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Cód. produto";
                $field->type = $field::LABEL;
                $field->property = "cod_produto";
                $row->field[] = $field;

                $operacao = FaturamentoProdutoServicoETT::getTipoOperacao();
                array_unshift($operacao["handle"], "");
                array_unshift($operacao["nome"], "");

                // cria novo campo
                $field = new Fields();
                $field->description = "Operação";
                $field->name = "tipo_operacao";
                $field->type = $field::SELECT;
                $field->class = "larger";
                $field->options = Options::byArray($operacao["handle"], $operacao["nome"]);
                $field->property = "cod_tipo_operacao";
                $row->field[] = $field;

                $endereco = EstoqueETT::getEndereco();
                array_unshift($endereco["handle"], "");
                array_unshift($endereco["nome"], "");

                // cria novo campo
                $field = new Fields();
                $field->name = "Endereço";
                $field->type = $field::SELECT;
                $field->class = "larger";
                $field->options = Options::byArray($endereco["handle"], $endereco["nome"]);
                $field->property = "cod_endereco";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "lote";
                $field->type = $field::TEXT;
                $field->property = "lote";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Unidade";
                $field->type = $field::HIDDEN;
                $field->property = "unidade";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Unidade";
                $field->type = $field::LABEL;
                $field->property = "unidade";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Qtd. disponível";
                $field->type = $field::LABEL;
                //$field->property = "lote";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Quantidade";
                $field->type = $field::TEXT;
                $field->property = "quantidade";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ Unitário";
                $field->name = "valor_unitario";
                $field->type = $field::TEXT;
                $field->property = "valor_unitario";
                $row->field[] = $field;

                // isto não é o valor unitario, é o valor do produto no dia da venda
                $field = new Fields();
                $field->name = "valor_tabela";
                $field->type = $field::HIDDEN;
                $field->property = "preco_original";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "valor_bruto";
                $field->type = $field::HIDDEN;
                $field->property = "valor_bruto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ Bruto";
                $field->name = "valor_bruto";
                $field->type = $field::LABEL;
                $field->property = "valor_bruto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "% Desconto";
                $field->name = "perc_desconto";
                $field->class = "precision-5";
                $field->type = $field::LABEL;
                $field->property = "perc_desconto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "perc_desconto";
                $field->class = "precision-5";
                $field->type = $field::HIDDEN;
                $field->property = "perc_desconto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ Desconto";
                $field->name = "valor_desconto";
                $field->type = $field::LABEL;
                $field->property = "valor_desconto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "valor_desconto";
                $field->type = $field::HIDDEN;
                $field->property = "valor_desconto";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "% IPI";
                $field->name = "perc_ipi";
                $field->type = $field::TEXT;
                $field->property = "perc_ipi";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ IPI";
                $field->name = "valor_ipi";
                $field->type = $field::HIDDEN;
                $field->property = "valor_ipi";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ IPI";
                $field->name = "valor_ipi";
                $field->type = $field::LABEL;
                $field->property = "valor_ipi";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ BC IPI";
                $field->name = "valor_bc_ipi";
                $field->type = $field::HIDDEN;
                $field->property = "valor_bc_ipi";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ BC IPI";
                $field->name = "valor_bc_ipi";
                $field->type = $field::LABEL;
                $field->property = "valor_bc_ipi";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ Total";
                $field->name = "valor_total";
                $field->type = $field::HIDDEN;
                $field->property = "valor_total";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ Total";
                $field->name = "valor_total";
                $field->type = $field::LABEL;
                $field->property = "valor_total";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "% ICMS";
                $field->name = "perc_icms";
                $field->type = $field::TEXT;
                $field->property = "perc_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ ICMS";
                $field->name = "valor_icms";
                $field->type = $field::LABEL;
                $field->property = "valor_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "valor_icms";
                $field->type = $field::HIDDEN;
                $field->property = "valor_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ BC ICMS";
                $field->name = "valor_bc_icms";
                $field->type = $field::HIDDEN;
                $field->property = "valor_bc_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ BC ICMS";
                $field->name = "valor_bc_icms";
                $field->type = $field::LABEL;
                $field->property = "valor_bc_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ ICMS ST";
                $field->name = "valor_icms_st";
                $field->type = $field::TEXT;
                $field->property = "valor_icms_st";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ BC ICMS ST";
                $field->name = "valor_bc_icms_st";
                $field->type = $field::TEXT;
                $field->property = "valor_bc_icms_st";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "$ Frete";
                $field->name = "valor_frete";
                $field->type = $field::TEXT;
                $field->property = "valor_frete";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "NCM";
                $field->description = "NCM";
                $field->type = $field::TEXT;
                $field->property = "ncm";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "cst_icms";
                $field->description = "CST ICMS";
                $field->type = $field::TEXT;
                $field->property = "cst_icms";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "CST_IPI";
                $field->description = "CST IPI";
                $field->type = $field::TEXT;
                $field->property = "cst_ipi";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "cfop";
                $field->description = "CFOP";
                $field->type = $field::TEXT;
                $field->property = "cfop";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "csosn";
                $field->description = "CSOSN";
                $field->type = $field::TEXT;
                $field->property = "csosn";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "% PIS";
                $field->name = "perc_pis";
                $field->type = $field::TEXT;
                $field->property = "perc_pis";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "% COFINS";
                $field->name = "perc_cofins";
                $field->type = $field::TEXT;
                $field->property = "perc_cofins";
                $row->field[] = $field;

                $table->rows[] = $row;
            }
            $tabs->form->table[] = $table;

            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-6 p-0");

            // novo campo
            $field = new Fields();
            $field->name = "natureza_operacao";
            $field->description = "Natureza de operação";
            $field->type = $field::TEXT;
            $field->value = $gui->nota_fiscal->natureza_operacao;
            $field->size = 12;
            $div->field[] = $field;

            // novo campo
            $field = new Fields();
            $field->name = "informacoes_fisco";
            $field->description = "Informações adicionais do fisco";
            $field->type = $field::TEXT;
            $field->value = $gui->nota_fiscal->informacoes_fisco;
            $field->size = 12;
            $div->field[] = $field;

            // novo campo
            $field = new Fields();
            $field->name = "chave_referencia";
            $field->description = "Chave NF-e de referência";
            $field->type = $field::TEXT;
            $field->size = 12;
            $div->field[] = $field;

            $tabs->form->children[] = $div;

            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-6 p-0");

            // novo campo
            $field = new Fields();
            $field->name = "historico";
            $field->description = "Informações complementares";
            $field->type = $field::AREA;
            $field->property = "historico";
            $field->size = 12;
            $div->field[] = $field;

            $tabs->form->children[] = $div;

            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-12 p-0");

            $historico = FaturamentoETT::getHistorico();
            // cria novo campo
            $field = new Fields();
            $field->name = "Finalidade";
            $field->type = $field::CHECKBOX;
            $field->options = Options::byArray($historico["handle"], $historico["nome"]);
            $field->size = 12;
            $tabs->form->field[] = $field;

            $tabs->form->children[] = $div;

            $field = new Fields();
            $field->type = $field::SUBMIT;
            $field->name = "enviar";
            $field->class = "float-right";
            $tabs->form->field[] = $field;

            $widget->body->tabs["Produtos"] = $tabs; // colocar o nome da tab
        }

        // Duplicatas
        if (1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-money";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "faturamento";
            $tabs->form->action = _pasta . "actions.php?pagina=faturamento";

            $forma_pagamento = FaturamentoDuplicataETT::getFormaPagamento();
            array_unshift($forma_pagamento["handle"], "");
            array_unshift($forma_pagamento["nome"], "");

            // cria novo campo
            $field = new Fields();
            $field->description = "Forma de pagamento";
            $field->name = "forma_pagamento";
            $field->type = $field::SELECT;
            $field->options = Options::byArray($forma_pagamento["handle"], $forma_pagamento["nome"]);
            $field->size = 3;
            //$field->property = "supervisor";
            $tabs->form->field[] = $field;

            $condicao_pagamento = FaturamentoDuplicataETT::getCondicaoPagamento();
            array_unshift($condicao_pagamento["handle"], "");
            array_unshift($condicao_pagamento["nome"], "");
            // cria novo campo
            $field = new Fields();
            $field->name = "condicao_pagamento";
            $field->description = "Condição de pagamento";
            $field->type = $field::SELECT;
            $field->options = Options::byArray($condicao_pagamento["handle"], $condicao_pagamento["nome"]);
            $field->size = 3;
            //$field->property = "supervisor";
            $tabs->form->field[] = $field;

//            // cria novo campo
//            $field = new Fields();
//            $field->name = "spinner_parcelas";
//            $field->description = "Nº parcelas";
//            $field->type = $field::LABEL;
//            $field->value = "--";
//            if(__LIBERA_CONDICAO_PAGTO__){
//                $field->type = $field::TEXT;
//                $field->value = 1;
//            }
//            $field->size = 2;
//            //$field->property = "supervisor";
//            $tabs->form->field[] = $field;
//
//            // cria novo campo
//            $field = new Fields();
//            $field->name = "spinner_intervalo";
//            $field->description = "Dias intervalo (corridos)";
//            $field->type = $field::LABEL;
//            $field->value = "--";
//            if(__LIBERA_CONDICAO_PAGTO__){
//                $field->type = $field::TEXT;
//                $field->value = "";
//            }
//            $field->size = 2;
//            //$field->property = "supervisor";
//            $tabs->form->field[] = $field;
//

            // cria novo campo
            $field = new Fields();
            $field->name = "spinner_vencimento";
            $field->description = "Dia vencimento (fixo)";
            $field->type = $field::TEXT;
            $field->value = "";
            $field->size = 3;
            //$field->property = "supervisor";
            $tabs->form->field[] = $field;

            $bancos = FaturamentoDuplicataETT::getBanco();
            // cria novo campo
            $field = new Fields();
            $field->name = "boleto_banco";
            $field->description = "Banco (boletos)";
            $field->type = $field::SELECT;
            $field->options = Options::byArray(array_keys($bancos), array_values($bancos));
            $field->value = $gui->duplicatas[0]->banco;
            $field->size = 3;
            //$field->property = "supervisor";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "propriedade";
            $field->type = $field::CHECKBOX;
            $field->options[] = new Options(0, "Primeira à vista");
            $field->options[] = new Options(1, "Apagar duplicatas (apenas orçamento)");
            $field->size = 6;
            //$field->property = "supervisor";
            $tabs->form->field[] = $field;

            $field = new Fields();
            $field->type = $field::BUTTON;
            $field->name = "Calcular parcelas";
            $field->function = "duplicatas.processa()";
            $field->class = "float-right btn-warning mt-3";
            if(count($gui->duplicatas) > 0 ){
                $field->function = "";
                $field->class = "float-right btn-default mt-3";
            }
            $tabs->form->field[] = $field;

            // ancora para o endereco
            $field = new Fields();
            $field->name = "duplicataanchor";
            $field->type = $field::HIDDEN;
            $field->size = 2;
            $tabs->form->field[] = $field;

            // adiciono uma objeto vazio no final para servir de modelo para o novo
            $modelo = new FaturamentoDuplicataETT($gui->handle);
            array_unshift($gui->duplicatas, $modelo);
            $prefixo = FaturamentoDuplicataETT::getPrefixo();

            // instancia a tabela dinamica
            $table = new FormTable("duplicatas");
            $table->after = "duplicataanchor";
            //$table->delete_block = false;
            $table->view = FormTable::TABLE_DYNAMIC;

            // itera as linhas
            foreach ($gui->duplicatas as $key => $r) {
                $row = new FormTableRow();
                $row->entity = $r;

                // cria novo campo
                $field = new Fields();
                $field->name = "handle";
                $field->type = $field::HIDDEN;
                $field->property = "handle";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "numero";
                $field->description = "Nº parcela";
                $field->type = $field::LABEL;
                $field->property = "numero";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "numero";
                $field->description = "Nº parcela";
                $field->type = $field::HIDDEN;
                $field->property = "numero";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Vencimento";
                $field->name = "data_vencimento_original";
                $field->type = $field::TEXT;
                $field->class = "datepicker-date";
                $field->property = "data_vencimento_original";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Venc. real	";
                $field->name = "data_vencimento_real";
                $field->type = $field::TEXT;
                $field->class = "datepicker-date";
                $field->property = "data_vencimento_real";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Dias";
                $field->name = "dias";
                $field->type = $field::LABEL;
                $field->property = "dias";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Dias";
                $field->name = "dias";
                $field->type = $field::HIDDEN;
                $field->property = "dias";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Intervalo";
                $field->name = "intervalo";
                $field->type = $field::LABEL;
                $field->property = "intervalo";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Intervalo";
                $field->name = "intervalo";
                $field->type = $field::HIDDEN;
                $field->property = "intervalo";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Valor";
                $field->name = "valor_total";
                $field->type = $field::TEXT;
                $field->property = "valor_total";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Pagamento";
                $field->name = "forma_pagamento";
                $field->class = "larger";
                $field->type = $field::SELECT;
                $field->options = Options::byArray($forma_pagamento["handle"], $forma_pagamento["nome"]);
                $field->property = "cod_forma_pagamento";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Prefixo";
                $field->name = "prefixo";
                $field->type = $field::SELECT;
                //$field->class = "larger";
                $field->options = Options::byArray($prefixo["handle"], $prefixo["nome"]);
                $field->property = "cod_prefixo";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->description = "Cheque";
                $field->name = "cheque";
                $field->type = $field::TEXT;
                $field->options = Options::byArray($forma_pagamento["handle"], $forma_pagamento["nome"]);
                $field->property = "cheque";
                $row->field[] = $field;

                $table->rows[] = $row;
            }

            $tabs->form->table[] = $table;

            $field = new Fields();
            $field->type = $field::SUBMIT;
            $field->name = "enviar";
            $field->class = "float-right";
            $tabs->form->field[] = $field;

            $widget->body->tabs["Duplicatas"] = $tabs; // colocar o nome da tab
        }

        // Transporte
        if (1 == 1) {
            $uf = PessoaEnderecoETT::getListaEstados();
            array_unshift($uf["uf"], "");
            array_unshift($uf["nome"], "");

            $tabs = new Tabs();
            $tabs->icon = "fa fa-truck";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "faturamento";
            $tabs->form->action = _pasta . "actions.php?pagina=faturamento";

            // cria novo campo
            $field = new Fields();
            $field->name = "Transportadora";
            $field->type = $field::TEXT;
            $field->size = 5;
            $field->property = "entrega->transportadora";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "cod_transportadora";
            $field->description = "Cód. transportadora";
            $field->type = $field::LABEL;
            $field->size = 2;
            $field->property = "entrega->cod_transportadora";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Data entrega";
            $field->type = $field::TEXT;
            $field->size = 2;
            $field->class = "datepicker-date";
            $field->property = "entrega->data_entrega";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Tipo frete";
            $field->type = $field::SELECT;
            $field->size = 3;
            $field->options = Options::byArray(array(9, 1, 0, 2), array("Sem frete", "FOB", "CIF", "Terceiros"));
            $field->property = "entrega->cod_tipo_frete";
            $tabs->form->field[] = $field;

            // =========================================================================================================

            // cria novo campo
            $field = new Fields();
            $field->name = "anchorendereco";
            $field->description = "Endereço de entrega";
            $field->type = $field::LABEL;
            $field->size = 12;
            $tabs->form->field[] = $field;

            // instancia a lista endereço de entrega
            $table = new FormTable("entrega");
            $table->after = "anchorendereco";
            $table->view = FormTable::LIST_STATIC;

            $row = new FormTableRow();
            $row->entity = $gui->entrega;

            // cria novo campo
            $field = new Fields();
            $field->name = "Logradouro";
            $field->type = $field::TEXT;
            $field->size = 5;
            $field->property = "logradouro";
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Complemento";
            $field->type = $field::TEXT;
            $field->size = 4;
            $field->property = "complemento";
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "endereco_numero";
            $field->description = "Número";
            $field->type = $field::TEXT;
            $field->size = 3;
            $field->property = "numero";
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Bairro";
            $field->type = $field::TEXT;
            $field->size = 5;
            $field->property = "bairro";
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "edereco_estado";
            $field->description = "Estado";
            $field->type = $field::SELECT;
            $field->options = Options::byArray($uf["uf"], $uf["nome"]);
            $field->property = "estado";
            $field->size = 3;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Cidade";
            $field->type = $field::SELECT;
            $field->property = "cod_cidade";

            if(empty($gui->entrega->cidade)){
                $field->options[] = new Options("", "Por gentileza, escolha um estado");
            }
            else{
                $field->options[] = new Options($gui->entrega->cidade, $gui->entrega->cidade);
            }
            $field->size = 3;
            $row->field[] = $field;

            $table->rows[] = $row;
            $tabs->form->table[] = $table;

            // =========================================================================================================

            // cria novo campo
            $field = new Fields();
            $field->name = "anchorveiculo";
            $field->description = "Endereço de entrega";
            $field->type = $field::LABEL;
            $field->size = 12;
            $tabs->form->field[] = $field;

            // instancia a lista veiculo
            $table = new FormTable("entrega");
            $table->after = "anchorveiculo";
            $table->view = FormTable::LIST_STATIC;

            $row = new FormTableRow();
            $row->entity = $gui->entrega;

            // cria novo campo
            $field = new Fields();
            $field->name = "Placa";
            $field->type = $field::TEXT;
            $field->property = "placa";
            $field->size = 2;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "UF placa";
            $field->type = $field::SELECT;
            $field->options = Options::byArray($uf["uf"], $uf["nome"]);
            $field->property = "uf_placa";
            $field->size = 3;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "rntc";
            $field->description = "Cód. ANTT";
            $field->type = $field::TEXT;
            $field->property = "rntc";
            $field->size = 2;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Motorista";
            $field->type = $field::TEXT;
            $field->property = "motorista";
            $field->size = 5;
            $row->field[] = $field;

            $table->rows[] = $row;
            $tabs->form->table[] = $table;

            // =========================================================================================================

            // cria novo campo
            $field = new Fields();
            $field->name = "anchorvolumes";
            $field->description = "Volumes";
            $field->type = $field::LABEL;
            $field->size = 12;
            $tabs->form->field[] = $field;

            // instancia a lista veiculo
            $table = new FormTable("entrega");
            $table->after = "anchorvolumes";
            $table->view = FormTable::LIST_STATIC;

            $row = new FormTableRow();
            $row->entity = $gui->entrega;

            // cria novo campo
            $field = new Fields();
            $field->description = "Quantidade";
            $field->name = "volume_quantidade";
            $field->type = $field::TEXT;
            $field->property = "volume_quantidade";
            $field->size = 2;
            $row->field[] = $field;

            $unidade = ProdutoETT::getUnidade();
            array_unshift($unidade["nome"], "");
            array_unshift($unidade["abreviatura"], "");

            // cria novo campo
            $field = new Fields();
            $field->name = "volume_especie";
            $field->description = "Espécie";
            $field->type = $field::SELECT;
            $field->options = Options::byArray($unidade["abreviatura"], $unidade["nome"]);
            $field->property = "volume_especie";
            $field->size = 2;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "volume_marca";
            $field->description = "Marcas";
            $field->type = $field::TEXT;
            $field->property = "volume_marca";
            $field->size = 2;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "volume_numeracao";
            $field->description = "Numeração";
            $field->type = $field::TEXT;
            $field->property = "volume_numeracao";
            $field->size = 2;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "volume_peso_bruto";
            $field->description = "Peso bruto (kg)";
            $field->type = $field::TEXT;
            $field->property = "volume_peso_bruto";
            $field->size = 2;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "volume_peso_liquido";
            $field->description = "Peso líquido (kg)";
            $field->type = $field::TEXT;
            $field->property = "volume_peso_liquido";
            $field->size = 2;
            $row->field[] = $field;

            $table->rows[] = $row;
            $tabs->form->table[] = $table;
            // =========================================================================================================

            $field = new Fields();
            $field->type = $field::SUBMIT;
            $field->name = "enviar";
            $field->class = "float-right";
            $tabs->form->field[] = $field;

            $widget->body->tabs["Transporte"] = $tabs; // colocar o nome da tab
        }

        //Painel NFE
        if($handle > 0){
            // carrega calendario
            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-6 p-0");

            $tabs = new Tabs();
            $tabs->icon = "fa fa-sliders";
            $gui->getNotas($handle);
            // cria novo campo
            $field = new Fields();
            $field->name = "protoculo";
            $field->type = $field::TEXT;
            $field->size = 12;
            $field->value = $gui->protocolo;
            $field->description = "Protocolo";
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "chave";
            $field->type = $field::TEXT;
            $field->size = 12;
            $field->value = $gui->chave;
            $field->description = "Chave";
            $div->field[] = $field;

            $tabs->form->children[] = $div;

            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-6 p-0");

            // cria novo campo
            $field = new Fields();
            $field->name = "senha";
            $field->type = $field::TEXT;
            $field->size = 8;
            $field->description = "Senha para liberação";
            $div->field[] = $field;

            $field = new Fields();
            $field->type = $field::BUTTON;
            $field->name = "Cancelar";
            $field->class = "float-right btn btn-danger mx-0 col-4";
            $field->function = "abreModalNFE()";
            $div->field[] = $field;

            $field = new Fields();
            $field->type = $field::BUTTON;
            $field->name = "danfe";
            $field->description = "VISUALIZAR DANFE";
            $field->class = " btn btn-info btn-block";
            $div->field[] = $field;

            $field = new Fields();
            $field->type = $field::BUTTON;
            $field->name = "IMPRIMIR CUPOM";
            $field->class = " btn btn-warning btn-block";
            $div->field[] = $field;

            $tabs->form->children[] = $div;

            // cria novo campo
            $field = new Fields();
            $field->name = "xml";
            $field->type = $field::AREA;
            $field->size = 12;
            $field->value = $gui->xml;
            $field->description = "XML de exportação";
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "dados_retorno";
            $field->type = $field::AREA;
            $field->size = 6;
            $field->description = "Retorno da SEFAZ";
            $field->value = $gui->dados_retorno;
            $tabs->form->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "txt_cupom";
            $field->type = $field::AREA;
            $field->size = 6;
            $field->value = $gui->txt_cupom;
            $field->description = "Cupom fiscal";
            $tabs->form->field[] = $field;

            $field = new Fields();
            $field->type = $field::SUBMIT;
            $field->name = "enviar";
            $field->class = "float-right";
            $tabs->form->field[] = $field;

            $widget->body->tabs["Painel NFE"] = $tabs; // colocar o nome da tab
        }

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=faturamento&pesq_tipo={$gui->cod_tipo}')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();
        return $widget;
    }
}