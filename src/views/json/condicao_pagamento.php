<?php
global $conexao;

$sql = "SELECT * FROM CP_MAPACONDICAOPAGAMENTO WHERE CONDICOESPAGAMENTO = :term";
$stmt = $conexao->prepare($sql);
$stmt->bindValue(":term", $_GET["term"]);
$stmt->execute();

$f = $stmt->fetchAll(PDO::FETCH_OBJ);

$json = array();
$json_row = array();

// para o caso de vencimento fixo
$vencimento_anterior = $_GET["emissao"];
if(empty($vencimento_anterior)) $vencimento_anterior = hoje();
$dias = 0;

foreach($f as $r) {
	// se for vencimento fixo, tem que saber quantos dias tem entre o vencimento anterior e o atual
	if($r->DIAVENCIMENTO > 0) {
		$vencimento = proximoDia($r->DIAVENCIMENTO, $vencimento_anterior);		
		$intervalo = diasEntre($vencimento_anterior, $vencimento);
		$dias += $intervalo;
		
		$vencimento_anterior = $vencimento;
	}
	else {
		$dias = $r->DIAS;
	}
	
	$json_row["dias"] = $dias;
	$json_row["percentual"] = $r->PERCENTUAL;
	$json_row["prefixo"] = $r->K_PREFIXO;
	
	$json_row = array_map("utf8_encode", $json_row);
	array_push($json, $json_row);
}

print json_encode($json);
