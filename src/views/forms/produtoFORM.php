<?php
namespace src\views\forms;

use src\creator\widget\Component;
use src\entity\EstoqueETT;
use src\entity\FornecedoresGUI;
use src\entity\ProdutoEstruturadoGUI;
use src\entity\ProdutoETT;
use src\entity\ProdutoGUI;
use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Header;
use src\creator\widget\Options;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\TabelaPrecosETT;
use src\entity\TabelaPrecosGUI;
use src\views\ControladoraFORM;


class ProdutoFORM implements ControladoraFORM {
    public function createSearch()
    {
        $widget = new Widget();
        $widget->header->title = "Produto";
        $widget->header->icon = "fa fa-graduation-cap";
        $widget->includes[] = "src/public/js/cadastro/produto.js";

        // criar body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        $tabs->form->action = "?pagina=produto";

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "nome";
        $field->description = "Nome/descrição";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "codigo";
        $field->description = "Código";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "ativo";
        $field->description = "Ativo";
        $field->options[] = new Options("", "");
        $field->options[] = new Options("S", "Sim");
        $field->options[] = new Options("N", "Não");
        $field->size = 4;
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 6);

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "produto";
        $tabs->table->target = "?pagina=produto";
        $tabs->table->entity = ProdutoGUI::class; // passar a classe/entidade para invocar

        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab
        $tab = new Tabs();
        $tab->function = "Tools.redirect('?pagina=produto&pesq_num=0')";
        $tab->icon = "i icon-plus-sign";

        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=produto&pesq_num=0')";
        $tabs->icon = "fa fa-plus";
        $widget->body->tabs["Inserir"] = $tabs;

