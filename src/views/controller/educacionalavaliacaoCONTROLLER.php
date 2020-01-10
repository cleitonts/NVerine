<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 09/10/2019
 * Time: 12:02
 */

namespace src\views\controller;


use src\creator\widget\Tools;
use src\entity\EducacionalAvaliacaoETT;
use src\entity\EducacionalBoletimETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\educacionalavaliacaoFORM;


global $__MODULO__;
global $__PAGINA__;

$__MODULO__ = 'Educacional';
$__PAGINA__ = 'Avaliações';


class educacionalavaliacaoCONTROLLER implements ControladoraCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new educacionalavaliacaoFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new educacionalavaliacaoFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=educacionalavaliacao';

        if (empty($obj->handle)) {
            $obj->cadastra();
        } else {
            $obj->atualiza();

        }

        // cadastra as notas
        foreach ($_REQUEST["grade_horaria"] as $k => $r) {
            if (!empty($r["nota_" . $k]) || !empty($r["nota_revisao_" . $k])) {

                $avaliacao = new EducacionalBoletimETT();
                $avaliacao->cod_aluno = $r["cod_aluno_" . $k];
                $avaliacao->cod_avaliacao = $obj->handle;
                $avaliacao->nota = str_replace(",", ".", $r["nota_" . $k]);
                $avaliacao->nota_revisao = str_replace(",", ".", $r["nota_revisao_" . $k]);

                if (empty($r["handle_" . $k])) {
                    $avaliacao->cadastra();
                } else {
                    $avaliacao->handle = $r["handle_" . $k];
                    $avaliacao->atualiza();
                }
            }
        }
    }
}