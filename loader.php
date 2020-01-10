<?php
include("functions.php");
include("src/services/Dates.php");

use src\services\Transact\ExtPDO;

ini_set("default_charset", "ISO-8859-1");

// identidade
@define("__NOME_SISTEMA__",				"NVerine");
@define("__DIR_IDENTIDADE__",			"");
define("__MAKER__",						"TSBrothers");
define("__MAKER_WEBSITE__",				"http://www.tsbrothers.com.br");
define("_base_path",				    "uploads/");

// versão do arquivo de configuração
$versao_config_atual = 1;

// constante para referenciar o endereço absoluto do sistema na rede
$host = "http://{$_SERVER['HTTP_HOST']}";
$pasta = preg_replace("#/[^/]*\.php$#simU", "/", $_SERVER["PHP_SELF"]); // remove script (*.php) do caminho
$url = $host.$pasta;

define("_pasta", $url); // essa constante é desnecessária em URLs porque aponta para a raiz do sistema.

// lê arquivo de configuração
$ini = @parse_ini_file("uploads/config.ini.php", false);

if(!$ini) {
    // não temos um arquivo!
    ini_set("display_errors", 1);
    error_reporting(E_ERROR | E_PARSE | E_RECOVERABLE_ERROR);
    include("instalador.php");
    die();
}

