<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 27/09/2019
 * Time: 13:01
 */

namespace src\views\forms;


use src\creator\widget\Component;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\Options;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\CadastroETT;
use src\entity\CadastroGUI;
use src\entity\PessoaGUI;
use src\views\ControladoraFORM;


class cadastroFORM implements ControladoraFORM
{
    public function createSearch()
    {
        global $__MODULO__;
        $dados = CadastroETT::cadastroLoader();

        $widget = new Widget();
        $widget->header->title = $dados["nome"];
        $widget->header->icon = "fa fa-persons";

        if($__MODULO__ == "Educacional"){
            $widget->header->icon = "fa fa-book";
        }

        //$widget->includes[] = "src/public/js/cadastro/agenda.js";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        //cria tabelas
        $tabs->table->name = $dados["nome"];
        $tabs->table->target = "?pagina=cadastro&tn=" . urlencode($dados['nome']) . "&tabela=" . $dados['table_enc'];
        $tabs->table->entity = CadastroGUI::class;               // passar a classe/entidade para invocar

        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=cadastro&pesq_num=0&tn=" . urlencode($dados['nome']) . "&tabela=" . $dados['table_enc'] . "')";
        $tabs->icon = "fa fa-plus";
        $widget->body->tabs["Inserir"] = $tabs;

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        global $__MODULO__;
        global $conexao;

        $dados_tabela = CadastroETT::cadastroLoader();
        $cadastro = $dados_tabela["cadastro"];

        // tratamento de erros da instância de tabela
        if (!empty($cadastro->tabela->mensagem_retorno)) {
            return Tools::returnError($cadastro->tabela->mensagem_retorno);
        }

        $gui = $cadastro->itens[0];

        $widget = new Widget();
        $widget->header->title = $dados_tabela["nome"];
        $widget->header->icon = "fa fa-persons";
        if($__MODULO__ == "Educacional"){
            $widget->header->icon = "fa fa-book";
        }
        $widget->entity = $gui;

        $widget->includes[] = "src/public/js/cadastro/cadastro.js";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "form_cadastro";
        $tabs->form->action = _pasta . "actions.php?pagina=cadastro";

        $div = new Component();
        $div->tag = "div";
        $div->attr = array("class" => "row");

        $div->field[] = Fields::novo(Fields::HIDDEN, 0, "tabela", "", $dados_tabela["table_enc"]);
        $div->field[] = Fields::novo(Fields::HIDDEN, 0, "tn", "", urlencode($dados_tabela["nome"]));

        foreach ($cadastro->tabela->campos as $r) {
            $campo = strtolower($r);
            $tabela = $dados_tabela["table"];
            $tam = 6;

            // default
            //$prop = $r;
            //$valor = $gui->{$prop};

            // tenta tratar campos pelo nome
            /* aqui também não precisamos de tantas especificações.
             * por exemplo, não defina um widgetTexto só porque você quer que o título do campo
             * apareça com uma formatação diferente.
             * cadastro.php NÃO é um formulário preciosista, é pra administração!
             */

            if ($campo == "handle") {
                if ($handle > 0) {
                    $field = Fields::novo(Fields::HIDDEN, 0, "atualiza", $r);
                } else {
                    //$new_handle = newHandle($tabela, $conexao);
                    $field = Fields::novo(Fields::HIDDEN, 0, "handle", $r);
                }
                $div->field[] = $field;
                $field = Fields::novo(Fields::LABEL, $tam, "Cód. registro", $r);
            } elseif ($campo == "filial" || $campo == "k_filial") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_FN_FILIAL", "NOME", "HANDLE", "", $r);
                $field->description = "Filial";
            } elseif ($campo == "regiao") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_REGIAO", "NOME", "HANDLE", "", $r);
                $field->description = "Região";
            } elseif ($campo == "senha") {
                $field = new Fields();
                $field->type = $field::PASSWORD;
                $field->property = $r;
                $field->value = "sonha";
                $field->size = $tam / 2;
                $field->name = $campo;
                $field->description = "Senha";
                $div->field[] = $field;

                $field = new Fields();
                $field->type = $field::BUTTON;
                $field->size = $tam / 2 ." mt-3";
                $field->description = "Gerar nova senha";
                $field->class = "btn-warning";
                $field->function = "makeid(5)";
            } elseif ($campo == "usuario") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_PD_USUARIOS", "NOME", "HANDLE", "", $r);
                $field->description = "Usuário";
            } elseif ($campo == "nivel" && $tabela == "K_PD_USUARIOS") {
                $field = new Fields();
                $field->type = Fields::SELECT;
                $field->size = $tam;
                $field->name = $campo;
                $field->property = $r;
                $field->description = "Nível";
                $field->options = Options::byArray(array(1, 2, 3, 4), array("1 - Unidade", "2 - Regional", "3 - Secretaria", "4 - Global"));
            } elseif ($campo == "grupo") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_FN_GRUPOUSUARIO", "NOME", "HANDLE", "", $r);
                $field->description = "Grupo";
            } elseif ($campo == "alcada") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_PD_ALCADAS", "NOME", "HANDLE", "", $r);
                $field->description = "Alçada";
            }  elseif ($campo == "area") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_FN_AREA", "NOME", "HANDLE", "", $r);
                $field->description = "Área";
            } elseif ($campo == "almoxarifado") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_FN_ALMOXARIFADO", "NOME", "HANDLE", "WHERE " . filtraFilial("FILIAL", "Almoxarifado"), $r);
                $field->description = "Almoxarifado";
            } elseif ($campo == "produto") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "PD_PRODUTOS", "NOME", "CODIGO", "WHERE " . filtraFilial("K_KFILIAL", "Produto"), $r);
                $field->description = "Produto";
            } elseif ($campo == "estado") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "ESTADOS", "NOME", "HANDLE", "WHERE HANDLE <= 48", $r);
            } elseif ($campo == "cidade") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "MUNICIPIOS", "NOME", "HANDLE", "WHERE ESTADO = {$cadastro->itens[0]->ESTADO}", $r);
            }
            elseif ($campo == "empresa") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "K_FN_PESSOA", "NOME", "HANDLE", "WHERE EMPRESA = 'S'", $r);
            }
            elseif ($campo == "cliente") {
                if (!empty($gui->$r)) {
                    $pessoa = new PessoaGUI();
                    $pessoa->pesquisa["pesq_num"] = $gui->$r;
                    $pessoa->fetch();
                    $pessoa = $pessoa->itens[0];
                }
                $field = Fields::novo(Fields::TEXT, $tam / 2, "Pessoa vinculada", "", $pessoa->nome);
                $div->field[] = $field;
                $field = Fields::novo(Fields::LABEL, $tam / 2, "Código pessoa", $r);
            }
            elseif ($campo == "formapagamento") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "FN_FORMASPAGAMENTO", "NOME", "HANDLE", "", $r);
                $field->description = "Forma pagamento";
            } elseif ($campo == "condicaopagamento" || $campo == "condicoespagamento") {
                $field = Fields::fromTable(Fields::SELECT, $tam, $campo, "CP_CONDICOESPAGAMENTO", "DESCRICAO", "HANDLE", "", $r);
                $field->description = "Condição pagto.";
            } elseif (in_array($campo, array("usalimite", "bloqueio", "menu", "compartilhado", "ativa", "exclusiva", "k_docliquidez", "ativo", "padrao"))) {
                $field = new Fields();
                $field->type = Fields::SELECT;
                $field->size = $tam;
                $field->property = $r;
                $field->name = $campo;
                $field->options = Options::byArray(array("N", "S"), array("Não", "Sim"));
            } elseif ($campo == "notas") {
                $field = Fields::novo(Fields::AREA, $tam, $campo, $r);
                $field->description = "Notas";
            } elseif ($campo == "timezone") {
                $field = new Fields();
                $field->type = Fields::SELECT;
                $field->size = $tam;
                $field->property = $r;
                $field->name = $campo;
                $field->description = "Fuso horário";
                $field->options = Options::byArray(array("-02:00", "-03:00", "-04:00", "-05:00"),
                        array("-02:00 (Horário de verão, Fernando de Noronha)", "-03:00 (Horário de Brasília)", "-04:00 (Amazônia)", "-05:00 (Acre)"));

            } else { // DEFAULT
                $field = Fields::novo(Fields::TEXT, $tam, $campo, $r);
            }
            $div->field[] = $field;
        }

        $tabs->form->children[] = $div;

        $div2 = new Component();
        $div2->tag = "div";
        $div2->attr = array("class" => "row");

        $size = 12;
        if($handle > 0 && !in_array($dados_tabela["table"], CadastroETT::$protegidos)){
            $field = new Fields();
            $field->name = "excluir";
            $field->type = $field::HIDDEN;
            $field->value = 0;
            $div2->field[] = $field;

            $field = new Fields();
            $field->type = $field::BUTTON;
            $field->name = "excluir";
            $field->size = 6;
            $field->function = "Form.excluir('#{$tabs->form->name}')";
            $field->class = "btn-danger mt-3";
            $div2->field[] = $field;

            $size = 6;
        }

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->size = $size;
        $field->class = "mt-3 float-right";
        $div2->field[] = $field;

        $tabs->form->children[] = $div2;

        $widget->body->tabs["Editar"] = $tabs;

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina=cadastro&tn=".urlencode($dados_tabela['nome'])."&tabela=".$dados_tabela['table_enc']."')";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();
        return $widget;
    }
}
