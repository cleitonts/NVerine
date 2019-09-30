<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:56
 */

namespace src\services\UAC;

use src\services\Transact\ExtPDO as PDO;

/**
 * Class Permissions
 * @package src\services\UAC
 * controle mais rigoroso de permissões
 */
class Permissions {
    protected $permissoes;
    protected $compartilhados;
    protected $erro;

    public $grupo;

    function __construct() {
        global $conexao;

        // guarda todas as permissões do usuário atual
        $this->permissoes = array();

        $sql = "SELECT A.NOME, P.BLOQUEIO, G.HANDLE GRUPO
				FROM K_PD_USUARIOS U, K_FN_GRUPOUSUARIO G, K_FN_PERMISSOES P, K_PD_ALCADAS A
				WHERE P.ALCADA = A.HANDLE
				AND P.GRUPO = G.HANDLE
				AND U.GRUPO = G.HANDLE
				AND U.HANDLE = :usuario";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $_SESSION["ID"]);
        $stmt->execute();

        $this->permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // grupo do usuário logado
        $this->grupo = $this->permissoes[0]["GRUPO"];

        // guarda os módulos compartilhados
        $this->compartilhados = array();

        $sql = "SELECT NOME FROM K_PD_ALCADAS WHERE COMPARTILHADO = 'S'";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $this->compartilhados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // trata strings
        for($i = 0; $i < count($this->permissoes); $i++) {
            $this->permissoes[$i]["NOME"] = $this->trataString($this->permissoes[$i]["NOME"]);
        }

        for($i = 0; $i < count($this->compartilhados); $i++) {
            $this->compartilhados[$i]["NOME"] = $this->trataString($this->compartilhados[$i]["NOME"]);
        }

        // erro default
        $this->erro = "Indefinido";
    }

    // retorna se a permissão existe
    public function libera($modulo) {
        $modulo = $this->trataString($modulo);

        // todos têm acesso a principal
        if($modulo == "principal") return true;

        // busca módulo
        foreach($this->permissoes as $r) {
            if($r["NOME"] == $modulo) return true;
        }

        // não encontrou
        $this->erro = "Sem acesso em ".strtoupper($modulo);
        return false;
    }

    // retorna se existe um bloqueio
    public function bloqueia($pagina) {
        $pagina = $this->trataString($pagina);

        foreach($this->permissoes as $r) {
            if($r["NOME"] == $pagina && $r["BLOQUEIO"] == "S") {
                $this->erro = "Bloqueio em ".strtoupper($pagina);
                return true;
            }
        }

        return false;
    }

    // bloqueia acesso no contexto do módulo
    public function valida() {
        global $__MODULO__;
        global $__PAGINA__;

        if(!$this->libera($__MODULO__) || $this->bloqueia($__PAGINA__)) {
            ?>
            <script>
                $(document).ready(function(){
                    destinoMenu("acesso_negado&msg=<?=$this->erro?>");
                });
            </script>
            <?php
        }
    }

    // retorna se módulo é compartilhado
    public function compartilhado($modulo) {
        $modulo = $this->trataString($modulo);

        foreach($this->compartilhados as $r) {
            if($r["NOME"] == $modulo) return true;
        }

        return false;
    }

    // retorna se senha de liberação é válida (false ou mensagem para auditoria)
    public function validaSenhaMestra($senha) {
        global $conexao;

        // não aceita senha vazia
        if(empty($senha)) return false;

        // senha global sobrescreve todas.
        if($senha == __SENHA__) return "Ação liberada por administrador do sistema";

        // busca senha do líder do grupo de usuários
        $sql = "SELECT GRUPO FROM K_PD_USUARIOS WHERE HANDLE = :usuario";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":usuario", $_SESSION["ID"]);
        $stmt->execute();
        $grupo = $stmt->fetch(PDO::FETCH_OBJ);

        $sql = "SELECT U.SENHA, G.NOME AS NOMEGRUPO
				FROM K_PD_USUARIOS U, K_FN_GRUPOUSUARIO G
				WHERE U.NIVEL > 1 AND U.GRUPO = :grupo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":grupo", $grupo->GRUPO);
        $stmt->execute();
        $lider = $stmt->fetch(PDO::FETCH_OBJ);

        if(safercrypt($senha) == $lider->SENHA) return "Ação liberada por supervisor do grupo ".formataCase($lider->NOMEGRUPO);

        // senha inválida
        return false;
    }

    // sanitiza strings
    private function trataString($str) {
        $str = strtolower(sanitize($str));
        $str = str_replace(" ", "", $str);

        return $str;
    }
}