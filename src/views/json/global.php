<?php
/*******************
 * pesquisa global *
 *******************/

global $conexao;

$term = trim($_GET["term"]);
$labels = array();
$values = array();
$top = 10;
//
//// =================================================================================================================
//// pesquisa financeiro
//$sql = "SELECT TOP {$top} F.HANDLE, F.NATUREZA, F.NUMERODOC
//		FROM K_FINANCEIRO F
//		WHERE F.NUMERODOC LIKE :term
//		AND ".filtraFilial("F.FILIAL", "Financeiro");
//$stmt = $conexao->prepare($sql);
//$stmt->bindValue(":term", stringPesquisa($term));
//$stmt->execute();
//$f = $stmt->fetchAll(PDO::FETCH_OBJ);
//
//if(!empty($f)) {
//	foreach($f as $r) {
//		$labels[] = "Financeiro: {$r->NUMERODOC}";
//		$values[] = "?pagina=contabil_titulos&pesq_titulo={$r->HANDLE}&pesq_natureza={$r->NATUREZA}&retorno=".urlencode(_pasta);
//	}
//}
//
//// =================================================================================================================
//// pesquisa nota
//$sql = "SELECT TOP {$top} N.HANDLE, N.NUMORCAMENTO, N.NUMNOTA, S.NOME
//		FROM K_NOTA N
//		LEFT JOIN K_STATUS S ON N.STATUS = S.HANDLE
//		WHERE (N.NUMORCAMENTO = :term OR N.NUMNOTA = :term)
//		AND N.FATURA = 'S' AND ".filtraFilial("N.FILIAL", "Faturamento");
//$stmt = $conexao->prepare($sql);
//$stmt->bindValue(":term", $term);
//$stmt->execute();
//$f = $stmt->fetchAll(PDO::FETCH_OBJ);
//
//if(!empty($f)) {
//	foreach($f as $r) {
//		if(empty($r->NUMNOTA)) $r->NUMNOTA = "sem n�mero";
//		$labels[] = "{$r->NOME}: {$r->NUMORCAMENTO} (NF {$r->NUMNOTA})";
//		$values[] = "?pagina=faturamento&nota={$r->HANDLE}&retorno=".urlencode(_pasta);
//	}
//}

// =================================================================================================================
// pesquisa pessoa
$sql = "SELECT TOP {$top} P.NOME, P.HANDLE, P.ALUNO,
		F.NOME AS FILIAL
		FROM K_FN_PESSOA P, K_FN_FILIAL F
		WHERE P.FILIAL = F.HANDLE
		AND (P.NOME LIKE :term OR P.NOMEFANTASIA LIKE :term) AND P.ATIVO = 'S' AND ".filtraFilial("P.FILIAL", "Pessoas");
$stmt = $conexao->prepare($sql);
$stmt->bindValue(":term", stringPesquisa($term));
$stmt->execute();
$f = $stmt->fetchAll(PDO::FETCH_OBJ);

if(!empty($f)) {
	foreach($f as $r) {
		if($r->ALUNO == "S") {
			$labels[] = "Aluno: ".formataCase($r->NOME, true);
			$values[] = "?pagina=educacional_aluno&pesq_codigo={$r->HANDLE}";
		}
		else {
			$labels[] = "Pessoa: ".formataCase($r->NOME, true)." ({$r->FILIAL})";
			$values[] = "?pagina=pessoa&pesq_num={$r->HANDLE}";
		}
	}
}

// =================================================================================================================
// pesquisa produto
$gui = new \src\entity\ProdutoGUI();
$gui->top = "TOP {$top}";
$gui->pesquisa["pesq_nome"] = $term;
$gui->fetch();

if(!empty($gui->itens)) {
	foreach($gui->itens as $r) {
		$labels[] = "Produto: [{$r->codigo}] ".formataCase($r->nome, true);
		$values[] = "?pagina=produto&pesq_num={$r->codigo}";
	}
}

// =================================================================================================================
// pesquisa p�ginas
if(strlen($term) >= 3) {
	$i = 0;
	
	foreach($_SESSION["menu_itens"] as $menu) {
		if((stripos("{$menu["modulo"]} {$menu["titulo"]}", $term) !== false) && ($i < $top)) {
			$labels[] = "P�gina: {$menu["modulo"]} / {$menu["titulo"]}";
			$values[] = "?pagina={$menu["link"]}";
			$i++;
		}
	}
}

// =================================================================================================================
// monta retorno
$json = array();

foreach($labels as $key => $value) {
	$json_row = array();
	$json_row["label"] = $labels[$key];
	$json_row["value"] = $labels[$key];
	$json_row["url"] = $values[$key];
	
	$json_row = array_map("utf8_encode", $json_row);
	array_push($json, $json_row);
}

print json_encode($json);
