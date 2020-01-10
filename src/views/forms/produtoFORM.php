<?php
namespace src\views\forms;

use src\creator\widget\Component;
use src\entity\EstoqueETT;
use src\entity\GaleriaETT;
use src\entity\GaleriaGUI;
use src\entity\ProdutoEstruturadoETT;
use src\entity\ProdutoETT;
use src\entity\ProdutoFornecedorETT;
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
use src\entity\ProdutoTabelaPrecoETT;
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

        if(empty($gui)){
            return Tools::returnError("Registro não encontrado.", "produto");
        }
        
        $widget = new Widget();
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = (!isset($_REQUEST["duplicar"]))?"Produto #{$gui->handle}" : "Duplicar produto #{$gui->handle}";
        $widget->includes[] = "src/public/js/cadastro/produto.js";

        // cria body e tabs
        $widget->body = new Body();

        // cabeçalho
        if(1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-info-circle";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "form_produto";
            $tabs->form->action = _pasta . "actions.php?pagina=produto";

            // ficha
            if ($handle > 0 && !isset($_REQUEST["duplicar"])) {
                $galeria = new GaleriaGUI();
                $galeria->pesquisa["pesq_target"] = GaleriaETT::TARGET_PRODUTO;
                $galeria->pesquisa["pesq_referencia"] = $gui->handle;
                $galeria->fetch();

                $div = new Component();
                $div->tag = "div";
                $div->attr = array("class" => "card my-1", "id" => "card_ficha");

                $div2 = new Component();
                $div2->tag = "div";
                $div2->attr = array("class" => "card-body");
                // *
                
                $div3 = new Component();
                $div3->setGaleria($galeria, $gui->handle, GaleriaETT::TARGET_PRODUTO);

                $div2->children[] = $div3;
                $div->children[] = $div2;
                $tabs->form->children[] = $div;
            }

            // carrega calendario
            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-2 p-0");
            
            // cria novo campo
            $field = new Fields();
            $field->type = $field::CHECKBOX;
            $field->name = "propriedade";
            //$field->property = "recorrente";
            $field->options[] = new Options("0", "Ativo*", $gui->ativo);
            $field->options[] = new Options("1", "Loja Virtual", $gui->loja_virtual);
            $field->options[] = new Options("2", "Destaques", $gui->destaque);
            $field->options[] = new Options("3", "Mivimenta estoque", $gui->controla_estoque);
            $field->options[] = new Options("4", "Controla saldo", $gui->controla_saldo);
            $field->options[] = new Options("5", "Reserva estoque", $gui->reserva_estoque);
            $field->options[] = new Options("6", "Controla lote", $gui->lote);
            $field->options[] = new Options("7", "Terceiro", $gui->terceiro);
            $field->size = 12;
            $div->field[] = $field;
            
            $tabs->form->children[] = $div;
            
            // carrega calendario
            $div2 = new Component();
            $div2->tag = "div";
            $div2->attr = array("class" => "col-md-10 p-0");

            if(isset($_REQUEST["duplicar"])) {
                $field = new Fields();
                $field->type = $field::HIDDEN;
                $field->name = "duplicar";
                $field->value = 1;
                $div2->field[] = $field;
            }
            
            // cria novo campo
            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "codigo";
            $field->property = "handle";
            $div2->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->property = "handle";
            $div2->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Cód.alternativo";
            $field->name = "codigo_alternativo";
            $field->property = "codigo_alternativo";
            $field->size = 2;
            $div2->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Cód.barras/ISBN";
            $field->name = "codigo_barras";
            $field->property = "codigo_barras";
            $field->size = 2;
            $div2->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Lista serviço";
            $field->name = "codigo_servico";
            $field->property = "codigo_servico";
            $field->size = 2;
            $div2->field[] = $field;

            // cria novo campo
            $field = Fields::fromTable(Fields::SELECT, 6, "NCM", "TR_TIPIS", "CODIGONBM+' -- '+NOME", "CODIGONBM", "", "ncm");
            $field->function = "getAliquota(this)";
            $div2->field[] = $field;
            
            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Nome";
            $field->name = "nome";
            $field->required = true;
            $field->property = "nome";
            $field->size = 8;
            $div2->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "Marca";
            $field->property = "marca";
            $field->size = 4;
            $div2->field[] = $field;
            
            // cria novo campo
            $getEndereco = Tools::emptyOption(EstoqueETT::getEndereco());
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Endereço";
            $field->name = "endereco";
            $field->property = "cod_endereco";
            $field->options = Options::byArray($getEndereco['handle'], $getEndereco['nome']);
            $field->size = 3;
            $div2->field[] = $field;
            
            // cria novo campo
            $familia_produto = Tools::emptyOption(ProdutoETT::get_familia_produto("familia"));
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Família";
            $field->name = "familia-2";
            $field->required = true;
            $field->property = "cod_grupo_pai";
            $field->options = Options::byArray($familia_produto['handle'], $familia_produto['nome']);
            $field->function = "getGrupo()";
            $field->size = 3;
            $div2->field[] = $field;
            
            // cria novo campo
            $familia_produto = Tools::emptyOption(ProdutoETT::get_familia_produto("grupo"));
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Grupo";
            $field->function = "getGrupo()";
            $field->name = "grupo";
            $field->required = true;
            $field->property = "cod_grupo";
            $field->options = Options::byArray($familia_produto['handle'], $familia_produto['nome']);
            $field->size = 3;
            $div2->field[] = $field;
            
            // cria novo campo
            $familia_produto = Tools::emptyOption(ProdutoETT::get_familia_produto("subgrupo"));
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Subgrupo";
            $field->name = "cod_familia";
            $field->required = true;
            $field->property = "cod_familia";
            $field->options = Options::byArray($familia_produto['handle'], $familia_produto['nome']);
            $field->size = 3;
            $div2->field[] = $field;
            
            // cria novo campo
            $array_material = ProdutoETT::getMaterial("0", true);
            $base_material = range(0, count($array_material) - 1);
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Material";
            $field->name = "material";
            $field->property = "material";
            $field->options = Options::byArray($base_material, $array_material);
            $field->size = 3;
            $div2->field[] = $field;
            
            $field = Fields::fromTable(Fields::SELECT, 3, "tipo_movimento_entrada",
                "K_TIPOOPERACAO", "CODIGO+') '+NOME", "HANDLE", "WHERE TIPO = 'E'", "cod_tipo_movimento_entrada");
            $field->description = "Operação entrada";
            $div2->field[] = $field;
            
            $field = Fields::fromTable(Fields::SELECT, 3, "tipo_movimento_saida",
                "K_TIPOOPERACAO", "CODIGO+') '+NOME", "HANDLE", "WHERE TIPO = 'S'", "cod_tipo_movimento_saida");
            $field->description = "Operação saída";
            $div2->field[] = $field;
            
            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Nº série";
            $field->name = "numero_serie";
            $field->property = "numero_serie";
            $field->size = 3;
            $div2->field[] = $field;
            
            $tabs->form->children[] = $div2;
            
            $this->getEnviar($tabs);
            
            $widget->body->tabs["Cabeçalho"] = $tabs; // colocar o nome da tab
        }
        
        /*iniciar cadastro tecnico*/
        if( 1 == 1) {
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
            
            $field = Fields::fromTable(Fields::SELECT, 3, "Unidade", "CM_UNIDADESMEDIDA", "ABREVIATURA+') '+NOME", "HANDLE", "", "cod_unidade");
            $field->required = true;
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
            $field->property = "data";
            $field->class = "datepicker-date";
            $field->size = 3;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Produto estruturado";
            $field->name = "estruturado";
            $field->property = "estruturado";
            $field->options[] = new Options("N", "Não");
            $field->options[] = new Options("S", "Sim");
            $field->size = 3;
            $tabs->form->field[] = $field;

            if (__PRODUCAO__) {
                $field = new Fields();
                $field->type = $field::SELECT;
                $field->description = "Produção/engenharia";
                $field->name = "producao";
                $field->property = "producao";
                $field->options[] = new Options("N", "Não");
                $field->options[] = new Options("S", "Sim");
                $field->size = 3;
                $tabs->form->field[] = $field;
            } 
            else {
                $field = Fields::fromTable(Fields::SELECT, 3, "fabricante", "K_FABRICANTE", "NOME", "HANDLE", "", "fabricante");
                $field->description = "Fabricante/Montadora";
                $tabs->form->field[] = $field;
                
                $field = Fields::fromTable(Fields::SELECT, 3, "Modelo", "K_MODELO", "NOME", "HANDLE", "", "modelo");
                $tabs->form->field[] = $field;
            }

            /*instacia a tabela dinamica*/
            $table = new FormTable("tabela_estruturada");
            $table->after = "modelo";
            $table->view = $table::TABLE_DYNAMIC;
            $table->delete_block = false;

            $template_table = new ProdutoEstruturadoETT();
            array_unshift($gui->tabela_estruturada, $template_table);

            foreach ($gui->tabela_estruturada as $r) {

                $row = new FormTableRow();
                $row->entity = $r;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::HIDDEN;
                $field->name = "pe_cod_filho";
                $field->property = "cod_filho";
                $field->class = "pe_cod_filho";
                $field->size = 2;
                $row->field[] = $field;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "Cód.";
                $field->name = "pe_cod_filho";
                $field->property = "cod_filho";
                $row->field[] = $field;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::TEXT;
                $field->description = "Produto estruturado";
                $field->name = "pe_filho";
                $field->class = "larger";
                $field->property = "filho";
                $field->size = 2;
                $row->field[] = $field;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "Unidade";
                $field->name = "pe_unidade";
                $field->property = "unidade";
                $field->size = 2;
                $row->field[] = $field;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::TEXT;
                $field->description = "Quantidade";
                $field->name = "pe_quantidade";
                $field->property = "quantidade";
                $field->size = 2;
                $row->field[] = $field;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "Valor unitário";
                $field->name = "pe_valor_unitario";
                $field->property = "valor_unitario";
                $field->size = 2;
                $row->field[] = $field;

                /*cria novo campo*/
                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "Valor total";
                $field->name = "pe_valor_total";
                $field->property = "valor_total";
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
            $field->size = 6;
            $field->class = "money";
            //$tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Markup %";
            $field->name = "valor_markup_pe";
            $field->property = "valor_markup_pe";
            $field->size = 4;
            $field->class = "money";
            //$tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Valor prod. estruturado";
            $field->name = "valor_venda_pe";
            $field->class = "money";
            $field->value = 0;
            if($gui->estruturado == "S"){
                $field->value = $gui->valor_custo;
            }
            $field->size = 12;
            $tabs->form->field[] = $field;

            $this->getEnviar($tabs);
        }
        
        // Formação de preço
        if( 1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-tag";
            $widget->body->tabs["Formação de preço"] = $tabs; // colocar o nome da tab
            
            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "form_produto";
            $tabs->form->action = _pasta . "actions.php?pagina=produto";
            /*formulario de formacao de preco*/
            
            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Saldo estoque";
            $field->property = "movimento->saldo_estoque";
            $field->size = 3;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "Valor estoque";
            $field->property = "movimento->valor_estoque";
            $field->size = 3;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Estoque mínimo";
            $field->name = "reservado";
            $field->property = "reservado";
            $field->size = 2;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "Estoque máximo";
            $field->property = "estoque_maximo";
            $field->size = 2;
            $tabs->form->field[] = $field;
            
            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "$ Comissão";
            $field->name = "valor_comissao";
            $field->class = "money";
            $field->required = true;
            $field->property = "valor_comissao";
            $field->size = 2;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "Valor compra";
            $field->property = "valor_custo";
            $field->required = true;
            $field->class = "money";
            $field->size = 2;
            $tabs->form->field[] = $field;
            
            if(__DEDUCAO_TRIBUTARIA__ && 1 == 2) {
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
            }
            
            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Valor FOB";
            $field->name = "valor_preco_fob";
            $field->property = "valor_frete";
            $field->required = true;
            $field->class = "money";
            $field->size = 2;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "Custos fixos";
            $field->name = "valor_custos_fixos";
            $field->property = "valor_custos_fixos";
            $field->required = true;
            $field->class = "money";
            $field->size = 2;
            $tabs->form->field[] = $field;

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::LABEL;
            $field->description = "% IPI venda ";
            $field->name = "perc_ipi";
            $field->property = "ncm_aliquota_ipi";
            $field->class = "money";
            $field->size = 2;
            $tabs->form->field[] = $field;
            
            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "$ Markup";
            $field->name = "valor_markup";
            $field->property = "markup";
            $field->required = true;
            $field->class = "money calcular_perc";
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

            /*instacia a tabela dinamica*/
            $table = new FormTable("tabela");
            $table->after = "valor_venda";
            $table->view = $table::TABLE_DYNAMIC;
            $table->delete_block = false;

            $template_table = new ProdutoTabelaPrecoETT();
            array_unshift($gui->tabela, $template_table);

            foreach ($gui->tabela as $r) {
                $row = new FormTableRow();
                $row->entity = $r;
                
                $field = new Fields();
                $field->type = $field::HIDDEN;
                $field->name = "handle";
                $field->property = "handle";
                $row->field[] = $field;

                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "Tabela de Precos";
                $field->name = "tabela_preco";
                $field->property = "indice";
                $row->field[] = $field;

                $array_material = ProdutoTabelaPrecoETT::getTabPrecos("0", true);
                $field = new Fields();
                $field->type = $field::SELECT;
                $field->description = "Tipo";
                $field->name = "nome";
                $field->property = "nome";
                $field->class = "larger";
                $field->options = Options::byArray($array_material, $array_material);
                $row->field[] = $field;

                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "$ base";
                $field->name = "tab_valor";
                $field->value = $gui->valor_venda;
                $field->class = "money";
                $row->field[] = $field;
                
                $field = new Fields();
                $field->type = $field::TEXT;
                $field->description = "Variação $ base";
                $field->name = "perc_tab";
                $field->property = "perc_tab";
                $field->class = "percent";
                $row->field[] = $field;

                $field = new Fields();
                $field->type = $field::TEXT;
                $field->description = "Qtd. mínima";
                $field->name = "qtd_tab";
                $field->property = "qtd_tab";
                $row->field[] = $field;

                $field = new Fields();
                $field->type = $field::LABEL;
                $field->description = "$ Total";
                $field->value = formataValor($gui->valor_venda * ($r->perc_tab / 100));
                $field->name = "tab_valor_total";
                $field->property = "margem_lucro";
                $row->field[] = $field;
                
//                $field = new Fields();
//                $field->type = $field::LABEL;
//                $field->description = "% lucro";
//                $field->name = "margem_lucro";
//                $field->property = "margem_lucro";
//                $row->field[] = $field;
                
                $table->rows[] = $row;
            }

            if ($handle > 0) {
                $tabs->form->table[] = $table;
            }
            
            $this->getEnviar($tabs);
        }
        
        // Fornecedores
        if ($handle > 0){
            $tabs = new Tabs();
            $widget->body->tabs["Fornecedores"] = $tabs; // colocar o nome da tab
            $tabs->icon = "fa fa-truck";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "form_produto";
            $tabs->form->action = _pasta . "actions.php?pagina=produto";
            /*formulario de formacao de preco*/

            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "anchorfornecedores";
            $tabs->form->field[] = $field;
            
            // adiciono uma objeto vazio no final para servir de modelo para o novo
            $modelo = new ProdutoFornecedorETT();
            array_unshift($gui->fornecedores, $modelo);

            // instancia a tabela dinamica
            $table = new FormTable("fornecedores");
            $table->after = "anchorfornecedores";
            $table->delete_block = false;
            $table->view = FormTable::TABLE_DYNAMIC;

            // itera as linhas
            foreach ($gui->fornecedores as $r) {
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
                $field->name = "Fornecedor";
                $field->type = $field::TEXT;
                $field->class = "larger";
                $field->property = "fornecedor";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "cod_fornecedor";
                $field->type = $field::HIDDEN;
                $field->property = "cod_fornecedor";
                $row->field[] = $field;
                
                // cria novo campo
                $field = new Fields();
                $field->name = "Cód.";
                $field->type = $field::LABEL;
                $field->property = "cod_fornecedor";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Cód. produto";
                $field->type = $field::TEXT;
                $field->property = "codigo_fornecedor";
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Preço compra";
                $field->type = $field::TEXT;
                $field->class = "money";
                $field->property = "preco";
                $row->field[] = $field;
                
                $table->rows[] = $row;
            }
            $tabs->form->table[] = $table;
            
            $this->getEnviar($tabs);
        }
        
        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=produto')";
        $widget->body->tabs["Retornar"] = $tabs;
        $widget->setDefaults();                 // pega todos os valores das entidades e popula
        return $widget;
    }
    
    private function getEnviar(&$tabs){
        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->name = "CADASTRO FAMÍLIA";
        $field->size = 3;
        $field->function = "destinoMenu('cadastro_familia')";
        $tabs->form->field[] = $field;

        /*cria novo campo*/
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->name = "CADASTRO TIPO OP.";
        $field->size = 3;
        $field->function = "destinoMenu('cadastro_tipo_operacao')";
        $tabs->form->field[] = $field;

        if(!isset($_REQUEST["duplicar"])) {
            /*cria novo campo*/
            $field = new Fields();
            $field->type = $field::BUTTON;
            $field->name = "duplicar";
            $field->size = 3;
            $field->class = "btn-warning";
            $field->function = "duplicarProduto()";
            $tabs->form->field[] = $field;
        }
        
        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->size = (!isset($_REQUEST["duplicar"]))? 3 : 6;
        $field->name = "enviar";
        $tabs->form->field[] = $field;
    }
}

