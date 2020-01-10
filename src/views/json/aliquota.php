<?php
global $conexao;

if(empty($_GET["ncm"])){
    echo 0;
}

// puxa dados
$sql = "SELECT * FROM TR_TIPIS WHERE CODIGONBM = :ncm";

$stmt = $conexao->prepare($sql);
$stmt->bindValue(":ncm", $_GET["ncm"]);
$stmt->execute();

$f = $stmt->fetchAll(\src\services\Transact\ExtPDO::FETCH_OBJ);
echo $f[0]->ALIQUOTA;