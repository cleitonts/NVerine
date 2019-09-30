<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/02/2019
 * Time: 17:12
 */

// tipos de mensagem (constantes globais)
const MSG_PADRAO = 1;
const MSG_ERRO = 2;
const MSG_AVISO = 3;
const MSG_SUCESSO = 4;
const MSG_DEBUG = 5;

// gera uma mensagem sem nenhuma a��o adicional
function mensagem($msg, $tag = MSG_PADRAO)
{
    global $mensagens;

    // temos que testar se o objeto mensagens existe, sen�o gera um erro fatal.
    if (isset($mensagens)) $mensagens->set($msg, $tag);
}

// para o SQL statement fornecido (depois da execu��o), exibir uma mensagem de sucesso ou de erro
// no caso de erro, interromper o processo!
function retornoPadrao($stmt, $sucesso = "Sucesso!", $erro = "Ser� que voc� deixou algum campo em branco?")
{
    // para debug: guarda todas as queries executadas
    if (__GLASS_DEBUG__) {
        global $conexao;
        $query = $conexao->last_sql_statement;

        // mapeia valores no �ltimo statement executado
        if (!empty($stmt->bound_params)) {
            // ordena chaves pelo tamanho da string
            $keys = array_map("strlen", array_keys($stmt->bound_params));
            array_multisort($keys, SORT_DESC, $stmt->bound_params);

            // implementa��o tupiniquim de bindValue
            foreach ($stmt->bound_params as $key => $value) {
                if (!is_numeric($value)) {
                    $value = "'{$value}'";
                }

                $query = str_replace(":{$key}", $value, $query);
            }
        }

        // salva no arquivo de log
        @file_put_contents("uploads/log.sql", $query . "\n\n", FILE_APPEND);
    }

    // sucesso
    if ($stmt->rowCount() > 0) {
        /* inventaram de esconder mensagens de sucesso passando string vazia.
         * isso est� proibido daqui pra frente.
         * se � necess�rio, podemos criar um par�metro non-verbose para s� exibir erros e avisos.
         * ainda recomendo que se defina uma mensagem de sucesso em tudo
         */
        if ($sucesso) {
            mensagem($sucesso);
        } else {
            $chamada = debug_backtrace();
            $arq = $chamada[1]["file"];
            $linha = $chamada[1]["line"];
            mensagem("Mensagem �rf� em {$arq}, linha {$linha} :'(");
        }

        /* gera log da opera��o sucedida
         * s� vai guardar o registro alterado se o par�metro passado foi ":handle"
         * alterar os statements � medida do necess�rio!
         */

        //$log  = new Logger($stmt);
        return true;
    } // erro
    else {
        global $conexao;

        $err = $stmt->errorInfo();
        $cod = $err[1];
        $msg = $err[2];

        // $erro = "<b>{$erro}</b>";
        if ($cod > 0) $erro .= " (Informe o c�digo de erro ao suporte: <span class='vermelho'>{$cod}</span>)";

        mensagem("Informa��es adicionais de erro: {$msg}", MSG_DEBUG);
        mensagem($erro, MSG_ERRO);
        finaliza($conexao);
    }
}

// in�cio do fluxo -- gera a trava e marca o ponto de rollback
function iniciaTransacao($con)
{
    if (!$_SESSION["t_lock"]) {
        $sql = "BEGIN TRANSACTION";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        // gera trava
        $_SESSION["t_lock"] = true;

        // reinicia arquivo de log
        @file_put_contents("uploads/log.sql", "");
    } else {
        // mensagem("Tentando iniciar transa��o com lock", MSG_AVISO);
    }
}

/* fim do fluxo com commit.
 * n�o chame essa fun��o diretamente, use redir()!
 * s�rio, n�o chame isso direto.
 * �NICA EXCE��O: envio de nota fiscal em lote
 */
function doCommit($con)
{
    global $mensagens;

    if ($_SESSION["t_lock"]) {
        // boqueia commit com ver empresa ativo!
        if ($_SESSION["ver_empresa"]) {
            mensagem("Altera��o insegura ao banco de dados bloqueada. Clique na op��o <b>'ver filial ativa'</b> no menu do usu�rio e repita a opera��o.", MSG_ERRO);
            finaliza();
        }

        // envia altera��es
        $sql = "COMMIT";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        /* SOBRE A MENSAGEM DE SUCESSO:
         * isso havia sido tucanado para "os dados foram salvos corretamente."
         *
         * isso era porque estavam escrevendo rotinas que disparavam erro e n�o encerravam o processo;
         * isso porque tinham pregui�a de implementar o controle de fluxo decentemente.
         * as rotinas de controle de fluxo est�o todas documentadas aqui, e a maioria dos actions s�o um bom exemplo.
         *
         * uma rotina que chegou at� aqui � bem-sucedida por defini��o!
         */
        mensagem("Opera��o conclu�da com sucesso!", MSG_SUCESSO);

        $_SESSION["t_lock"] = false;
    }
}

