<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 15/04/2019
 * Time: 10:36
 */

$turma = new src\entity\EducacionalTurmaGUI();
$turma->pesquisa["pesq_filial"] = $_REQUEST["escola"];
$turma->fetch();

$arr = array();
foreach($turma->itens as $r){
    $atual = array();
    $atual["name"] = $r->nome;
    $atual["handle"] = $r->handle;

    $atual = array_map("utf8_encode", $atual);

    $arr[] = $atual;
}

print_r(json_encode($arr));