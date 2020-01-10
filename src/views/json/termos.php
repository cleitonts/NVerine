<?php

use src\services\Transact\ExtPDO as PDO;

global $conexao;

// puxa dados de usuário
$sql = "SELECT * FROM K_DICIONARIO";
$stmt = $conexao->prepare($sql);
$stmt->execute();

$f = $stmt->fetchAll(PDO::FETCH_OBJ);
$arr = array();
foreach ($f as $r){
    $temp["handle"] = $r->HANDLE;
    $temp["original"] = utf8_encode($r->TERMO_ORIGINAL);
    $temp["novo"] = utf8_encode($r->TERMO_NOVO);
    $arr[] = $temp;
}
print_r(json_encode($arr));