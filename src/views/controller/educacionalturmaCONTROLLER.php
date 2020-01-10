<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 18/04/2019
 * Time: 08:28
 */

namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Educacional';
$__PAGINA__ = 'Turma';

use src\creator\widget\Tools;
use src\entity\EducacionalGradeHorariaETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\educacionalturmaFORM;

class educacionalturmaCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        $pesquisa = new educacionalturmaFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new educacionalturmaFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=educacionalturma';

        if(empty($obj->handle)){
            $obj->cadastra();
        }else{
            $obj->atualiza();
        }

        // primeiro atualiza o cadastro de alunos
        if(!empty($obj->turma_aluno)){
            $i = 1;
            foreach($obj->turma_aluno as $d){
                // salva a serie como referencia
                $d->turma = $obj->handle;
                if(empty($d->numero)) {
                    $d->numero = $i;
                }
                if(empty($d->handle)) {
                    $d->cadastra();
                }
                else{
                    $d->atualiza();
                }
                $i++;
            }
        }

        // atualiza o cadastro

        if(!empty($_REQUEST["grade_horaria"])){
            $obj->grade_horaria = array();
            $grade = new EducacionalGradeHorariaETT();
            $grade->cod_turma = $obj->handle;
            $grade->remove();
        }

        foreach ($_REQUEST["grade_horaria"] as $d){
            $i = 0;
            foreach ($d as $r){
                if($r["existe_".$i] == 1) {
                    $grade = new EducacionalGradeHorariaETT();
                    $grade->horario_inicio = $r["horario_inicio_" . $i];
                    $grade->horario_termino = $r["horario_termino_" . $i];
                    $grade->cod_dia_semana = $r["cod_dia_semana_" . $i];
                    $grade->cod_disciplina = $r["cod_disciplina_" . $i];
                    $grade->cod_professor = $r["cod_professor_" . $i];
                    $grade->cod_turma = $obj->handle;
                    $grade->cadastra();
                }
                $i++;
            }
        }
    }
}