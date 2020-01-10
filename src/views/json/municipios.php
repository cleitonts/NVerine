<?php
global $conexao;
$term = $_GET["term"];

// puxa lista de países se for exterior
if($term == 48) {
	$sql = "SELECT * FROM PAISES ORDER BY NOME";
	$stmt = $conexao->prepare($sql);
}
// puxa municípios do estado por sigla
elseif(!is_numeric($term)) {
	$sql = "SELECT M.* FROM MUNICIPIOS M LEFT JOIN ESTADOS E ON M.ESTADO = E.HANDLE
			WHERE E.SIGLA = :term
			ORDER BY M.NOME";
	$stmt = $conexao->prepare($sql);
	$stmt->bindValue(":term", $term);
}
// puxa municípios do estado por handle
else {
	$sql = "SELECT * FROM MUNICIPIOS WHERE ESTADO = :term ORDER BY NOME";
	$stmt = $conexao->prepare($sql);
	$stmt->bindValue(":term", $term);
}

$stmt->execute();
$f = $stmt->fetchAll(PDO::FETCH_OBJ);

$json = array();
$json_row = array();

foreach($f as $r) {
	$json_row["value"] = $r->HANDLE;
	$json_row["label"] = $r->NOME;
	
	if(!empty($r->CODIGOBANCOCENTRAL)) $json_row["label"] .= " ** {$r->CODIGOBANCOCENTRAL}";
	
	$json_row = array_map("utf8_encode", $json_row);
	array_push($json, $json_row);
}

print json_encode($json);
