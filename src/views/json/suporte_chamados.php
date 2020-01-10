<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/08/2019
 * Time: 08:46
 */

$status = \src\entity\SuporteChamadoETT::getNomeStatus(0, true);
$chamados = new \src\entity\SuporteChamadoGUI();
$listas = array();

// pesquisa somente status do kanban
foreach ($status as $k => $v){
    $listas[insereZeros($k, 2) . " - " . $v] = array();
    $chamados->pesquisa["pesq_status_".$k] = true;
}

$chamados->setPesquisa();
$chamados->fetch();

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