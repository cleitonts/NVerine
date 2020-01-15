<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/05/2019
 * Time: 14:45
 */

namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Component;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Header;
use src\creator\widget\Options;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\GaleriaETT;
use src\entity\GaleriaGUI;
use src\entity\PessoaContatoETT;
use src\entity\PessoaEnderecoETT;
use src\entity\PessoaETT;
use src\entity\PessoaGUI;
use src\entity\UsuarioGUI;
use src\views\ControladoraFORM;

class pessoaFORM implements ControladoraFORM
{

    public function createSearch()
    {
        $widget = new Widget();
        $widget->includes[] = "src/public/js/cadastro/pessoa.js";
        $widget->header->title = "Pessoas";
        $widget->header->icon = "fa fa-persons";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        //$tabs->form->action = "page.php?pagina=pessoa";

        //cria tabelas
        $tabs->table->name = "pessoa";
        $tabs->table->target = "?pagina=pessoa";
        $tabs->table->entity = PessoaGUI::class;               // passar a classe/entidade para invocar

        // cria novo campo
        $field = new Fields();
        $field->type = $field::CHECKBOX;
        $field->name = "Funções";
        //$field->property = "recorrente";
        $field->options[] = new Options(1, "Ativo", 'S');
        $field->options[] = new Options(2, "Cliente");
        $field->options[] = new Options(3, "Fornecedor");
        $field->options[] = new Options(4, "Funcionário");
        $field->options[] = new Options(5, "Empresa");
        $field->options[] = new Options(6, "Aluno");
        $field->options[] = new Options(7, "Professor");
        $field->options[] = new Options(8, "Transportador");
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "CPF/CNPJ";
        $field->name = "cpf_cnpj";
        $field->type = $field::TEXT;
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->name = "Nome";
        $field->type = $field::TEXT;
        $field->size = 6;
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 9);

        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->name = "Gerar mala direta";
        $field->class = "btn-info float-right";
        $field->function = "malaDireta()";
        $field->size = 3;

        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=pessoa&pesq_num=0')";
        $tabs->icon = "fa fa-plus";
        $widget->body->tabs["Inserir"] = $tabs;

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if ($handle == 0) {
            // instancia a entidade
            $gui = new PessoaETT();
        } else {
            // instancia a entidade
            $gui = new PessoaGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }

        $widget = new Widget();
        $widget->includes[] = "src/public/js/cadastro/pessoa.js";

        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Pessoa #{$gui->handle}";

        // cria body e tabs
        $widget->body = new Body();

