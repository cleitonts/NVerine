<?php
global $conexao;

// pode pesquisae por três valores: código do produto, código alternativo e nome
if(is_numeric($_GET["term"]))
    $where = "(P.CODIGO = '".anti_injection($_GET["term"])."'";
else
    $where = "(P.NOME LIKE '".stringPesquisa(anti_injection($_GET["term"]))."'";

$where .= " OR P.CODIGOBARRAS = '".anti_injection($_GET["term"])."')";

// filtra por filial se compartilhado
$where .= " AND P.ATIVO = 'S' AND ".filtraFilial("P.K_KFILIAL", "Produto", false);

// puxa dados
$sql = "SELECT TOP 10 	P.HANDLE, P.NOME, P.DESCRICAO, P.CODIGO, P.CODIGOALTERNATIVO,
						P.K_ENDERECO AS ENDERECO, P.PRECOVENDA AS PRECO,
						U.ABREVIATURA, P.K_ESTRUTURADO ESTRUTURADO
		FROM			PD_PRODUTOS P
		LEFT JOIN		CM_UNIDADESMEDIDA U
		ON				P.UNIDADEMEDIDAVENDAS = U.HANDLE
		WHERE			{$where}";

$stmt = $conexao->prepare($sql);
$stmt->bindValue(":filial", $_GET["filial"]);
$stmt->execute();

$f = $stmt->fetchAll(PDO::FETCH_OBJ);

// mapeia json
$json = array();
$json_row = array();

foreach($f as $r) {
    $json_row["id"] = $r->CODIGO;
    $json_row["label"] = $r->CODIGO . " - " . $r->NOME;
    $json_row["value"] = $r->NOME;
    $json_row["unidade"] = $r->ABREVIATURA;
    $json_row["descricao"] = $r->DESCRICAO;
    $json_row["quantidadeSolicitada"] = 0; // re-implementar
    $json_row["preco"] = formataValor($r->PRECO);
    $json_row["endereco"] = $r->ENDERECO;
    $json_row["estruturado"] = $r->ESTRUTURADO;
    $json_row["codigoAlternativo"] = $r->CODIGOALTERNATIVO;
    $json_row["handle"] = $r->HANDLE;

    $json_row = array_map("utf8_encode", $json_row);
    array_push($json, $json_row);
}

print json_encode($json);
