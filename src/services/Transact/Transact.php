<?php
/*
 * =============================================================
 * funções globais para controle de fluxo e mensagens do sistema
 *
 * para a integridade do fluxo, é importante que a conexão com
 * BD seja apenas uma -- definida na página raiz, depois
 * instanciada com GLOBAL $CONEXAO.
 * chamar include("conexao.php") gera uma nova conexão sempre!
 *
 * agora temos orientação a objeto em class/Mensagens
 * =============================================================
 */

namespace src\services;

class Transact
{
    // tipos de mensagem (constantes globais)
    const MSG_PADRAO = 1;
    const MSG_ERRO = 2;
    const MSG_AVISO = 3;
    const MSG_SUCESSO = 4;
    const MSG_DEBUG = 5;

    // gera uma mensagem sem nenhuma ação adicional
    public function mensagem($msg, $tag = MSG_PADRAO)
    {
        global $mensagens;

        // temos que testar se o objeto mensagens existe, senão gera um erro fatal.
        if (isset($mensagens)) $mensagens->set($msg, $tag);
    }

    public function filtraFilial($campo, $modulo, $inclui_newline = true){
        global $permissoes;

        // parâmetros de filtragem de filial do ambiente de execução
        if (isset($_REQUEST["filial"])) {
            $filial = $_REQUEST["filial"]; // fallback para pdf, etc.
        } else {
            $filial = __FILIAL__;
        }

        if (empty($filial)) $filial = $_REQUEST["filial"];

        $ver_empresa = $_SESSION["ver_empresa"];
        if (!isset($_SESSION["ver_empresa"])) $ver_empresa = $_REQUEST["ver_empresa"]; // para aumentar a segurança, só executar isso quando estiver em pdf.php?

        // monta string de condição
        if (empty($filial)) { // deixa usuário navegar como administrador sem filial
            $cond = "1 = 1";
        } elseif ($permissoes->compartilhado($modulo) || $ver_empresa) {
            // $cond = $campo." IN (".$filiais_empresa.")";
            $cond = "1 = 1"; // regra: todas as filiais em um sistema são da mesma empresa.
        } else {
            $cond = $campo . " = " . $filial;
        }

        if ($inclui_newline) $cond .= "\n";
        return $cond;
    }

    // para o SQL statement fornecido (depois da execução), exibir uma mensagem de sucesso ou de erro
    // no caso de erro, interromper o processo!
    public function retornoPadrao($stmt, $sucesso = "Sucesso!", $erro = "Será que você deixou algum campo em branco?")
    {
        // para debug: guarda todas as queries executadas
        if (__GLASS_DEBUG__) {
            global $conexao;
            $query = $conexao->last_sql_statement;

            // mapeia valores no último statement executado
            if (!empty($stmt->bound_params)) {
                // ordena chaves pelo tamanho da string
                $keys = array_map("strlen", array_keys($stmt->bound_params));
                array_multisort($keys, SORT_DESC, $stmt->bound_params);

                // implementação tupiniquim de bindValue
                foreach ($stmt->bound_params as $key => $value) {
                    if (!is_numeric($value)) {
                        $value = "'{$value}'";
                    }

                    $query = str_replace(":{$key}", $value, $query);
                }
            }

            // salva no arquivo de log
            @file_put_contents(_base_path . "log.sql", $query . "\n\n", FILE_APPEND);
        }

        // sucesso
        if ($stmt->rowCount() > 0) {
            /* inventaram de esconder mensagens de sucesso passando string vazia.
             * isso está proibido daqui pra frente.
             * se é necessário, podemos criar um parâmetro non-verbose para só exibir erros e avisos.
             * ainda recomendo que se defina uma mensagem de sucesso em tudo
             */
            if ($sucesso) {
                mensagem($sucesso);
            } else {
                $chamada = debug_backtrace();
                $arq = $chamada[1]["file"];
                $linha = $chamada[1]["line"];
                mensagem("Mensagem órfã em {$arq}, linha {$linha} :'(");
            }

//            /* gera log da operação sucedida
//             * só vai guardar o registro alterado se o parâmetro passado foi ":handle"
//             * alterar os statements à medida do necessário!
//             */
//            include_once("class/Auditoria.php");
//            $log = new Auditoria\Logger($stmt);
            return true;
        } // erro
        else {
            global $conexao;

            $err = $stmt->errorInfo();
            $cod = $err[1];
            $msg = $err[2];

            // $erro = "<b>{$erro}</b>";
            if ($cod > 0) $erro .= " (Informe o c&oacute;digo de erro ao suporte: <span class='vermelho'>{$cod}</span>)";

            mensagem("Informações adicionais de erro: {$msg}", MSG_DEBUG);
            mensagem($erro, MSG_ERRO);
            finaliza($conexao);
        }
    }