        // Cabeçalho
        if (1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-pencil";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "pessoa";
            $tabs->form->action = _pasta . "actions.php?pagina=pessoa";

            // ficha
            if ($handle > 0) {
                $galeria = new GaleriaGUI();
                $galeria->pesquisa["pesq_target"] = GaleriaETT::TARGET_PESSOA;
                $galeria->pesquisa["pesq_referencia"] = $gui->handle;
                $galeria->fetch();

                $div = new Component();
                $div->tag = "div";
                $div->attr = array("class" => "card my-1", "id" => "card_ficha");

                $div2 = new Component();
                $div2->tag = "div";
                $div2->attr = array("class" => "card-body");

                $div3 = new Component();
                $div3->setGaleria($galeria, $gui->handle, GaleriaETT::TARGET_PESSOA);

                $div2->children[] = $div3;
                $div->children[] = $div2;
                $tabs->form->children[] = $div;
            }

            // carrega calendario
            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-3 col-sm-6 p-0 pb-3");

            // cria novo campo
            $field = new Fields();
            $field->type = $field::CHECKBOX;
            $field->name = "Funções";
            //$field->property = "recorrente";
            $field->options[] = new Options(1, "Ativo", $gui->ativo);
            $field->options[] = new Options(2, "Cliente", $gui->cliente);
            $field->options[] = new Options(3, "Fornecedor", $gui->fornecedor);
            $field->options[] = new Options(4, "Funcionário", $gui->funcionario);
            $field->options[] = new Options(5, "Empresa", $gui->empresa);
            $field->options[] = new Options(6, "Contribuinte ICMS", $gui->contribuinte_icms);
            $field->options[] = new Options(7, "Aluno", $gui->aluno);
            $field->options[] = new Options(8, "Professor", $gui->professor);
            $field->options[] = new Options(9, "Bloqueio cadastral", $gui->credito->bloqueio);
            $field->options[] = new Options(10, "Restrição de crédito", $gui->credito->restricao);
            $field->options[] = new Options(11, "Transportador", $gui->transportador);
            $field->size = 12;
            $div->field[] = $field;

            $tabs->form->children[] = $div;

            // carrega calendario
            $div = new Component();
            $div->tag = "div";
            $div->attr = array("class" => "col-md-9 col-sm-6 p-0");

            $field = new Fields();
            $field->name = "handle";
            $field->type = $field::HIDDEN;
            $field->property = "handle";
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "tipo";
            $field->property = "tipo";
            $field->options[] = new Options("F", "Pessoa física");
            $field->options[] = new Options("J", "Pessoa jurídica");
            $field->size = 4;
            $div->field[] = $field;

            $field = new Fields();
            $field->description = "CPF/CNPJ";
            $field->name = "cpfcnpj";
            $field->type = $field::TEXT;
            $field->size = 4;
            $field->property = "cpf_cnpj";
            $div->field[] = $field;


            // cria novo campo
            $field = new Fields();
            $field->description = "RG/Inscrição";
            $field->name = "RG";
            $field->type = $field::TEXT;
            $field->size = 4;
            $field->property = "rg";
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->description = "Nome/razão social";
            $field->name = "nome";
            $field->type = $field::TEXT;
            $field->size = 8;
            $field->property = "nome";
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::TEXT;
            $field->description = "CNAE";
            $field->name = "cnae";
            $field->property = "cnae";
            $field->size = 4;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->description = "Apelido/nome fantasia";
            $field->name = "nome_fantasia";
            $field->type = $field::TEXT;
            $field->size = 8;
            $field->property = "nome_fantasia";
            $div->field[] = $field;

            $field = Fields::fromTable(Fields::SELECT, 4, "area", "K_FN_AREA", "NOME", "HANDLE", "", "cod_area");
            $field->description = "Área";
            $div->field[] = $field;

            $field = Fields::fromTable(Fields::SELECT, 5, "Segmento de negócio", "K_CRM_SEGMENTOS", "NOME", "HANDLE", "", "cod_segmento");
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Tabela de preços";
            $field->property = "tabela_preco";
            $field->options = Options::byArray(range(1, 10), range(1, 10));
            $field->size = 3;
            $div->field[] = $field;

            $vendedores = UsuarioGUI::getVendedor();
            array_unshift($vendedores["handle"], "");
            array_unshift($vendedores["nome"], "");

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Comercial";
            $field->property = "cod_vendedor";
            $field->options = Options::byArray($vendedores["handle"], $vendedores["nome"]);
            $field->size = 4;
            $div->field[] = $field;

            $listas = PessoaETT::getListaPreco();
            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Lista de preços";
            $field->property = "lista_preco";
            $field->options = Options::byArray($listas["handle"], $listas["nome"]);
            $field->size = 6;
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->name = "Data nascimento";
            $field->type = $field::TEXT;
            $field->size = 6;
            $field->class = "datepicker-date";
            $field->property = "data_nascimento";
            $div->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::AREA;
            $field->description = "Observações";
            $field->name = "editor_conteudo";
            $field->property = "observacoes";
            $field->size = 12;
            $div->field[] = $field;

            $div->field[] = $this->getSubmit();

            $tabs->form->children[] = $div;

            $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab
        }

