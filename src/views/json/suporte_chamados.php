<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/08/2019
 * Time: 08:46
 */

$status = \src\entity\SuporteChamadoETT::getStatusKanban(0, true);
$chamados = new \src\entity\SuporteChamadoGUI();
$listas = array();

function moveElement(&$array, $pos_origem, $pos_final) {
    $out = array_splice($array, $pos_origem, 1);
    array_splice($array, $pos_final, 0, $out);
}


// pesquisa somente status do kanban
foreach ($status as $k => $v){
    $listas[insereZeros($k, 2) . " - " . $v] = array();
    $chamados->pesquisa["pesq_status_".$k] = true;
}
$chamados->setPesquisa();
$chamados->fetch();

$temp = array();
foreach ($chamados->itens as $k => $v){
    $arr = array();
    $arr["pos"] = $k;
    $arr["handle"] = $v->handle;
    $arr["after"] = $v->after;
    $temp[$v->handle] = $arr;
}

foreach ($temp as $k => $v){
    if($v["after"] > 0){
        $final = $temp[$v["after"]]["pos"];

        moveElement($chamados->itens, $v["pos"], $final);
    }
}

if(!empty($_REQUEST["pesq_chamado"])){
    $json = \src\creator\widget\Tools::toJson($chamados->itens[0]);
    print_r($json);
    return;
}

foreach ($chamados->itens as $r){
    $listas[$r->status][] = $r;
}

$listas = \src\creator\widget\Tools::toJson($listas);
print_r($listas);