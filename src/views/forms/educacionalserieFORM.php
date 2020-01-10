<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 27/03/19
 * Time: 11:57
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
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\EducacionalSerieETT;
use src\entity\EducacionalSerieGUI;
use src\entity\EducacionalSerieMateriasETT;
use src\views\ControladoraFORM;

class educacionalserieFORM implements ControladoraFORM
{
    /**
     * @return Widget
     */
    public function createSearch()
    {
        $widget = new Widget();
        $widget->includes[] = "src/public/js/faturamento/exportacao.js?bosta";
        $widget->header->title = "Série";
        $widget->header->icon = "fa fa-book";

        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";
        $widget->body->tabs[""] = $tabs;

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "educacionalserie";
        $tabs->table->target = "?pagina=educacionalserie";
        $tabs->table->entity = EducacionalSerieGUI::class;               // passar a classe/entidade para invocar

        $widget->body->tabs["PESQUISAR"] = $tabs; // colocar o nome da tab
        $tab = new Tabs();
        $tab->function = "Tools.redirect('?pagina=educacionalserie&pesq_num=0')";
        $tab->icon = "fa fa-plus";

        $widget->body->tabs["inSeRir"] = $tab;



        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {

        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            // instancia a entidade
            $gui = new EducacionalSerieETT();
        }
        else {
            // instancia a entidade
            $gui = new EducacionalSerieGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
        }
        if(empty($gui)){
            return Tools::returnError("Registro não encontrado.");
        }

        $disciplinas = Fields::fromTable(Fields::SELECT, 3, "Componentes Curriculares", "K_DISCIPLINA", "NOME", "HANDLE", "", "componente_curricular");
        $disciplinas->description = "Disciplina";
        $disciplinas->class = "larger";

        // template para criação de tabela dinamica
        $template = new EducacionalSerieMateriasETT();
        array_unshift($gui->materias, $template);

        $widget = new Widget();
        //$widget->includes[] = "https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.js";
        $widget->includes[] = "src/public/js/educacional/educacionalSerie.js";
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Série: #{$gui->handle}";
        $widget->header->icon = "fa fa-book";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "educacionalserie";
        $tabs->form->action = _pasta . "actions.php?pagina=educacionalserie";

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::HIDDEN;
        $field->property = "handle";
        $tabs->form->field[] = $field;

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::LABEL;
        $field->property = "handle";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Nome do ano Escolar";
        $field->property = "nome";
        $field->size = 3;
        $tabs->form->field[] = $field;

        $array_ciclo = EducacionalSerieETT::getNomeCiclo("0",true);
        $array_etapa = range(0, count($array_ciclo));

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Ciclo escolar";
        $field->name = "ciclo_etapa";
        $field->property = "ciclo_etapa";
        $field->options = Options::byArray($array_etapa, $array_ciclo);
        $field->size = 3;
        $tabs->form->field[] = $field;

        $array_idade = EducacionalSerieETT::getFaixaIdade("0",true);
        $array_etapa_idade = range(0, count($array_idade));

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "faixa_etaria";
        $field->description = "Faixa Etária";
        $field->property = "faixa_etaria";
        $field->options = Options::byArray($array_etapa_idade, $array_idade);
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "Nota aprovação";
        $field->property = "nota_aprovacao";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // instancia a tabela dinamica
        $table = new FormTable("materias");
        $table->after = "nota_aprovacao";
        $table->view = $table::TABLE_DYNAMIC;
        $table->delete_block = false;

        foreach ($gui->materias as $key => $r) {
            $row = new FormTableRow();
            $row->entity = $r;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->property = "handle";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "Código";
            $field->property = "codigo";
            $row->field[] = $field;

            $row->field[] = clone $disciplinas;

//            $field = new Fields();
//            $field->type = $field::TEXT;
//            $field->description = "Carga horária(h)";
//            $field->name = "carga_horaria";
//            $field->property = "carga_horaria";
//            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Máscara de Nota";
            $field->name = "mascara_nota";
            $field->property = "mascara_nota";
            $field->options[] = new Options("", "");
            $field->options[] = new Options("N", "NUMERICA");
            $field->options[] = new Options("A", "ALFABETICA");
            $row->field[] = $field;

            $array_base_nacional = EducacionalSerieMateriasETT::getBaseNacionalComum("0",true);
            $array_base_nacional_id = range(0, count($array_base_nacional) - 1);

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->name = "Base curricular";
            $field->property = "base_curricular";
            $field->options = Options::byArray($array_base_nacional_id, $array_base_nacional);
            $row->field[] = $field;

            $area_conhecimento = EducacionalSerieMateriasETT::getAreaConhecimento("0",true);
            $area_conhecimento_id = range(0, count($area_conhecimento) - 1);
            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Área de conhecimento";
            $field->name = "area_conhecimento";
            $field->property = "area_conhecimento";
            $field->options = Options::byArray($area_conhecimento_id, $area_conhecimento);
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar()";

        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;

    }
}