        // Endereços
        if (1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-home";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "pessoa";
            $tabs->form->action = _pasta . "actions.php?pagina=pessoa";

            // ancora para o endereco
            $field = new Fields();
            $field->name = "enderecoanchor";
            $field->type = $field::HIDDEN;
            $tabs->form->field[] = $field;

            $estados = PessoaEnderecoETT::getListaEstados();

            // adiciono uma objeto vazio no final para servir de modelo para o novo
            $modelo = new PessoaEnderecoETT();
            array_unshift($gui->enderecos, $modelo);

            // instancia a tabela dinamica
            $table = new FormTable("enderecos");
            $table->after = "enderecoanchor";
            $table->delete_block = false;
            $table->view = FormTable::LIST_DYNAMIC;

            // itera as linhas
            foreach ($gui->enderecos as $key => $r) {
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
                $field->name = "CEP";
                $field->type = $field::TEXT;
                $field->property = "cep";
                $field->size = 2;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "uf";
                $field->description = "Estado";
                $field->type = $field::SELECT;
                $field->options = Options::byArray($estados["handle"], $estados["uf"]);
                $field->property = "cod_estado";
                $field->size = 3;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Cidade";
                $field->type = $field::SELECT;
                $field->property = "cidade";

                if(empty($r->cidade)){
                    $field->options[] = new Options("", "Por gentileza, escolha um estado");
                }
                else{
                    $field->options[] = new Options($r->cidade, $r->cidade);
                }

                $field->size = 3;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "bairro";
                $field->type = $field::TEXT;
                $field->property = "bairro";
                $field->size = 4;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "logradouro";
                $field->type = $field::TEXT;
                $field->property = "logradouro";
                $field->size = 6;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "complemento";
                $field->type = $field::TEXT;
                $field->property = "complemento";
                $field->size = 4;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "numero";
                $field->type = $field::TEXT;
                $field->property = "numero";
                $field->size = 2;
                $row->field[] = $field;

                $table->rows[] = $row;
            }
            $tabs->form->table[] = $table;

            $tabs->form->field[] = $this->getSubmit();

            $widget->body->tabs["Endereços"] = $tabs;
        }

        // Contatos
        if (1 == 1) {
            $tabs = new Tabs();
            $tabs->icon = "fa fa-phone";

            // cria form
            $tabs->form = new Form();
            $tabs->form->method = "POST";
            $tabs->form->name = "pessoa";
            $tabs->form->action = _pasta . "actions.php?pagina=pessoa";

            // ancora para o endereco
            $field = new Fields();
            $field->name = "contatoanchor";
            $field->type = $field::HIDDEN;
            $tabs->form->field[] = $field;

            // adiciono uma objeto vazio no final para servir de modelo para o novo
            $modelo = new PessoaContatoETT();
            array_unshift($gui->contatos, $modelo);

            // instancia a tabela dinamica
            $table = new FormTable("contatos");
            $table->after = "contatoanchor";
            $table->delete_block = false;
            $table->view = FormTable::LIST_DYNAMIC;

            // itera as linhas
            foreach ($gui->contatos as $key => $r) {
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
                $field->name = "nome";
                $field->type = $field::TEXT;
                $field->property = "nome";
                $field->size = 3;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "E-mail";
                $field->type = $field::TEXT;
                $field->property = "email";
                $field->size = 4;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Telefone";
                $field->type = $field::TEXT;
                $field->property = "telefone";
                $field->size = 2;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "Área";
                $field->type = $field::TEXT;
                $field->property = "area";
                $field->size = 2;
                $row->field[] = $field;

                // cria novo campo
                $field = new Fields();
                $field->name = "NF-e";
                $field->type = $field::SELECT;
                //$field->property = "nfe";
                $field->options[] = new Options("", "");
                $field->options[] = new Options("N", "N");
                $field->options[] = new Options("S", "S");
                $field->size = 1;
                $row->field[] = $field;

                $table->rows[] = $row;
            }
            $tabs->form->table[] = $table;

            $tabs->form->field[] = $this->getSubmit();

            $widget->body->tabs["Contatos"] = $tabs;
        }

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=pessoa')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;
    }

    private function getSubmit(){
        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->size = "4 float-right";

        return $field;
    }
}