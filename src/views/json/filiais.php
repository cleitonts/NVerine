<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 15/04/2019
 * Time: 10:36
 */

include_once("class/Empresa.php");

$filial = new Empresa\FilialGUI();
$filial->regiao = intval($_REQUEST["regiao"]);
$filial->fetch();

$arr = array();

foreach($filial->itens as $r){
    $atual = array();
    $atual["name"] = $r->nome;
    $atual["handle"] = $r->handle;

    // transforma encode
    $atual = array_map("utf8_encode", $atual);

    $arr[] = $atual;
}

print_r(json_encode($arr));