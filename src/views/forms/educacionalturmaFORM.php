<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 18/04/2019
 * Time: 08:27
 */

namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\FormTable;
use src\creator\widget\FormTableRow;
use src\creator\widget\Header;
use src\creator\widget\HeaderMenuItem;
use src\creator\widget\Options;
use src\creator\widget\Table;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\AgendaETT;
use src\entity\EducacionalGradeHorariaETT;
use src\entity\EducacionalSerieGUI;
use src\entity\EducacionalTurmaETT;
use src\entity\EducacionalTurmaGUI;
use src\entity\EducacionalTurmaListaETT;
use src\services\Transact;
use src\views\ControladoraFORM;

class educacionalturmaFORM implements ControladoraFORM
{
    public function createSearch()
    {
        $widget = new Widget();
        //$widget->includes[] = "src/public/js/faturamento/exportacao.js?";
        $widget->header->title = "Turma";
        $widget->header->icon = "fa fa-book";

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Livro de matrículas";
        $menu->function = "destinoMenu('educacional_matricula&retorno=".urlencode(getUrlRetorno("index2.php"))."')";
        $menu->icon = "fa fa-list";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Listagem de turmas(sintético)";
        $menu->function = "window.open('relatorio.php?pagina=educacional_turma_sintetico=".urlencode(getUrlRetorno("index2.php"))."', '_blank')";
        $menu->icon = "fa fa-university";
        $widget->header->menu[] = $menu;

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        // cria form
        $tabs->form->method = "GET";
        $tabs->form->prefix = "pesq_";
        $tabs->form->name = "form_pesquisa";
        $tabs->form->action = "?pagina=educacionalturma";

        // cria novo campo
        $arr_anos = range(2010, 2025);
        array_unshift($arr_anos, "");
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "pesq_ano";
        $field->description = "Ano";
        $field->options = Options::byArray($arr_anos, $arr_anos);
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "pesq_vigente";
        $field->description = "Vigente";
        $field->options[] = new Options("1", "Sim");
        $field->options[] = new Options("2", "Não");
        $field->size = 4;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = Fields::fromTable(Fields::SELECT, 4, "pesq_professor", "K_FN_PESSOA", "NOME", "HANDLE", "WHERE PROFESSOR = 'S'");
        $field->description = "Professor";
        $tabs->form->field[] = $field;

        Tools::footerSearch($tabs->form, 6);

        // cria tabelas
        $tabs->table = new Table();
        $tabs->table->name = "educacionalturma";
        $tabs->table->target = "?pagina=educacionalturma";
        $tabs->table->entity = EducacionalTurmaGUI::class;               // passar a classe/entidade para invocar
        $widget->body->tabs["pesquisar"] = $tabs; // colocar o nome da tab

        $tab = new Tabs();
        $tab->function = "destinoMenu('educacional_turma_relatorios&retorno=".urlencode(getUrlRetorno("index2.php"))."')";
        $tab->icon = "fa fa-bar-chart";

        $widget->body->tabs["Relatórios"] = $tab;

        $tab = new Tabs();
        $tab->function = "Tools.redirect('?pagina=educacionalturma&pesq_num=0')";
        $tab->icon = "fa fa-plus";

        $widget->body->tabs["inserir"] = $tab;

        $widget->setDefaults();
        return $widget;
    }

