<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:41
 */

namespace src\services\UAC;


use src\services\Transact\ExtPDO as PDO;

class Login {
    public $usuario;
    public $senha;

    // métodos privados
    private function registraLogin() {
        global $conexao;

        // registra último login, gera auditoria
        $sql = "UPDATE K_PD_USUARIOS SET DATAULTIMOLOGIN = GETDATE() WHERE APELIDO = :usuario";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $this->usuario);
        $stmt->execute();
        //$log = new Auditoria\Logger($stmt);
    }

    // métodos públicos
    public function logar() {
        global $conexao;
        global $mensagens;

        // utiliza login e senha para acesso
        $sql = "SELECT HANDLE, NOME, CNPJCPF, DATAULTIMOLOGIN, GRUPO FROM K_PD_USUARIOS WHERE APELIDO = :usuario AND SENHA = :senha";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $this->usuario);
        $stmt->bindValue(":senha", $this->senha);
        $stmt->execute();

        $f = $stmt->fetch(PDO::FETCH_OBJ);

        // valida usuário e senha
        if(empty($f->HANDLE) || empty($f->GRUPO)) {
            $mensagens->retorno = "erro";
            mensagem("Dados de acesso inválidos", MSG_ERRO);
            return;
        }

        // efetua login
        $_SESSION["NOME"] = $f->NOME;
        $_SESSION["ID"] = $f->HANDLE;
        $_SESSION["DATAULTIMOLOGIN"] = converteDataSql0($f->DATAULTIMOLOGIN);
        $this->registraLogin();

        // salva cookie para login persistente
        if(!isset($_COOKIE["gs_uid"])) {
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
        $license = @parse_ini_file("uploads/license.ini.php");

        if(empty($license["key"])) {
            $_SESSION["LICENSE_HOLDER"] = false;
            $_SESSION["LICENSE_ID"] = false;
            $_SESSION["LICENSE_KEY"] = false;
            $_SESSION["LICENSE_EXPIRES"] = 0;
        }
        else {
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
    public function relogar() {
        if(isset($_COOKIE["gs_uid"])) {
            $this->usuario = $_COOKIE["gs_usuario"];
            $this->senha = decrypt($_COOKIE["gs_senha"], true);
            $this->logar();

            if(isset($_SESSION["ID"])) return true;
        }

        return false;
    }

    public function sair($erro = "") {
        // limpe aqui todas as sessions que foram criadas
        session_start();
        session_unset();
        session_destroy();

        // destrói o cookie
        $validade = time() -3600;
        setcookie("gs_uid", "", $validade);
        setcookie("gs_usuario", "", $validade);
        setcookie("gs_senha", "", $validade);
        setcookie("gs_fingerprint", "", $validade);

        // redireciona
        header("Location: index.php?erro=".urlencode($erro));
    }

    //usado na parte de requisição de estoque, serve somente para confirmar o usuario e a senha.
    public function verifica(){
        global $conexao;
        // utiliza login e senha para acesso
        $sql = "SELECT HANDLE, NOME FROM K_PD_USUARIOS WHERE APELIDO = :usuario AND SENHA = :senha";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $this->usuario);
        $stmt->bindValue(":senha", $this->senha);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // valida usuário e senha
        if(empty($f[0]->HANDLE)){
            return 0;
        }
        else{
            return $f[0]->HANDLE;
        }
    }
}