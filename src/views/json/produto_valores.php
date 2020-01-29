<?php

global $conexao;

// fallbacks
$tabela = 			!empty($_GET["tabela"]) ? $_GET["tabela"] : 1;
$filial =			!empty($_GET["filial"]) ? $_GET["filial"] : 1;
$pessoa = 			!empty($_GET["pessoa"]) ? $_GET["pessoa"] : null;
$forma =			!empty($_GET["forma"]) ? $_GET["forma"] : null;
$tipo_contrato = 	!empty($_GET["tipo_contrato"]) ? $_GET["tipo_contrato"] : null;
$tipo_movimento = 	!empty($_GET["tipo_movimento"]) ? $_GET["tipo_movimento"] : "S"; // contrato geralmente é saída?
$tipo_operacao =	$_GET["tipo_operacao"];

/* --------------------------------------------------------------------------------
 * consulta tipo de operação padrão
 */
if(empty($tipo_operacao)) {
	$chave_movimento = 	$tipo_movimento == "E" ? "ENTRADA" : "SAIDA";
	
	$sql = "SELECT P.K_TIPOMOV{$chave_movimento} MOVIMENTO
			FROM PD_PRODUTOS P
			WHERE P.CODIGO = :term";
	$stmt = $conexao->prepare($sql);
	$stmt->bindValue(":term", $_GET["term"]);
	$stmt->execute();
	$f = $stmt->fetch(PDO::FETCH_OBJ);
	
	$tipo_operacao = $f->MOVIMENTO;
}

/* --------------------------------------------------------------------------------
 * consulta produto
 */
$sql = "SELECT 	P.PRECOVENDA, 
				P.CODIGO,
				P.MEDIDA_X, 
				P.MEDIDA_Z, 
				P.MEDIDA_Y,
				P.K_ENDERECO AS ENDERECO,
				P.ALIQUOTASUBSTITUICAO AS MVA,
				M.ABREVIATURA,
				T.PORCENTAGEM AS DESCONTOTAB,
				N.CODIGONBM AS NCM,
				N.ALIQUOTA AS ALIQUOTAIPI,
				PE.LISTA,
				
				-- tipos de movimento
				MOV.HANDLE CODMOVIMENTO,
				MOV.CODIGO MOVIMENTO,
				MOV.CREDITAIPI,
				MOV.CREDITAICMS,
				MOV.ALIQUOTAPIS,
				MOV.ALIQUOTACOFINS,
				MOV.ALIQUOTAISSQN,
				CONCAT(MOV.CSTORIGEM, MOV.CSTTRIBUTACAO) CST,
				MOV.CSOSN,
				MOV.CFOP,
				MOV.CALCULOBASE,
				MOV.MODALIDADE,
				MOV.SUBSTITUICAOTRIB,
				MOV.CSTIPI,
				CFOP.NOME NOMECFOP
				
		FROM PD_PRODUTOS P
		LEFT JOIN K_FN_TABELAPRECOS T ON (P.CODIGO = T.PRODUTO AND T.INDICE = :tabela)
		LEFT JOIN TR_TIPIS N ON P.K_NCM = N.CODIGONBM
		LEFT JOIN CM_UNIDADESMEDIDA M ON P.UNIDADEMEDIDAVENDAS = M.HANDLE
		LEFT JOIN K_TIPOOPERACAO MOV ON MOV.HANDLE = :tipooperacao
		LEFT JOIN K_FN_CFOP CFOP ON MOV.CFOP = CFOP.CODIGO
		LEFT JOIN K_FN_PESSOA PE ON PE.HANDLE = :pessoa
		WHERE P.CODIGO = :term";

$stmt = $conexao->prepare($sql);
$stmt->bindValue(":term", $_GET["term"]);
$stmt->bindValue(":pessoa", $pessoa);
$stmt->bindValue(":tabela", $tabela);
$stmt->bindValue(":tipooperacao", $tipo_operacao);
$stmt->execute();
$f = $stmt->fetch(PDO::FETCH_OBJ);

$err = $stmt->errorInfo(); // para debug

/* --------------------------------------------------------------------------------
 * puxa lista de preços
 */
$lista_preco = null;
$filtro_ativo = " AND DATAFIM > '".converteData(hoje())."' ";

