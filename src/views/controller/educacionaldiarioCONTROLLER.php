<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/06/2019
 * Time: 14:54
 */

namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

use src\creator\widget\Tools;
use src\entity\EducacionalAulaETT;
use src\entity\EducacionalDiarioClasseETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\educacionaldiarioFORM;


$__MODULO__ = 'Educacional';
$__PAGINA__ = 'Diário';

class educacionaldiarioCONTROLLER implements ControladoraCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new educacionaldiarioFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new educacionaldiarioFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=educacionaldiario';
        $i = 0;
        // cadastra conteudos
        foreach ($_REQUEST["conteudo"] as $k => $v){
            if(is_int($k)){
                // as vezes por cauda das varias trocas de campo,
                // o num pode vir muito alto e sempre diferente do form anterior
                if(!isset($c)){
                    $c = $k;
                }
                $aula = new EducacionalAulaETT();
                //$aula->horario
                $aula->data = converteData($_REQUEST["data_diario"]);
                $aula->professor = $v["cod_professor_{$k}"];
                $aula->conteudo = $v["conteudo_{$k}"];
                $aula->horario = $_REQUEST["turma_aluno"][$i]["cod_horario_{$i}"];
                $aula->cadastra();
                $c++;
                $i++;
            }
        }

        $c = $c - 1; // consera o contador de materias
        $i = 0;
        // cadastra as presenças e faltas
        foreach ($_REQUEST["turma_aluno"] as $r){
            $copy = $c;
            while($copy >= 0){
                $presenca = new EducacionalDiarioClasseETT();
                $presenca->data = converteData($_REQUEST["data_diario"]);
                $presenca->horario = $_REQUEST["turma_aluno"][$copy]["cod_horario_{$copy}"];
                $presenca->cod_aluno = $r["cod_aluno_{$i}"];
                $presenca->presenca = isset($r["falta"][$copy])? "S" : "N" ;
                $presenca->historico = $r["observacoes_{$i}"];
                $presenca->cadastra();
                $copy--;
            }
            $i++;
        }
    }
}