<?php
include("functions.php");
include("src/services/Dates.php");

use src\services\Transact\ExtPDO;

ini_set("default_charset", "ISO-8859-1");

// identidade
define("__NOME_SISTEMA__",				"NVerine");
define("__MAKER__",						"TSBrothers");
define("__MAKER_WEBSITE__",				"http://www.tsbrothers.com.br");
define("_pasta",                        "");    // retirar de uma vez ou manter vazio

// vers�o do arquivo de configura��o
$versao_config_atual = 1;

// constante para referenciar o endere�o absoluto do sistema na rede
$host = "http://{$_SERVER['HTTP_HOST']}";
$temp = explode("?", $_SERVER["REQUEST_URI"]);
$pasta = preg_replace("#/[^/]*\.php$#simU", "/", $temp[0]); // remove script (*.php) do caminho
$url = $host.$pasta;

// =====================================================================================================================
//verifica se foi digitado a pasta para o funcionamento correto
if (strpos(dirname(__FILE__), 'releases_nverine') !== false) {
    $arr = explode("/", $_SERVER["REDIRECT_URL"]);
    if(empty($arr[1])) {
        include("../../404.html");
        die();
    }

    define("_base_name", $arr[1]);
    define("_base_root", "../../bases");
    define("_base_path", _base_root."/"._base_name."/erp/");
    if(!is_dir(_base_path)) {
        include("../../404.html");
        die();
    }
    // l� arquivo de configura��o
    $ini = @parse_ini_file(_base_path."config.ini.php", false);
}
else{
    define("_base_path", "uploads/");
    // l� arquivo de configura��o
    $ini = @parse_ini_file(_base_path."config.ini.php", false);

    // executa instalador
    if(!$ini) {
        // n�o temos um arquivo!
        ini_set("display_errors", 1);
        error_reporting(E_ERROR | E_PARSE | E_RECOVERABLE_ERROR);
        include("instalador.php");
        die();
    }
}

// confere se arquivo de configura��o est� atualizado
if($ini["versao"] < $versao_config_atual) {
    echo('O arquivo <code>config.ini.php</code> de seu sistema est� desatualizado.
		Por favor, inclua as novas vari�veis que foram criadas em <code>config.ini.php.sample</code>
		para garantir o melhor funcionamento do sistema, ou inclua a linha <code>versao = '.$versao_config_atual.'</code>
		para desabilitar esta mensagem.');
    die();
}

// mensagem de manuten��o/corte de servi�o
if($ini["manutencao"]) {
    echo("Sistema fora do ar para manuten��o. Por favor, volte em alguns instantes.");
    die();
}

// eventualmente podemos processar as se��es para maior clareza
@define("__SENHA__",					$ini["senha"]);
@define("__DEBUG__",				    $ini["debug"]);
@define("__HOMOLOGACAO__",				$ini["homologacao"]);
@define("__DEVELOPER__",                $ini["developer"]);

// db
@define("__DB_DRIVER__",				$ini["dbdriver"]);
@define("__DB_HOST__", 					$ini["dbhost"]);
@define("__DB_NAME__",					$ini["dbname"]);
@define("__DB_USER__",					$ini["dbuser"]);
@define("__DB_PASS__",					$ini["dbpass"]);

// smtp
@define("__SMTP_HOST__",				$ini["smtp_host"]);
@define("__SMTP_PORT__",				$ini["smtp_port"]);
@define("__SMTP_USER__",				$ini["smtp_user"]);
@define("__SMTP_PASS__",				$ini["smtp_pass"]);
@define("__SMTP_SECURE__",				$ini["smtp_secure"]);

@define("__LIBERA_CPF__",				$ini["libera_cpf"]);
@define("__MODELO_NF__", 				$ini["modelo_nf"]);
@define("__LOTE_BOLETOS__",				$ini["lote_boletos"]);
@define("__CASAS_DECIMAIS__",			isset($ini["casas_decimais"]) ? $ini["casas_decimais"] : 2);

// verificar se e educacional
@define("__EDUCACIONAL__",              $ini["educacional"]);

// grade de produtos
@define("__GRADE_TAMANHOS__", isset($ini["grade_tamanhos"]) ? $ini["grade_tamanhos"] : "P,M,G,GG");
@define("__GRADE_CORES__", isset($ini["grade_cores"]) ? $ini["grade_cores"] : "Azul,Amarelo,Vermelho,Preto,Branco");

// configura php (isso � necess�rio?)
date_default_timezone_set('America/Sao_Paulo');

if(__DEBUG__) {
    ini_set("display_errors", 1);
    error_reporting(E_ERROR | E_PARSE | E_RECOVERABLE_ERROR);
}
else {
    ini_set("display_errors", 0);
}

// abre conex�o com banco de dados
try {
    // driver a usar e credenciais
    if(__DB_DRIVER__ == "dblib") { // dblib � o driver padr�o para microsoft sql.
        $pdo_string = "dblib:version=8.0;host=".__DB_HOST__.";dbname=".__DB_NAME__.";";
        if(__DB_FORCE_UTF8__) $pdo_string .= " charset=utf8;";

        $conexao = new ExtPDO($pdo_string, __DB_USER__, __DB_PASS__);
    }
    elseif(__DB_DRIVER__ == "mysql") { // mysql tem suporte parcial ainda, mas � funcional.
        $pdo_string = "mysql:host=".__DB_HOST__."; dbname=".__DB_NAME__.";";

        $conexao = new ExtPDO($pdo_string, __DB_USER__, __DB_PASS__,
            array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    }
    elseif(__DB_DRIVER__ == "sqlsrv") { // sqlsrv n�o � testado/suportado h� muito tempo!
        $pdo_string = "sqlsrv:Server=".__DB_HOST__."; Database=".__DB_NAME__.";";

        $conexao = new ExtPDO($pdo_string, __DB_USER__, __DB_PASS__);
        $conexao->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
    }

    // puxa filial logada
    if(strpos($_SERVER["PHP_SELF"], "json") !== false
        || strpos($_SERVER["PHP_SELF"], "boleto") !== false) { // estes ambientes n�o podem trabalhar com filiais
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

    // valida se usu�rio est� no sistema correto
    if(isset($_SESSION["ID"]) && isset($_SESSION["NOME"])) {
        if($_SESSION["NOME"] != $f->USUARIO && $default != null) {
            $login = new \src\services\Login();
            $login->sair("Fingerprint mismatch");
        }
    }

    // define par�metros da filial como constante global
    define("__TIMEZONE__", $f->TIMEZONE);
    define("__CSC__", $f->CSC);

    // se estiver vendo empresa, mostra asterisco no nome
    if($_SESSION["ver_empresa"]) $f->FILIAL = "{$f->FILIAL} *";
    // salva c�digo e nome da filial
    define("__FILIAL__", $default);
    define("__TABELA_ME__", $f->TABELA_ME);
    define("__SISTEMA__", formataCase($f->FILIAL, true));
    define("__CNPJ__", $f->CNPJ);

}
catch(PDOException $erro) {
    echo("Falha de conex�o com banco de dados: ".$erro->getMessage());
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

// puxa as permiss�es do usu�rio logado
$permissoes = new \src\services\UAC\PermissoesETT();

// puxa as informa��es de perfil
$perfil = new \src\services\UAC\UsuarioGUI();
$perfil->fetch();
$perfil = $perfil->itens[0];

// puxa as notifica��es
$notificacoes = new \src\services\Notifier\Notifier();
$notificacoes->fetch();

// puxa eventos do usu�rio atual
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