// primeiro pesquisa as globais ATIVO = 'S'
if(empty($f->LISTA)) {
    // se tiver várias listas de preço ativas, puxa a de menor valor primeiro
    $sql = "SELECT VALOR, INDICE FROM K_LISTAPRECO WHERE PRODUTO = :term AND ATIVO = 'S' {$filtro_ativo} ORDER BY VALOR ASC";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(":term", $_GET["term"]);
    $stmt->execute();
}
else{
	$sql = "SELECT VALOR, INDICE FROM K_LISTAPRECO WHERE PRODUTO = :term AND INDICE = :lista {$filtro_ativo}
			ORDER BY VALOR ASC"; // se tiver várias listas de preço ativas, puxa a de menor valor primeiro
	$stmt = $conexao->prepare($sql);
	$stmt->bindValue(":term", $_GET["term"]);
	$stmt->bindValue(":lista", $f->LISTA);
	$stmt->execute();
}
$lista_preco = $stmt->fetch(PDO::FETCH_OBJ);

/* --------------------------------------------------------------------------------
 * puxa objeto ICMS (e cadastros relacionados)
 */
$cliente = new \src\entity\PessoaGUI();
$cliente->top = "TOP 10"; // evita estouro de memória nessa query
$cliente->pesquisa["pesq_codigo"] = $pessoa;
$cliente->fetch();
$cliente = $cliente->itens[0];

$empresa = new \src\entity\FilialGUI();
$empresa->filial = $filial;
$empresa->fetch();
$empresa = $empresa->itens[0];

$icms = new \src\entity\FaturamentoICMSGUI();
$icms->uf_origem = $empresa->endereco->sigla_estado;
$icms->uf_destino = $cliente->endereco->sigla_estado;
$icms->tipo_cliente = $cliente->tipo;

/* --------------------------------------------------------------------------------
 * monta output JSON
 */
$json = array();

// proporção do preço/desconto
$desconto = 0;
$preco = $f->PRECOVENDA;

// usa lista de preço?
if(!empty($lista_preco->VALOR)) {
	$preco = $lista_preco->VALOR;
	$lista = $lista_preco->INDICE;
}

// usa tabela de preços?
elseif(!empty($f->DESCONTOTAB)) {
    $preco = $f->PRECOVENDA * ($f->DESCONTOTAB / 100);
}

// tipo de medida
if($f->ABREVIATURA == "M2" || $f->ABREVIATURA == "MT") {
	$json["medida_t"] = 0;
	$json["desativaMedidas"] = false;		
}
else {
	$json["medida_t"] = "";
	$json["desativaMedidas"] = true;	
}

// cfop interestadual (soma 1 no primeiro dígito)
$cfop = apenasNumeros($f->CFOP);
$cfop_inter = $cfop + 1000;

// sugere se operação é estadual ou não
if($empresa->endereco->sigla_estado == $cliente->endereco->sigla_estado) {
	$json["operacaoEstadual"] = true;
	$json["destinoNota"] = 1;
}
else {
	$json["operacaoEstadual"] = false;
	$json["destinoNota"] = 2;
}

// tributos
$json["codMovimento"] = $f->CODMOVIMENTO;
$json["cst"] = $f->CST;
$json["lista_indice"] = $lista;
$json["cstIpi"] = left($f->CST, 1).$f->CSTIPI;
$json["csosn"] = $f->CSOSN;
$json["cfop"] = $cfop;
$json["cfopInterestadual"] = $cfop_inter;
$json["cfopDescricao"] = formataCase($f->NOMECFOP);
$json["creditaIcms"] = $f->CREDITAICMS;				// essas variáveis não são mais usadas na venda
$json["creditaIpi"] = $f->CREDITAIPI;				// ||
$json["percIpi"] = $f->ALIQUOTAIPI;
$json["percIcms"] = $icms->getAliquota();
$json["percPis"] = $f->ALIQUOTAPIS;
$json["percCofins"] = $f->ALIQUOTACOFINS;
$json["percIssqn"] = $f->ALIQUOTAISSQN;
$json["fatorBCIcms"] = $f->CALCULOBASE / 100; 		// já transforma porcentagem em 0-1
$json["modalidadeBCIcms"] = $f->MODALIDADE;			// determina o cálculo do BC/ST
$json["usaSubstituicaoTributaria"] = $f->SUBSTITUICAOTRIB;
$json["margemValorAgregado"] = empty($f->MVA) ? 0 : (float) $f->MVA / 1000; // MVA tem ponto flutuante mas é salva como int * 1000

// dados do produto
$json["cod_produto"] = $f->CODIGO;
$json["quantidade"] = 1;
$json["unidade"] = str_replace("Ç", "C", $f->ABREVIATURA);
$json["medida_x"] = $f->MEDIDA_X;
$json["medida_z"] = $f->MEDIDA_Z;
$json["medida_y"] = $f->MEDIDA_Y;
$json["valorUnitario"] = formataValor($preco);
$json["percDesconto"] = $desconto;
$json["ncm"] = $f->NCM;
$json["endereco"] = $f->ENDERECO;

$json["debug"] = htmlspecialchars($err[2]);

$json = array_map("utf8_encode", $json);
print json_encode($json);