    public function createForm($handle = null)
    {
        // se chegar null é pq eu quero a instancia da entidade somente 1x
        if($handle == 0){
            // instancia a entidade
            $gui = new EducacionalTurmaETT();
            $title = "Nova turma";
        }
        else {
            // instancia a entidade
            $gui = new EducacionalTurmaGUI($handle);
            $gui->setPesquisa();
            $gui->fetch();
            $gui = $gui->itens[0];
            //$gui->handle = $handle;

            $title = "Turma #".$gui->handle;
        }

        if(empty($gui)){
            return Tools::returnError("Registro não encontrado.");
        }

        // template para criação de tabela dinamica
        $template = new EducacionalTurmaListaETT();
        array_unshift($gui->turma_aluno, $template);

        $widget = new Widget();
        $widget->includes[] = "src/public/js/educacional/turma.js";
        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = $title;
        $widget->header->icon = "fa fa-book";

        $widget->header->menu[] = new HeaderMenuItem("Duplicar turma", "duplica()", "fa fa-copy");

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Diário de classe (relatório)";
        $menu->function = "window.open('index.php?pagina=educacional_presenca&pesq_turma={$gui->handle}&pesq_aula_1=1', '_blank')";
        $menu->icon = "fa fa-print";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Livro de matrículas (relatório)";
        $menu->function = "window.open('relatorio.php?pagina=educacional_matricula&pesq_turma={$gui->handle}&agrupa_por=3&totaliza=nao', '_blank')";
        $menu->icon = "fa fa-print";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Ata de fechamento (relatório)";
        $menu->function = "window.open('relatorio.php?pagina=educacional_ata&pesq_turma={$gui->handle}', '_blank')";
        $menu->icon = "fa fa-check";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Familiares (relatório)";
        $menu->function = "window.open('relatorio.php?pagina=educacional_familiares&pesq_turma={$gui->handle}&agrupa_por=3&totaliza=nao', '_blank')";
        $menu->icon = "fa fa-group";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Certificado (relatório)";
        $menu->function = "window.open('relatorio.php?pagina=educacional_certificado&pesq_turma={$gui->handle}', '_blank')";
        $menu->icon = "fa fa-certificate";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Avaliações (relatório parcial)";
        $menu->function = "window.open('relatorio.php?pagina=educacional_notas&pesq_turma={$gui->handle}&agrupa_por=nao&totaliza=nao', '_blank')";
        $menu->icon = "fa fa-file-o";
        $widget->header->menu[] = $menu;

        // submenu interno
        $menu = new HeaderMenuItem();
        $menu->description = "Censo escolar";
        $menu->function = "window.open('index.php?pagina=educacional_censo&pesq_turma={$gui->handle}', '_blank')";
        $menu->icon = "fa fa-clipboard";
        $widget->header->menu[] = $menu;


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

        // cria novo campo
        $field = Fields::fromTable(Fields::SELECT, 3, "cod_filial", "K_FN_FILIAL", "NOME", "HANDLE", "", "cod_filial");
        $field->description = "Unidade";
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = Fields::fromTable(Fields::SELECT, 3, "serie", "K_SERIE", "NOME", "HANDLE", "", "serie");
        $field->description = "Serie";
        $tabs->form->field[] = $field;

        // cria novo campo
        $array_turno = EducacionalTurmaETT::getTurno("",true);
        array_unshift($array_turno, "");

        $array_turno_abreviado = EducacionalTurmaETT::getTurno("",true, true);
        array_unshift($array_turno_abreviado, "");

        $field = new Fields();
        $field->type = $field::SELECT;
        $field->description = "Turno";
        $field->name = "cod_turno";
        $field->property = "cod_turno";
        $field->options = Options::byArray(array_values($array_turno_abreviado), array_values($array_turno));
        $field->size = 2;
        $tabs->form->field[] = $field;

        $periodo = EducacionalTurmaETT::getPeriodo();

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "periodo";
        $field->description = "Período letivo";
        $field->property = "periodo";
        $field->options = Options::byArray($periodo["handle"], $periodo["nome"]);
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "divisao_periodo";
        $field->description = "Divisão período";
        $field->property = "divisao_periodo";
        $field->options[] = new Options("", "");
        $field->options[] = new Options(AgendaETT::DIV_BIMESTRE, AgendaETT::getNomeEvento(AgendaETT::DIV_BIMESTRE));
        $field->options[] = new Options(AgendaETT::DIV_TRIMESTRE, AgendaETT::getNomeEvento(AgendaETT::DIV_TRIMESTRE));
        $field->size = 3;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "min_alunos";
        $field->description = "Mínimo de alunos";
        $field->property = "min_alunos";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "max_alunos";
        $field->description = "Máximo de alunos";
        $field->property = "max_alunos";
        $field->size = 2;
        $tabs->form->field[] = $field;

        // instancia a tabela dinamica
        $table = new FormTable("turma_aluno");
        $table->after = "max_alunos";
        $table->view = $table::TABLE_DYNAMIC;
        $table->delete_block = true;

        foreach ($gui->turma_aluno as $key => $r) {
            $row = new FormTableRow();
            $row->entity = $r;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "handle";
            $field->property = "handle";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "numero";
            $field->property = "numero";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "Número";
            $field->property = "numero";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "aluno";
            $field->description = "Aluno";
            $field->property = "aluno";
            $field->class = "larger";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::HIDDEN;
            $field->name = "cod_aluno";
            $field->property = "cod_aluno";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::LABEL;
            $field->name = "cod";
            $field->description = "Cód";
            $field->value = $r->cod_aluno;
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Ativo";
            $field->name = "ativo";
            $field->property = "ativo";
            $field->options[] = new Options("S", "SIM");
            $field->options[] = new Options("N", "NÃO");
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "data_entrada";
            $field->description = "Data Entrada";
            $field->class = "datepicker-date";
            $field->property = "data_entrada";
            $row->field[] = $field;

            $field = new Fields();
            $field->type = $field::TEXT;
            $field->name = "data_saida";
            $field->description = "Data saida";
            $field->class = "datepicker-date";
            $field->property = "data_saida";
            $row->field[] = $field;

            // cria novo campo
            $field = new Fields();
            $field->type = $field::SELECT;
            $field->description = "Turno";
            $field->name = "turno";
            $field->property = "turno";
            $field->options = Options::byArray(array_keys($array_turno), array_values($array_turno));
            $row->field[] = $field;

            $table->rows[] = $row;
        }

        $tabs->form->table[] = $table;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Turma"] = $tabs; // colocar o nome da tab

        // =============================================================================================================
        $tabs = new Tabs();
        $tabs->icon = "fa fa-calendar";

        // copia o form anterior
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "educacionalturma"; // se tiver o mesmo nome ele interpreta como sendo o mesmo form
        $tabs->form->action = _pasta . "actions.php?pagina=educacionalturma";

        $disciplinas = Fields::fromTable(Fields::SELECT, 3, "cod_disciplina", "K_DISCIPLINA", "NOME", "HANDLE", "", "cod_disciplina");
        $disciplinas->description = "Disciplina";
        $disciplinas->class = "larger";

        $professores = Fields::fromTable(Fields::SELECT, 3, "cod_professor", "K_FN_PESSOA", "NOME", "HANDLE", "WHERE PROFESSOR = 'S'", "cod_professor");
        $professores->description = "Professor";
        $professores->class = "larger";

        $title = "Grade horária da turma #".$gui->handle;

        // template para criação de tabela dinamica
        $template = new EducacionalGradeHorariaETT();
        array_unshift($gui->grade_horaria, $template);

        $dias_semana = 1;
        while ($dias_semana <= 7) {
            // cria novo campo
            $field = new Fields();
            $field->description = "Dia da semana";
            $field->name = "anchordia" . $dias_semana;
            $field->type = $field::LABEL;
            $field->value = EducacionalGradeHorariaETT::getNomeDiaSemana($dias_semana);
            $field->size = 12;
            $tabs->form->field[] = $field;

            // instancia a tabela dinamica
            $table = new FormTable("grade_horaria[{$dias_semana}]");
            $table->after = "anchordia" . $dias_semana;
            $table->name = "dia".$dias_semana;
            $table->view = $table::TABLE_DYNAMIC;
            $table->delete_block = false;

            // itera cada um dos itens
            foreach ($gui->grade_horaria as $key => $r) {
                if ($r->cod_dia_semana == $dias_semana || empty($r->dia_semana)) {
                    $row = new FormTableRow();
                    $row->entity = $r;

                    $field = new Fields();
                    $field->type = $field::HIDDEN;
                    $field->name = "cod_dia_semana";
                    $field->size = 3;
                    $field->value = $dias_semana;
                    $row->field[] = $field;

                    $field = new Fields();
                    $field->type = $field::HIDDEN;
                    $field->name = "handle";
                    $field->property = "handle";
                    $field->size = 3;
                    $row->field[] = $field;

                    $field = new Fields();
                    $field->type = $field::TEXT;
                    $field->name = "horário início";
                    $field->class = "datepicker-time";
                    $field->property = "horario_inicio";
                    $field->size = 3;
                    $row->field[] = $field;

                    $field = new Fields();
                    $field->type = $field::TEXT;
                    $field->name = "Horário término";
                    $field->class = "datepicker-time";
                    $field->property = "horario_termino";
                    $field->size = 3;
                    $row->field[] = $field;

//                    $field = new Fields();
//                    $field->type = $field::SELECT;
//                    $field->name = "Disciplina";
//                    $field->property = "cod_disciplina";
//                    $field->options = Options::byArray($disciplinas_values, $disciplinas_description);
//                    $field->size = 3;
//                    $row->field[] = $field;

                    // cria novo campo
                    $disciplinas = Fields::fromTable(Fields::SELECT, 3, "cod_disciplina", "K_DISCIPLINA", "NOME", "HANDLE", "", "cod_disciplina", "Disciplina");
                    $disciplinas->class = "larger";
                    $row->field[] = $disciplinas;

                    // cria novo campo
                    $row->field[] = $professores;

                    $table->rows[] = $row;
                }
            }
            $tabs->form->table[] = $table;
            $dias_semana++;
        }

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->class = "float-right mt-3";
        $tabs->form->field[] = $field;

        $widget->body->tabs["Grade horária"] = $tabs; // colocar o nome da tab

        // =============================================================================================================
        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar()";
        $widget->body->tabs["Retornar"] = $tabs;

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;
    }

}