        $widget->setDefaults();
        return $widget;

    }
    
    public function createForm($handle = null)
    {

        global $conexao;

        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            // instancia a entidade
            $gui = new ProdutoETT();
        }
        else {
            // instancia a entidade
            $gui = new ProdutoGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        $widget = new Widget();
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Lista #{$gui->indice}";
        $widget->includes[] = "src/public/js/cadastro/produto.js";



        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "i icon-info-sign";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_produto";
        $tabs->form->action = _pasta . "actions.php?pagina=produto";


        // cria novo campo
        $field = new Fields();
        $field->type = $field::CHECKBOX;
        $field->name = "propriedade";
        //$field->property = "recorrente";
        $field->options[] = new Options("0", "Ativo*");
        $field->options[] = new Options("1", "Loja Virtual");
        $field->options[] = new Options("2", "Destaques");
        $field->options[] = new Options("3", "Mivimenta estoque");
        $field->options[] = new Options("4", "Controla saldo");
        $field->options[] = new Options("5", "Reserva estoque");
        $field->options[] = new Options("6", "Controla lote");
        $field->options[] = new Options("7", "Terceiro");
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::HIDDEN;
        $field->description = "Código";
        $field->name = "codigo";
        $field->property = "handle";
        $field->size = 1;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "Handle";
        $field->name = "handle";
        $field->property = "handle";
        $field->size = 1;
        $tabs->form->field[] = $field;


        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Cód.alternativo";
        $field->name = "codigo_alternativo";
        $field->property = "codigo_alternativo";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Cód.barras/ISBN";
        $field->name = "codigo_barras";
        $field->property = "codigo_barras";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Lista serviço";
        $field->name = "codigo_servico";
        $field->property = "codigo_servico";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo

        $tipoFab = Tools::emptyOption(ProdutoETT::getNcm());
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "NCM";
        $field->name = "ncm";
        $field->property = "ncm";
        $field->options = Options::byArray($tipoFab['handle'],$tipoFab['nome']);
        $field->size = 5;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Nome";
        $field->name = "nome";
        $field->property = "nome";
        $field->size = 5;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Marca";
        $field->name = "marca";
        $field->property = "marca";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $getEndereco = Tools::emptyOption(EstoqueETT::getEndereco());
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Endereço";
        $field->name = "endereco";
        $field->property = "endereco";
        $field->options = Options::byArray($getEndereco['handle'], $getEndereco['nome']);
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $familia_produto = Tools::emptyOption(ProdutoETT::get_familia_produto("familia"));
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Família";
        $field->name = "familia-2";
        $field->property = "familia";
        $field->options = Options::byArray($familia_produto['handle'], $familia_produto['nome']);
        $field->function = "getGrupo()";
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $familia_produto = Tools::emptyOption(ProdutoETT::get_familia_produto("grupo"));
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Grupo";
        $field->function = "getGrupo()";
        $field->name = "grupo";
        $field->property = "grupo";
        $field->options = Options::byArray($familia_produto['handle'],$familia_produto['nome']);
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $familia_produto = Tools::emptyOption(ProdutoETT::get_familia_produto("subgrupo"));
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Subgrupo";
        $field->name = "cod_familia";
        $field->property = "cod_familia";
        $field->options = Options::byArray($familia_produto['handle'],$familia_produto['nome']);
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $array_material = ProdutoETT::getMaterial("0",true);
        $base_material = range(0, count($array_material) - 1);
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Material";
        $field->name = "material";
        $field->property = "material";
        $field->options = Options::byArray($base_material, $array_material);
        $field->size = 3;
        $tabs->form->field[] = $field;


        // cria novo campo
        $tipoOp = Tools::emptyOption(ProdutoETT::getTipoOperacao("E"));
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Operação entrada";
        $field->name = "tipo_movimento_entrada";
        $field->property = "tipo_movimento_entrada";
        $field->options = Options::byArray($tipoOp['handle'],$tipoOp['nome']);
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $tipoOp = Tools::emptyOption(ProdutoETT::getTipoOperacao("S"));
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Operação saída";
        $field->name = "tipo_movimento_saida";
        $field->property = "tipo_movimento_saida";
        $field->options = Options::byArray(array_keys($tipoOp['handle']), array_values($tipoOp['nome']));
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Nº série";
        $field->name = "numero_serie";
        $field->property = "numero_serie";
        $field->size = 4;
        $tabs->form->field[] = $field;

        $widget->body->tabs["Cabeçalho"] = $tabs; // colocar o nome da tab
        $tabs->icon = "fa fa-info-circle";
        $tabs = new Tabs();

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_produto";
        $tabs->icon = "fa fa-tag";
        $tabs->form->action = _pasta . "actions.php?pagina=produto";
        /*formulario de formacao de preco*/

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "% Margem valor agregado";
        $field->name = "mva";
        $field->property = "mva";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "Saldo estoque";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "Valor estoque";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Estoque mínimo*";
        $field->name = "reservado";
        $field->property = "reservado";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Perc. comissão";
        $field->name = "perc_comissao";
        $field->property = "perc_comissao";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Custo compra";
        $field->name = "valor_custo";
        $field->property = "valor_custo";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "ICMS compra %";
        $field->name = "valor_cred_ipi";
        $field->property = "valor_cred_ipi";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "IPI compra %";
        $field->name = "valor_cred_icms";
        $field->property = "valor_cred_icms";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Custo frete %";
        $field->name = "valor_frete";
        $field->property = "valor_frete";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "Preço FOB";
        $field->name = "valor_preco_fob";
        $field->property = "valor_preco_fob";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Markup";
        $field->name = "valor_markup";
        $field->property = "valor_markup";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "Preço venda";
        $field->name = "valor_venda";
        $field->property = "valor_venda";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "ICMS médio vd. %";
        $field->name = "valor_icms";
        $field->property = "valor_icms";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "% IPI venda ";
        $field->name = "perc_ipi";
        $field->property = "perc_ipi";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Média descontos %";
        $field->name = "valor_descontos";
        $field->property = "valor_descontos";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Acréscimos";
        $field->name = "valor_acrescimos";
        $field->property = "valor_acrescimos";
        $field->class = "money";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::LABEL;
        $field->description = "% Margem lucro";
        $field->name = "perc_margem";
        $field->property = "perc_margem";
        $field->size = 2;
        $tabs->form->field[] = $field;

        /*instacia a tabela dinamica*/
        $table = new FormTable("tabela");
        $table->after = "perc_margem";
        $table->view = $table::TABLE_DYNAMIC;
        $table->delete_block = false;

        $template_table = new TabelaPrecosETT();
        array_unshift($gui->tabela, $template_table);

        foreach ($gui->tabela as $r){
            $row = new FormTableRow();
            $row->entity = $r;
            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->property = "handle";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Tabela de Precos";
            $field->name = "tabela_preco";
            $field->property = "tabela_preco";
            $field->size = 2;
            $row->field[] = $field;

            $array_material = ProdutoETT::getTabPrecos("0",true);
            $base_material = range(0, count($array_material) - 1);
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Nome";
            $field->name = "nome";
            $field->property = "nome";
            $field->options = Options::byArray($array_material, $array_material);
            $field->size = 2;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Valor";
            $field->name = "valor";
            $field->property = "valor";
            $field->class = "money";
            $field->size = 2;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Variação preço base";
            $field->name = "perc_tab";
            $field->property = "perc_tab";
            $field->class = "porcent";
            $field->size = 2;
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Quantidade mínima";
            $field->name = "qtd_tab";
            $field->property = "qtd_tab";
            $field->size = 2;
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->size = 2;
        $field->name = "enviar";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->description = "CADASTRO FAMÍLIA";
        $field->size = 2;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->description = "CADASTRO TIPO OP";
        $field->size = 2;
        $field->function = "destinoMenu('cadastro_tipo_operacao')";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Formação de preço"] = $tabs; // colocar o nome da tab
        $tabs = new Tabs();
        $tabs->icon = "i icon-truck";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_produto";
        $tabs->form->action = _pasta . "actions.php?pagina=produto";


        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->size = 2;
        $field->name = "enviar";
        $tabs->form->field[] = $field;


        /*instacia a tabela dinamica*/
        $table = new FormTable("pesq_codigo");
        $table->after = "perc_margem";
        $table->view = $table::TABLE_DYNAMIC;
        $table->delete_block = false;
        $tabela_produto = new  FornecedoresGUI();
        $tabela_produto->fetch();

        array_unshift($tabela_produto->itens, $gui->handle);



        foreach ($tabela_produto->itens as $r){

            $row = new FormTableRow();
            $row->entity = $r;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Nome";
            $field->name = "nome_fornecedor";
            $field->property = "nome_fornecedor";
            $field->size = 6;
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Cód.";
            $field->name = "cod_fornecedor";
            $field->property = "cod_fornecedor";
            $field->size = 4;
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->description = "CADASTRO FAMÍLIA";
        $field->size = 2;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->description = "CADASTRO TIPO OP";
        $field->size = 2;
        $field->function = "destinoMenu('cadastro_tipo_operacao')";
        $tabs->form->field[] = $field;


        /*iniciar cadastro tecnico*/

        $tabs = new Tabs();
        $widget->body->tabs["Técnico"] = $tabs; // colocar o nome da tab
        $tabs->icon = "fa fa-expand";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_produto";
        $tabs->form->action = _pasta . "actions.php?pagina=produto";

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Largura(mm)";
        $field->name = "medida_x";
        $field->property = "medida_x";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Comprimento(mm)";
        $field->name = "medida_z";
        $field->property = "medida_z";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Espessura(mm)";
        $field->name = "medida_y";
        $field->property = "medida_y";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Garantia";
        $field->name = "garantia";
        $field->property = "garantia";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Peso (kg)";
        $field->name = "peso";
        $field->property = "peso";
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $tipoUni = ProdutoETT::getUnidade();
        array_unshift($tipoUni["handle"], "");
        array_unshift($tipoUni["nome"], "");
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Unidade";
        $field->name = "unidade";
        $field->property = "unidade";
        $field->options = Options::byArray($tipoUni['handle'],$tipoUni['nome']);
        $field->size = 3;
        $tabs->form->field[] = $field;


        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Cor";
        $field->name = "cor";
        $field->property = "cor";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Tamanho";
        $field->name = "tamanho";
        $field->property = "tamanho";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Data";
        $field->name = "data_inclusao";
        $field->property = "data_inclusao";
        $field->class = "datepicker-date";
        $field->size = 3;
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Produto estruturado";
        $field->name = "estruturado";
        $field->property = "estruturado";
        $field->options[] = new Options("", "");
        $field->options[] = new Options("1", "Sim");
        $field->options[] = new Options("2", "Não");
        $field->size = 3;
        $tabs->form->field[] = $field;

        if(__PRODUCAO__) {
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Produção/engenharia";
            $field->name = "producao";
            $field->property = "producao";
            $field->options[] = new Options("", "");
            $field->options[] = new Options("1", "Sim");
            $field->options[] = new Options("2", "Não");
            $field->size = 3;
            $tabs->form->field[] = $field;
        }else{
            // cria novo campo
            $tipoMod = ProdutoETT::getModelo();
            array_unshift($tipoMod["handle"], "");
            array_unshift($tipoMod["nome"], "");
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Modelo";
            $field->name = "modelo";
            $field->property = "modelo";
            $field->options = Options::byArray($tipoMod['handle'],$tipoMod['nome']);
            $field->size = 3;
            $tabs->form->field[] = $field;

            // cria novo campo
            $tipoFab = ProdutoETT::getFabricante();
            array_unshift($tipoFab["handle"], "");
            array_unshift($tipoFab["nome"], "");
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Fabricante/Montadora";
            $field->name = "fabricante";
            $field->property = "fabricante";
            $field->options = Options::byArray($tipoFab['handle'],$tipoFab['nome']);
            $field->size = 3;
            $tabs->form->field[] = $field;
        }


        /*instacia a tabela dinamica*/
        $table = new FormTable("tabela_produto_estruturado");
        $table->after = "fabricante";
        $table->view = $table::TABLE_DYNAMIC;
        $table->delete_block = false;
        $tabela_produto_estruturado = new ProdutoEstruturadoGUI();
        array_unshift($gui->tabela_produto_estruturado, $tabela_produto_estruturado);

        foreach ($gui->tabela as $r){

            $row = new FormTableRow();
            $row->entity = $r;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Cód.";
            $field->name = "pe_cod_filho";
            $field->property = "pe_cod_filho";
            $field->class = "pe_cod_filho";
            $field->size = 2;
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Produto estruturado";
            $field->name = "pe_filho";
            $field->property = "pe_filho";
            $field->size = 2;
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Unidade";
            $field->name = "pe_unidade";
            $field->property = "pe_unidade";
            $field->size = 2;
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Quantidade";
            $field->name = "pe_quantidade";
            $field->property = "pe_quantidade";
            $field->size = 2;
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Valor unitário";
            $field->name = "pe_valor_unitario";
            $field->property = "pe_valor_unitario";
            $field->size = 2;
            $row->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Valor total";
            $field->name = "pe_valor_total";
            $field->property = "pe_valor_total";
            $field->size = 2;
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;


        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Custo prod. estruturado";
        $field->name = "valor_custo_pe";
        $field->property = "valor_custo_pe";
        $field->size = 4;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Markup %";
        $field->name = "valor_markup_pe";
        $field->property = "valor_markup_pe";
        $field->size = 4;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->description = "Custo venda";
        $field->name = "valor_venda_pe";
        $field->property = "valor_venda_pe";
        $field->size = 4;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->size = 2;
        $field->name = "enviar";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->description = "CADASTRO FAMÍLIA";
        $field->size = 2;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->description = "CADASTRO TIPO OP";
        $field->size = 2;
        $field->function = "destinoMenu('cadastro_tipo_operacao')";
        $tabs->form->field[] = $field;

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=agenda')";
        $widget->body->tabs["Retornar"] = $tabs;


        $widget->setDefaults();                 // pega todos os valores das entidades e popula
        return $widget;
    }
}

