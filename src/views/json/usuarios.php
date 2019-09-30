<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/08/2019
 * Time: 16:35
 */

use ExtPDO as pdo;

global $conexao;

// puxa dados de usuário
$sql = "SELECT U.*,
				G.NOME AS NOMEGRUPO, G.PAGINAINICIAL,
				P.NOME AS NOMECLIENTE
				FROM K_PD_USUARIOS U
				LEFT JOIN K_FN_GRUPOUSUARIO G ON U.GRUPO = G.HANDLE
				LEFT JOIN K_FN_PESSOA P ON U.CLIENTE = P.HANDLE";
$stmt = $conexao->prepare($sql);
$stmt->execute();

$f = $stmt->fetchAll(PDO::FETCH_OBJ);

$arr = array();
foreach ($f as $r){
    $temp["handle"] = $r->HANDLE;
    $temp["nome"] = $r->NOME;
    if($r->NOMEGRUPO != "Inativo"){
        $arr[] = $temp;
    }
}

print_r(\src\creator\widget\Tools::toJson($arr));
