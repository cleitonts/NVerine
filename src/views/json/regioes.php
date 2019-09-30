<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 15/04/2019
 * Time: 11:26
 */

$sql = "SELECT * FROM K_REGIAO";
$stmt = $conexao->prepare($sql);
$stmt->execute();

$f = $stmt->fetchAll(PDO::FETCH_OBJ);

$json = array();

if(!empty($f)) {
    foreach($f as $r) {
        $json_row = array();
        $json_row["name"] = $r->NOME;
        $json_row["handle"] = $r->HANDLE;

        $json_row = array_map("utf8_encode", $json_row);
        $json[] = $json_row;
    }
}

print_r(json_encode($json));