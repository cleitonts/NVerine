<?php
/*
 * ========================================================================
 * Relatórios: template minimalista amigável a impressão e ao parser de PDF
 * ========================================================================
 */

include_once("includes/functions.php");
include_once("includes/conexao.php");
include_once("class/Permissoes.php");
include_once("class/Dicionario.php");
include_once("class/Perfil.php");
include_once("class/Login.php");

// verifica se tem hash antes
if(empty($_REQUEST["hash"])) {
    // tenta relogar com cookie
    if (!isset($_SESSION["ID"])) {
        $login = new Login();
        $login->relogar();
    }

    // valida login no front-end
    if (!isset($_SESSION["ID"])) {
        include("index.php");
        return;
    }
}

// valida a hash
else{
    $url = explode("&hash", "http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]);
    $hash = sha1($url[0]);
    if($hash != $_REQUEST["hash"]) {
        headerMensagem();
        mensagemErro("Bloqueio de segurança: hash informado difere do calculado");
        die();
    }
}

// não deixa conexão cair?
set_time_limit(300);

// puxa as permissões do usuário logado (hahaha nope)
$permissoes = new PermissoesDummy();

// puxa as informações de perfil
$perfil = new Perfil();
$perfil->fetch();

// puxa traduções de termos
$dicionario = new Traducao\Dicionario();

// passa parâmetros de get
if(isset($_REQUEST["pagina"])) $param = "?".$_SERVER["QUERY_STRING"];

// passa itens de session
$session = 	"&filial=".__FILIAL__.
			"&sistema=".__SISTEMA__.
			"&ver_empresa=".$_SESSION["ver_empresa"];

// monta url de retorno
if(file_exists("includes/{$_REQUEST['pagina']}.php")) {
	$retorno = _pasta.$param;
}
else {
	$retorno = null;
}

// guarda timestamp inicial
$ts_inicio = time();
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Relatórios - <?=__NOME_SISTEMA__?></title>
	<link rel="shortcut icon" href="imagens/favicon.png" />
	<!-- WebFonts -->
	<link rel="stylesheet" href="css/fonts/fonts.css">
	<!-- Font Awesome -->
	<link href="css/<?=FONT_AWESOME?>/css/font-awesome-old.min.css" rel="stylesheet">
	<link href="css/<?=FONT_AWESOME?>/css/font-awesome.css" rel="stylesheet">
	<!-- CSS próprio -->
	<link rel="stylesheet" href="ui2/css/style.css">
	<link rel="stylesheet" href="css/relatorio.css">
	<!-- jQuery -->
	<script src="js/jquery-ui/jquery-1.9.1.js"></script>
	<script src="js/pdf.js"></script>
</head>
<body style="background: #fff !important;">		
	<!-- cabeçalho -->		
	<div class="botoes_relatorio typo" id="cabecalho_relatorio">
		<!--<div class="logo-relatorios"></div>-->
		
		<?php if(!empty($_REQUEST["exporta_csv_1"])) { ?>
		<a class="btn btn-submit" href="<?=_pasta?>uploads/relatorio.csv" target="_blank"><i class="i icon-save"> Seu arquivo CSV está pronto! Clique aqui para baixar</i></a>
		<?php } ?>
		<a href="#" class="btn-relatorio" onclick="orientation()"><i class="fa fa-file-pdf-o"></i> Salvar como PDF*</a><a href="#" onclick="imprimePagina();" class="btn-relatorio"><i class="i icon-print"></i> Imprimir</a><?php if($retorno) { ?><a href="<?=$retorno?>" class="btn-relatorio"><i class="i icon-undo"></i> Retornar</a><?php } ?>
	</div>
	<!-- espaçamento abaixo -->
	<div id="spacer" style="height: 50px;"></div>
	
	<!-- corpo -->
	<div id="imprimir" class="data data_relatorio width-mobile">			
		<!-- conteúdo dinâmico -->					
		<?php
		$pag = "includes/relatorios/clientes/".apenasNumeros(__CNPJ__)."/{$_GET['pagina']}.php";
		if(file_exists($pag)) {
			include($pag);
		}
		else {
			$pag = "includes/relatorios/{$_REQUEST['pagina']}.php";
		
			if(!file_exists($pag)) {
				mensagemErro("Página não encontrada");
			}
			else {
				include($pag);
			}
		}
		?>
	</div>
	<!-- /corpo -->
	
	<!-- js -->
	<script>
		function orientation(){
			/*var orient = confirm('Deseja imprimir o documento na orientação de paisagem?');

			if (orient === true) {
				pdf('landscape');
			}

			else {
				pdf('portrait');
			}
			*/
			pdf('landscape');
		}

		var marginpdf = 0.5;

		function pdf(ort){

			var element = document.getElementById('imprimir');
			html2pdf(element, {
				margin:       marginpdf,
				filename:     'maiscompleto_<?=$_GET['pagina']?>.pdf',
				image:        { type: 'jpeg', quality: 0.98 },
				html2canvas:  { dpi: 192, letterRendering: true },
				jsPDF:        { unit: 'in', format: 'letter', orientation: ort }
			});
		}
		// atalho para impressão
		function imprimePagina() {
			// esconde cabeçalho
			document.getElementById("cabecalho_relatorio").style.display = "none";
			document.getElementById("spacer").style.display = "none";
			
			// imprime
			window.print();
			
			// restaura cabeçalho
			document.getElementById("cabecalho_relatorio").style.display = "block";
			document.getElementById("spacer").style.display = "block";
		}
	</script>
</body>
</html>