// confere se arquivo de configuração está atualizado
if($ini["versao"] < $versao_config_atual) {
    echo("O arquivo <code>config.ini.php</code> de seu sistema está desatualizado.
		Por favor, inclua as novas variáveis que foram criadas em <code>config.ini.php.sample</code>
		para garantir o melhor funcionamento do sistema, ou inclua a linha <code>versao = ".$versao_config_atual."</code>
		para desabilitar esta mensagem.");
    die();
}

// mensagem de manutenção/corte de serviço
if($ini["manutencao"]) {
    echo("Sistema fora do ar para manutenção. Por favor, volte em alguns instantes.");
    die();
}

// eventualmente podemos processar as seções para maior clareza
@define("__SENHA__",					$ini["senha"]);
@define("__GLASS_DEBUG__",				$ini["debug"]);
@define("__HOMOLOGACAO__",				$ini["homologacao"]);
@define("__LOG_EMAIL__",				$ini["log_email"]);
@define("__VIDRACARIA__", 				$ini["vidracaria"]);
@define("__DEVELOPER__",                $ini["developer"]);

// db
@define("__DB_DRIVER__",				$ini["dbdriver"]);
@define("__DB_HOST__", 					$ini["dbhost"]);
@define("__DB_NAME__",					$ini["dbname"]);
@define("__DB_USER__",					$ini["dbuser"]);
@define("__DB_PASS__",					$ini["dbpass"]);
@define("__DB_FORCE_UTF8__",			$ini["dbforceutf8"]);

// para os tipos diferenciados de comissão
@define("__COMISSAO_TIPO__", 			$ini["comissao_tipo"]);
@define("__COMISSAO_PERC__", 			$ini["comissao_perc"]);
@define("__COMISSAO_DATA__",			$ini["dia_pag_comissao"]);

// smtp
@define("__SMTP_HOST__",				$ini["smtp_host"]);
@define("__SMTP_PORT__",				$ini["smtp_port"]);
@define("__SMTP_USER__",				$ini["smtp_user"]);
@define("__SMTP_PASS__",				$ini["smtp_pass"]);
@define("__SMTP_SECURE__",				$ini["smtp_secure"]);

// chaves deprecadas do current - remover?
define("__PESQ_PORCENTO__", true);
define("__TAB_PRECOS_USA_DESCONTO__", false);

// novos parâmetros
@define("__USA_CAIXA__",				$ini["usa_caixa"]);
@define("__PROCESSA_EXPEDICAO__",		$ini["processa_expedicao"]);
@define("__LIBERA_CPF__",				$ini["libera_cpf"]);
@define("__UI_EXPERIMENTAL__",			$ini["ui_experimental"]);
@define("__EDITOR_HTML__",				$ini["editor_html"]);
@define("__LIBERA_CONDICAO_PAGTO__",	$ini["libera_condicao_pagto"]);
@define("__MODELO_NF__", 				$ini["modelo_nf"]);
@define("__PRODUCAO__", 				$ini["producao"]);
// @define("__NFE_40__",				$ini["nfe_40"]);
@define("__EDITA_NUM_NF__",				$ini["edita_num_nf"]);
@define("__LOTE_BOLETOS__",				$ini["lote_boletos"]);
@define("__CASAS_DECIMAIS__",			isset($ini["casas_decimais"]) ? $ini["casas_decimais"] : 2);
@define("__PDV_RESERVA_ESTOQUE__",		$ini["pdv_reserva_estoque"]);
@define("__OBSERVACOES__",				$ini["observacoes"]);
@define("__LIBERA_DATA_NOTA__",         $ini["libera_data_nota"]);
@define("__LIBERA_DATA_BAIXA__",		$ini["libera_data_baixa"]);
@define("__LIBERA_LANCAMENTO__",        $ini["libera_lancamento"]);
// regras de comissao
@define("__MAX_DESCONTO_VENDA__",		$ini["max_desconto_venda"]);
@define("__COMISSAO_FATOR__",			$ini["comissao_fator"]);
@define("__COMISSAO_SOBREPRECO__", 		$ini["sobrepreco"]);
@define("__COMISSAO_PARCELA__",			$ini["comissao_parcela"]);
// verificar se e educacional
@define("__EDUCACIONAL__",              $ini["educacional"]);


// grade de produtos
@define("__GRADE_TAMANHOS__", isset($ini["grade_tamanhos"]) ? $ini["grade_tamanhos"] : "P,M,G,GG");
@define("__GRADE_CORES__", isset($ini["grade_cores"]) ? $ini["grade_cores"] : "Azul,Amarelo,Vermelho,Preto,Branco");

// chaves que serão implementadas no arquivo no futuro, mas ainda não.
define("__MAX_CAIXAS__", 10);

// token para validação de captcha do google
define("__CAPTCHA_SECRET_KEY__", "6LfzcxYUAAAAAPtejWdrWtIklaLvJKnKedGKOGiV");

// configura php (isso é necessário?)
date_default_timezone_set('America/Sao_Paulo');

if(__GLASS_DEBUG__) {
    ini_set("display_errors", 1);
    error_reporting(E_ERROR | E_PARSE | E_RECOVERABLE_ERROR);
}
else {
    ini_set("display_errors", 0);
}

// abre conexão com banco de dados
try {
    // driver a usar e credenciais
    if(__DB_DRIVER__ == "dblib") { // dblib é o driver padrão para microsoft sql.
        $pdo_string = "dblib:version=8.0;host=".__DB_HOST__.";dbname=".__DB_NAME__.";";
        if(__DB_FORCE_UTF8__) $pdo_string .= " charset=utf8;";

        $conexao = new ExtPDO($pdo_string, __DB_USER__, __DB_PASS__);
    }
    elseif(__DB_DRIVER__ == "mysql") { // mysql tem suporte parcial ainda, mas é funcional.
        $pdo_string = "mysql:host=".__DB_HOST__."; dbname=".__DB_NAME__.";";

        $conexao = new ExtPDO($pdo_string, __DB_USER__, __DB_PASS__, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    }
    elseif(__DB_DRIVER__ == "sqlsrv") { // sqlsrv não é testado/suportado há muito tempo!
        $pdo_string = "sqlsrv:Server=".__DB_HOST__."; Database=".__DB_NAME__.";";

        $conexao = new ExtPDO($pdo_string, __DB_USER__, __DB_PASS__);
        $conexao->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
    }

    // puxa filial logada
    if(strpos($_SERVER["PHP_SELF"], "json") !== false
        || strpos($_SERVER["PHP_SELF"], "boleto") !== false) { // estes ambientes não podem trabalhar com filiais
        $default = null;
    }
    else {
        $default = 1;
    }

    $sql = "SELECT U.NOME AS USUARIO, U.APELIDO,
			F.*, F.NOME AS FILIAL, F.CNPJ,
			UF.FILIAL AS FILIALESCOLHIDA
			FROM K_FN_FILIAL F
			LEFT JOIN K_PD_USUARIOS U ON U.HANDLE = :usuario
			LEFT JOIN K_FN_USUARIOFILIAL UF ON UF.USUARIO = U.HANDLE";
    $stmt = $conexao->prepare($sql);

    if(isset($_SESSION["ID"])) {
        $stmt->bindValue(":usuario", $_SESSION["ID"]);
    }
    // puxa filial pelo cookie no caso de Login->relogar() ser executado depois
    else {
        $stmt->bindValue(":usuario", $_COOKIE["gs_uid"]);
    }

    $stmt->execute();
    $filiais = $stmt->fetchAll(PDO::FETCH_OBJ);

    if(!empty($filiais[0]->FILIALESCOLHIDA)) $default = $filiais[0]->FILIALESCOLHIDA;

    if(!empty($filiais)) {
        foreach($filiais as $r) {
            if($r->HANDLE == $default) {
                $f = $r;
                break;
            }
        }
    }

    // valida se usuário está no sistema correto
    if(isset($_SESSION["ID"]) && isset($_SESSION["NOME"])) {
        if($_SESSION["NOME"] != $f->USUARIO && $default != null) {
            $login = new \src\services\Login();
            $login->sair("Fingerprint mismatch");
        }
    }

    // define parâmetros da filial como constante global
    define("__TIMEZONE__", $f->TIMEZONE);
    define("__CSC__", $f->CSC);

    // se estiver vendo empresa, mostra asterisco no nome
    if($_SESSION["ver_empresa"]) $f->FILIAL = "{$f->FILIAL} *";
    // salva código e nome da filial
    define("__FILIAL__", $default);
    define("__TABELA_ME__", $f->TABELA_ME);
    define("__SISTEMA__", formataCase($f->FILIAL, true));
    define("__CNPJ__", $f->CNPJ);

}
catch(PDOException $erro) {
    echo("Falha de conexão com banco de dados: ".$erro->getMessage());
    die();
}

// tenta relogar com cookie
if(!isset($_SESSION["ID"])) {
    $login = new \src\services\UAC\Login();
    $login->relogar();
}

$dumper = new \src\services\Dumper();

function dumper($arg){
    $backtrace = debug_backtrace();
    \src\services\Dumper::dump($arg, $backtrace);
}

// puxa as permissões do usuário logado
$permissoes = new \src\services\UAC\Permissions();

// puxa as informações de perfil
$perfil = new \src\services\UAC\Perfil();
$perfil->fetch();

// puxa as notificações
$notificacoes = new \src\services\Notifier\Notifier();
$notificacoes->fetch();

// puxa eventos do usuário atual
$agenda = new \src\entity\AgendaGUI();
$agenda->pesquisa["pesq_usuario"] = $_SESSION["ID"];
$agenda->pesquisa["pesq_data_inicial"] = hoje();
$agenda->pesquisa["pesq_data_final"] = somaDias(hoje(), 30);
$agenda->top = "TOP 100";
$agenda->fetch();

/**
 * @param $string
 * carrega o diretorio dos arquivos
 */
function asset($string){
    echo "src/public/".$string;
}