/* fim do fluxo com rollback.
 * n�o chame essa fun��o diretamente, use finaliza() para interromper qualquer processo
 */
function doRollback($con)
{
    if ($_SESSION["t_lock"]) {
        $sql = "ROLLBACK";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        // mensagem("Revertendo ao estado anterior.");

        $_SESSION["t_lock"] = false;
    }
}

// wrapper para die/exit. interrup��o do processo com erro
function finaliza()
{
    global $conexao;
    global $mensagens;

    // cancela transa��o
    doRollback($conexao);

    /* exibe mensagens at� aqui.
     * se voc� est� escrevendo uma aplica��o que v� puxar o objeto de mensagens
     * ao inv�s de renderizar no HTML, precisa reimplementar finaliza!
     * fa�a isso de alguma forma, mas N�O altere o funcionamento aqui,
     * nem no objeto de mensagens.
     */
    $mensagens->pronto();

    /* se houve um erro, reseta o cooldown timer.
     * para que o usu�rio possa fazer corre��es r�pidas no formul�rio
     */
    unset($_SESSION["sys_cooldown"]);

    // encerra o processo.
    die();
}

// fim do fluxo com sucesso. ap�s o usu�rio confirmar as mensagens, redirecionar para o url_retorno informado
function redir($con)
{
    global $mensagens;

    // encerra transa��o
    doCommit($con);

    // redireciona ou mostra bot�o continuar (*valem os coment�rios acima sobre a chamada de pronto())
    $mensagens->pronto(); // s� isso!

    // encerra o processo.
    die();
}

// checa $_REQUEST de preenchimento obrigat�rio (contexto de actions)
function validaCampo($campo, $nome)
{
    global $mensagens;

    if (strlen(trim($campo)) <= 0) {
//            dumper($nome);
//            dumper(debug_backtrace());

        $mensagens->set("Por favor, preencha o campo \"{$nome}\"", MSG_ERRO);
        finaliza();
    }
}


/* inclui sql para filtrar campo de filial de acordo com a regra de compartilhamento definida em al�adas
 *
 * @campo: nome do campo na cl�usula WHERE a filtrar
 * @modulo: o nome da al�ada correspondente � informa��o que deve ser filtrada
 *       -- este N�O � necessariamente o nome da CLASSE ou do $__MODULO__!
 *          por exemplo, voc� pode querer filtrar se o "Plano de contas" � compartilhado ou n�o
 *          dentro de uma a��o do m�dulo "Financeiro"
 * @inclui_newline: passe false se n�o quiser que uma quebra de linha seja inserida automaticamente ao final
 */
function filtraFilial($campo, $modulo, $inclui_newline = true) {
    global $permissoes;

    // par�metros de filtragem de filial do ambiente de execu��o
    if(isset($_REQUEST["filial"])) {
        $filial = $_REQUEST["filial"]; // fallback para pdf, etc.
    }
    else {
        $filial = __FILIAL__;
    }

    if(empty($filial)) $filial = $_REQUEST["filial"];

    $ver_empresa = $_SESSION["ver_empresa"];
    if(!isset($_SESSION["ver_empresa"])) $ver_empresa = $_REQUEST["ver_empresa"]; // para aumentar a seguran�a, s� executar isso quando estiver em pdf.php?

    // monta string de condi��o
    if(empty($filial)) { // deixa usu�rio navegar como administrador sem filial
        $cond = "1 = 1";
    }
    elseif($permissoes->compartilhado($modulo) || $ver_empresa) {
        // $cond = $campo." IN (".$filiais_empresa.")";
        $cond = "1 = 1"; // regra: todas as filiais em um sistema s�o da mesma empresa.
    }
    else {
        $cond = $campo." = ".$filial;
    }

    if($inclui_newline) $cond .= "\n";
    return $cond;
}

function newHandle($tabela, $con = null) {
    if(empty($con)) {
        global $conexao;
        $con = &$conexao;
    }

    $stmt = $con->prepare("SELECT HANDLE FROM ".$tabela." ORDER BY HANDLE DESC");
    $stmt->execute();
    $f = $stmt->fetchObject();

    if($f) {
        return $f->HANDLE + 1;
    }
    else {
        return 1;
    }
}