    // início do fluxo -- gera a trava e marca o ponto de rollback
    public function iniciaTransacao($con)
    {
        if (!$_SESSION["t_lock"]) {
            $sql = "BEGIN TRANSACTION";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // gera trava
            $_SESSION["t_lock"] = true;

            // reinicia arquivo de log
            @file_put_contents(_base_path . "log.sql", "");
        } else {
            // mensagem("Tentando iniciar transação com lock", MSG_AVISO);
        }
    }

    /* fim do fluxo com commit.
     * não chame essa função diretamente, use redir()!
     * sério, não chame isso direto.
     * ÚNICA EXCEÇÃO: envio de nota fiscal em lote
     */
    public function doCommit($con)
    {
        global $mensagens;

        if ($_SESSION["t_lock"]) {
            // boqueia commit com ver empresa ativo!
            if ($_SESSION["ver_empresa"]) {
                mensagem("Alteração insegura ao banco de dados bloqueada. Clique na opção <b>'ver filial ativa'</b> no menu do usuário e repita a operação.", MSG_ERRO);
                finaliza();
            }

            // envia alterações
            $sql = "COMMIT";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            /* SOBRE A MENSAGEM DE SUCESSO:
             * isso havia sido tucanado para "os dados foram salvos corretamente."
             *
             * isso era porque estavam escrevendo rotinas que disparavam erro e não encerravam o processo;
             * isso porque tinham preguiça de implementar o controle de fluxo decentemente.
             * as rotinas de controle de fluxo estão todas documentadas aqui, e a maioria dos actions são um bom exemplo.
             *
             * uma rotina que chegou até aqui é bem-sucedida por definição!
             */
            mensagem("Operação concluída com sucesso!", MSG_SUCESSO);

            $_SESSION["t_lock"] = false;
        }
    }

    /* fim do fluxo com rollback.
     * não chame essa função diretamente, use finaliza() para interromper qualquer processo
     */
    public function doRollback($con)
    {
        if ($_SESSION["t_lock"]) {
            $sql = "ROLLBACK";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // mensagem("Revertendo ao estado anterior.");

            $_SESSION["t_lock"] = false;
        }
    }

    // wrapper para die/exit. interrupção do processo com erro
    public function finaliza()
    {
        global $conexao;
        global $mensagens;

        // cancela transação
        doRollback($conexao);

        /* exibe mensagens até aqui.
         * se você está escrevendo uma aplicação que vá puxar o objeto de mensagens
         * ao invés de renderizar no HTML, precisa reimplementar finaliza!
         * faça isso de alguma forma, mas NÃO altere o funcionamento aqui,
         * nem no objeto de mensagens.
         */
        $mensagens->pronto();

        /* se houve um erro, reseta o cooldown timer.
         * para que o usuário possa fazer correções rápidas no formulário
         */
        unset($_SESSION["sys_cooldown"]);

        // encerra o processo.
        die();
    }

    // fim do fluxo com sucesso. após o usuário confirmar as mensagens, redirecionar para o url_retorno informado
    public function redir($con)
    {
        global $mensagens;

        // encerra transação
        doCommit($con);

        // redireciona ou mostra botão continuar (*valem os comentários acima sobre a chamada de pronto())
        $mensagens->pronto(); // só isso!

        // encerra o processo.
        die();
    }

    // url de retorno para a mesma página
    public function getUrlRetorno()
    {
        // monta url
        $url = _pasta . "index.php" . "?";

        // filtra parâmetros vazios
        $params = explode("&", $_SERVER["QUERY_STRING"]);

        foreach ($params as $param) {
            $partes = explode("=", $param);
            if (!empty($partes[1])) {
                $url .= "{$partes[0]}={$partes[1]}&";
            }
        }

        // remove o último separador
        $url = trim($url, "&");
        return $url;
    }

    public function newHandle($tabela, $con = null)
    {
        if (empty($con)) {
            global $conexao;
            $con = &$conexao;
        }

        $stmt = $con->prepare("SELECT HANDLE FROM " . $tabela . " ORDER BY HANDLE DESC");
        $stmt->execute();
        $f = $stmt->fetchObject();

        if ($f) {
            return $f->HANDLE + 1;
        } else {
            return 1;
        }
    }

    // checa $_REQUEST de preenchimento obrigatório (contexto de actions)
    public function validaCampo($campo, $nome)
    {
        if (strlen(trim($campo)) <= 0) {
//            dumper($nome);
//            dumper(debug_backtrace());

            mensagem("Por favor, preencha o campo \"{$nome}\"", MSG_ERRO);
            finaliza();
        }
    }
}