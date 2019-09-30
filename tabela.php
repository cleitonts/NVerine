<?php
/*
 * ====================================
 * Gestão de Tudo/Mais Completo: início
 * ====================================
 * 
 * > como funciona:
 * 		Essa a pagina que verifica a hash da pesquisa de tabelas
 *		O retorno é sempre um JSON
 */
spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

include("loader.php");

global $conexao;
global $permissoes;
global $perfil;

// guarda timestamp inicial
$ts_inicio = time();

// atribui o offset
$conexao->pagina = intval($_REQUEST["pesq_pagina"]);

// instancia pesquisa
$gui = new $_REQUEST["class"]();
if(strpos($_REQUEST["class"], "CadastroGUI") !== false){
    $arr = explode("&", $_SERVER["HTTP_REFERER"]);

    foreach ($arr as $r){
        if(strpos($r, "tabela=") !== false){
            $nome = decrypt(substr($r, 7));
        }
    }
    $gui = new $_REQUEST["class"]($nome);
}
$gui->top = "TOP ".intval($_REQUEST["pesq_top"]);
$gui->setPesquisa();
$gui->fetch();

// retorna para o padrão
$conexao->pagina = null;

getTabela($gui);


// gera o json para as tabelas
function getTabela($gui){
    global $dumper;

	$gui = bubblesort($gui, $_REQUEST["ordena_por"]);
	$retorno = array();
	$header = $gui->header;

	// descobre o número de campos
	$max = count($header);

	// lista todos os campos
	foreach ($header as $r) {
		$retorno["header"][] = utf8_encode(strip_tags($r));
	}

	foreach($gui->itens as $r) {
		// descobre o número da linha
		$linha = $r->cont;
		$prod = array();

		// lista todos os itens
		for($coluna = 0; $coluna < $max; $coluna++) {
			$campo = $gui->getCampo($linha, $coluna);
			$prod["colunas"][] = utf8_encode(strip_tags($campo[0]));
			$prod["classes"][] = utf8_encode(strip_tags($campo[1]));
			$prod["handle"] = utf8_encode(strip_tags($r->handle));
			if(empty($prod["handle"])){
                $prod["handle"] = utf8_encode(strip_tags($r->HANDLE));
            }
            if(empty($prod["handle"])){
                $prod["handle"] = utf8_encode(strip_tags($r->cont));
            }
            if(empty($prod["handle"])){
                $prod["handle"] = utf8_encode(strip_tags($r->CONT));
            }
		}
		$retorno["itens"][] = $prod;
	}

    // faz o push dos prints com o retorno padrão
    if(!__DEVELOPER__) {
	    $dumper->dumped = "";
    }

    $json["dev_log"] = src\creator\widget\Tools::toUTF8($dumper->dumped);
    $json["render"] = $retorno;
    print_r(json_encode($json));
}