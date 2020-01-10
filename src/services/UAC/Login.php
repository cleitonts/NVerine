<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:41
 */

namespace src\services\UAC;


use src\entity\SuporteDiagETT;
use src\services\Transact\ExtPDO as PDO;

class Login
{
    public $usuario;
    public $senha;

    // métodos privados
    private function registraLogin()
    {
        global $conexao;

        // registra último login, gera auditoria
        $sql = "UPDATE K_PD_USUARIOS SET DATAULTIMOLOGIN = GETDATE() WHERE APELIDO = :usuario";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $this->usuario);
        $stmt->execute();

        $this->atualizaBD();
        //$log = new Auditoria\Logger($stmt);
    }

    // métodos públicos
    public function logar()
    {
        global $conexao;
        global $mensagens;

        // utiliza login e senha para acesso
        $sql = "SELECT HANDLE, NOME, CNPJCPF, DATAULTIMOLOGIN, GRUPO, CLIENTE
                FROM K_PD_USUARIOS 
                WHERE APELIDO = :usuario AND SENHA = :senha";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $this->usuario);
        $stmt->bindValue(":senha", $this->senha);
        $stmt->execute();

        $f = $stmt->fetch(PDO::FETCH_OBJ);

        // valida usuário e senha
        if (empty($f->HANDLE) || empty($f->GRUPO)) {
            $mensagens->retorno = "erro";
            mensagem("Dados de acesso inválidos", MSG_ERRO);
            return;
        }

        // efetua login
        $_SESSION["NOME"] = $f->NOME;
        $_SESSION["ID"] = $f->HANDLE;
        $_SESSION["PESSOA"] = $f->CLIENTE;
        $_SESSION["DATAULTIMOLOGIN"] = converteDataSql0($f->DATAULTIMOLOGIN);
        $this->registraLogin();

        // salva cookie para login persistente
        if (!isset($_COOKIE["gs_uid"])) {
            $validade = time() + (86400 * 7);
            setcookie("gs_uid", $f->HANDLE, $validade);
            setcookie("gs_usuario", $this->usuario, $validade);
            setcookie("gs_senha", encrypt($this->senha), $validade);

            /* o fingerprint é uma forma de "assinar" o cookie com o sistema de origem e o usuário.
             * devíamos cruzar esta informação com os dados de conexao.php,
             * mas não está funcionando por algum motivo.
             */
            setcookie("gs_fingerprint", safercrypt(_pasta, "zXa4yv{$this->usuario}"), $validade);
        }

        // valida licença de uso do sistema
        $license = @parse_ini_file(_base_path . "license.ini.php");

        if (empty($license["key"])) {
            $_SESSION["LICENSE_HOLDER"] = false;
            $_SESSION["LICENSE_ID"] = false;
            $_SESSION["LICENSE_KEY"] = false;
            $_SESSION["LICENSE_EXPIRES"] = 0;
        } else {
            $_SESSION["LICENSE_HOLDER"] = $license["holder"];
            $_SESSION["LICENSE_ID"] = $license["cnpj"];
            $_SESSION["LICENSE_KEY"] = $license["key"];
            //$_SESSION["LICENSE_EXPIRES"] = LicenseManager::getDaysLeft($license["key"]);
        }
    }

    /*
     * tenta fazer um login automático com as credenciais guardadas no cookie.
     * a sessão é renovada sem o usuário perceber que foi desconectado
     * você pode capturar o retorno da função para redirecionar ao login se for false
     */
    public function relogar()
    {
        if (isset($_COOKIE["gs_uid"])) {
            $this->usuario = $_COOKIE["gs_usuario"];
            $this->senha = decrypt($_COOKIE["gs_senha"], true);
            $this->logar();

            if (isset($_SESSION["ID"])) return true;
        }

        return false;
    }

    public function sair($erro = "")
    {
        // limpe aqui todas as sessions que foram criadas
        session_start();
        session_unset();
        session_destroy();

        // destrói o cookie
        $validade = time() - 3600;
        setcookie("gs_uid", "", $validade);
        setcookie("gs_usuario", "", $validade);
        setcookie("gs_senha", "", $validade);
        setcookie("gs_fingerprint", "", $validade);

        // redireciona
        header("Location: index.php?erro=" . urlencode($erro));
    }

    //usado na parte de requisição de estoque, serve somente para confirmar o usuario e a senha.
    public function verifica()
    {
        global $conexao;
        // utiliza login e senha para acesso
        $sql = "SELECT HANDLE, NOME FROM K_PD_USUARIOS WHERE APELIDO = :usuario AND SENHA = :senha";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $this->usuario);
        $stmt->bindValue(":senha", $this->senha);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // valida usuário e senha
        if (empty($f[0]->HANDLE)) {
            return 0;
        } else {
            return $f[0]->HANDLE;
        }
    }

    public function atualizaBD()
    {
        try {
            global $conexao;

            $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'BASE_ESTRUTURA'";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $atualizado = $stmt->fetchAll(PDO::FETCH_OBJ);

            $bd_atualizado = array();
            foreach ($atualizado as $r) {
                $column = array();
                $column["type"] = $r->COLUMN_TYPE;
                $column["nullable"] = $r->IS_NULLABLE;
                $bd_atualizado[$r->TABLE_NAME][$r->COLUMN_NAME] = $column;
            }

            $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . __DB_NAME__ . "'";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $original = $stmt->fetchAll(PDO::FETCH_OBJ);

            $bd_original = array();
            foreach ($original as $r) {
                $column = array();
                $column["type"] = $r->COLUMN_TYPE;
                $column["nullable"] = $r->IS_NULLABLE;
                $bd_original[$r->TABLE_NAME][$r->COLUMN_NAME] = $column;
            }

            $string = "START TRANSACTION;\n";
            $create = null;
            foreach ($bd_atualizado as $t => $r) {
                if (empty($bd_original[$t])) {
                    if ($create == null) {
                        $tamanho_tabela = count($bd_atualizado[$t]);
                        $create .= "CREATE TABLE {$t} \n( \n";
                    }
                }
                foreach ($bd_atualizado[$t] as $c => $l) {
                    if ($create == null) {
                        if (empty($bd_original[$t][$c])) {
                            $string .= "ALTER TABLE {$t} ADD {$c} {$bd_atualizado[$t][$c]['type']} " . $this->nullable($bd_atualizado[$t][$c]['nullable']) . ";\n";
                        }
                    } else {
                        $create .= "    {$c} {$bd_atualizado[$t][$c]['type']} " . $this->nullable($bd_atualizado[$t][$c]['nullable']);
                        if ($tamanho_tabela > 0) {
                            $create .= ",\n";
                        } else {
                            $create .= "\n);\n";
                            $string .= $create;
                            $create = null;
                        }
                    }

                    $tamanho_tabela--;
                }
            }

            if ($string != "START TRANSACTION;\n") {
                $string .= "COMMIT;\n";
                // converte para utf8
                $string = mb_convert_encoding($string, 'UTF-8', 'OLD-ENCODING');

                if(!is_writable(_base_path)){
                    mensagem("Diretório da base não é editavel, impossível inserir diff de BD", MSG_AVISO);
                    return;
                }

                file_put_contents(_base_path . "bd_diff.sql", $string);
                SuporteDiagETT::parseScript("bd_diff.sql", $conexao, _base_path);
            }
        }
        catch (\PDOException $erro) {
            echo("Falha de conexão com banco de dados: " . $erro->getMessage());
            die();
        }
    }

    private function nullable($obj)
    {
        if ($obj == "YES") {
            return "NULL";
        }
        return "NOT NULL";
    }
}