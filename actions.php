<?php
spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

include ("loader.php");

global $conexao;
global $dumper;

const __NO_ERROR_VIEW__ = true; // bloqueia erros que aparecem somente na view

// define página a incluir
$pag = strtolower($_REQUEST["pagina"]);// define página a incluir

// instancia mensagens globais
$mensagens = new \src\services\SysMessages\Messages();

if ($_REQUEST["act"] == "login"){
    $mensagens->retorno = "refresh";

    $login = new \src\services\UAC\Login();
    $login->usuario = $_REQUEST["usuario"];
    $login->senha = safercrypt($_REQUEST["senha"]);
    $login->logar();
    $mensagens->pronto(); // só isso!
    die();
}

// so mostra se estiver logado
elseif(isset($_SESSION["ID"])) {
    // envio de form que alteram as propriedades do config.ini
    if(in_array($_REQUEST["pagina"], array("toggledebug", "toggleverempresa")) ){
        switch ($_REQUEST["pagina"]) {
            case "toggledebug":

                if(__GLASS_DEBUG__) {
                    $mensagem = "desligar";
                    $v1 = "true";
                    $v2 = "false";
                }
                else {
                    $mensagem = "ligar";
                    $v1 = "false";
                    $v2 = "true";
                }

                $transact->mensagem("Tentanto {$mensagem} o Debug");

                // guarda o valor anterior para comparação
                $mensagem_anterior = $mensagem;

                /* tenta alterar o arquivo .ini em uploads.
                 * outras pastas não devem ser acessíveis por segurança!
                 *
                 * note aqui que o espaço entre o operador '=' e os átomos é importante
                 */
                shell_exec("sed -i -e 's/debug = {$v1}/debug = {$v2}/g' uploads/config.ini.php");

                // tenta gerar uma nova conexão para validar
               $transact->mensagem("O sistema tentará reiniciar a conexão para validar as mudanças", MSG_ERRO);

                break;
            case "toggleverempresa":
                if($_SESSION["ver_empresa"]) {
                    $_SESSION["ver_empresa"] = false;
                    $transact->mensagem("Você está agora visualizando apenas a filial ativa.", MSG_ERRO);
                }
                else {
                    $_SESSION["ver_empresa"] = true;
                    $transact->mensagem("Você está agora visualizando todas as filiais da empresa atual.", MSG_ERRO);
                }
                break;
            default:
                $transact->mensagem("burrice", MSG_ERRO);
        }

        $mensagens->retorno = "refresh";
        $mensagens->pronto(); // só isso!
        die();
    }

    // instancia o form para fazer a tradução
    $pagina = str_replace(".php", "", $pag)."FORM"; // sanitiza
    $arq = "src/views/forms/{$pagina}";
    $pagina = str_replace("/", "\\", $arq);

    // controladora
    $pagina2 = str_replace(".php", "", $pag)."CONTROLLER"; // sanitiza
    $arq2 = "src/views/controller/{$pagina2}";
    $pagina2 = str_replace("/", "\\", $arq2);

    if(file_exists("{$arq}.php")) {
        include_once($arq . "php");
        $temp = new $pagina();
        $base = $temp->createForm();
    }

    // não faz tradução caso seja apenas cadastro
    if(is_object($base)){
        $tradutor = new src\services\Translate();
        $traduzido = $tradutor->translate($_REQUEST, $base);
    }

    // se chegou aqui este arquivo deve existir também
    iniciaTransacao($conexao);
        include_once($arq2."php");
        $temp2 = new $pagina2();
        $temp2->persist($traduzido);
    redir($conexao);

}