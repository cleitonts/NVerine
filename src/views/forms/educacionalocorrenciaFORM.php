<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/10/2019
 * Time: 10:30
 */

namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\Header;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\EducacionalOcorrenciaETT;
use src\entity\EducacionalOcorrenciaGUI;
use src\views\ControladoraFORM;

class educacionalocorrenciaFORM implements ControladoraFORM
{

    public function createSearch()
    {
        $widget = new Widget();
        $widget->header->title = "Ocorrências";
        $widget->header->icon = "fa fa-book";

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
        $tabs->table->entity = EducacionalOcorrenciaGUI::class;               // passar a classe/entidade para invocar

        // cria novo campo
        $field = new Fields();
        $field->description = "Data inicial";
        $field->name = "pesq_data_inicial";
        $field->type = $field::TEXT;
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->description = "Data final";
        $field->name = "pesq_data_final";
        $field->type = $field::TEXT;
        $field->size = 2;
        $tabs->form->field[] = $field;

        $field = Fields::fromTable(Fields::SELECT, 4, "pesq_usuario", "K_PD_USUARIOS", "NOME", "HANDLE", "WHERE GRUPO IS NOT NULL");
        $field->description = "Responsável";
        $tabs->form->field[] = $field;


        $field = Fields::fromTable(Fields::SELECT, 4, "pesq_tipo", "K_TIPOOCORRENCIA", "NOME", "HANDLE");
        $field->description = "Tipo de ocorrência";
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->name = "pesq_pessoa";
        $field->description = "Aluno";
        $field->type = $field::TEXT;
        $field->size = 6;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->name = "pesq_cod_pessoa";
        $field->description = "Cód.";
        $field->type = $field::TEXT;
        $field->size = 2;
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 6);

        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tab = new Tabs();
        $tab->function = "destinoMenu('educacional_ocorrencia_relatorios&retorno=".urlencode(getUrlRetorno("index2.php"))."')";
        $tab->icon = "fa fa-bar-chart";
        $widget->body->tabs["Relatórios"] = $tab;

        $tabs = new Tabs();
        $tabs->function = "Tools.redirect('?pagina=educacionalocorrencia&pesq_num=0')";
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
            $gui = new EducacionalOcorrenciaETT();
            $title = "Nova turma";
        }
        else {
            // instancia a entidade
            $gui = new EducacionalOcorrenciaGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
            //$gui->handle = $handle;

            $title = "Ocorrência #".$gui->handle;
        }

        if(empty($gui)){
            return Tools::returnError("Registro não encontrado.");
        }

        // template para criação de tabela dinamica
        $template = new EducacionalTurmaListaETT();
        array_unshift($gui->turma_aluno, $template);

        $widget = new Widget();
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = $title;

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "educacionalturma";
        $tabs->form->action = _pasta . "actions.php?pagina=educacionalturma";

        // cria novo campo
        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::LABEL;
        $field->property = "handle";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "nome";
        $field->description = "Nome da turma";
        $field->property = "nome";
        $field->size = 4;
        $tabs->form->field[] = $field;
    }
}