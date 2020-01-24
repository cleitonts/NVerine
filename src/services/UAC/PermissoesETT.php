<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:59
 */

namespace src\services\UAC;

use src\entity\ObjectETT;
use src\services\Transact\ExtPDO as PDO;

/**
 * Class UAC
 * @package src\services\UAC
 * atualiza permissões de grupo
 */
class PermissoesETT extends ObjectETT {
    protected $permissoes;
    protected $compartilhados;
    protected $erro;

    const VISUALIZACAO = 1;
    const EDICAO = 2;

    public $grupo;
    public $cod_grupo;
    public $nivel;      // controla de qual nivel esta sendo acessado

    function __construct() {
        global $conexao;

        // guarda todas as permissões do usuário atual
        $this->permissoes = array();

        $sql = "SELECT A.NOME, P.NIVEL, G.HANDLE GRUPO, G.NOME AS NOMEGRUPO
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
        $this->cod_grupo = $this->permissoes[0]["GRUPO"];
        $this->grupo = $this->permissoes[0]["NOMEGRUPO"];

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

    // isso só pode ser rodado de actions.php :( criar um bloqueio?
    public function cadastra(array $obj) {
        global $conexao;

        // monta lista de grupos
        $handle = newHandle("K_FN_PERMISSOES", $conexao);
        $values = "";
        $i = 0;
        foreach($obj as $r) {
            if(isset($r["alcada_{$i}"])) {
                $nivel = 0;
                if(isset($r["vizualizar_{$i}"]))
                    $nivel = 1;
                if(isset($r["editar_{$i}"]))
                    $nivel = 2;
                if(isset($r["total_{$i}"]))
                    $nivel = 3;

                $values .= "({$handle}, {$r["alcada_{$i}"]}, {$this->grupo}, {$nivel}), ";
                $handle++;
                $i++;
            }
        }
        $values = trim($values, ", ");

        if(!empty($values)) {
            mensagem("Criando novos acessos...");

            $sql = "INSERT INTO K_FN_PERMISSOES (HANDLE, ALCADA, GRUPO, NIVEL) VALUES {$values}";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
        }
        else {
            mensagem("Grupo não possuirá nenhuma permissão!", MSG_AVISO);
        }
    }

    public function limpa()
    {
        validaCampo($this->grupo, "Grupo");

        mensagem("Apagando configurações antigas...");
        $this->deleteStatement("K_FN_PERMISSOES", array("GRUPO" => $this->grupo));
    }

    /**
     * @param $modulo
     * @param null $pagina
     * @return bool
     * retorna se a permissão existe
     * $modulo pode ser tanto o $__MODULO__ quanto $__PAGINA__
     * quando informado o $pagina serve para testar se existe algum bloqueio ou liberação especial
     */
    public function libera($modulo, $pagina = null) {
        $modulo = $this->trataString($modulo);
        $pagina = $this->trataString($pagina);
        $retorno = false;

        // todos têm acesso a principal
        if($modulo == "principal") return true;

        // busca módulo
        foreach($this->permissoes as $r) {
            if(!empty($pagina)){
                // manter desta forma, pois nao sebemos em qual posição do foreach será iterado
                if($r["NOME"] == $pagina) {
                    if ($r["NIVEL"] >= $this->nivel) {
                        return true;
                    }
                    $this->erro = "Sem acesso em ".strtoupper($pagina);
                    return false;
                }
            }

            if($r["NOME"] == $modulo && $r["NIVEL"] >= $this->nivel) $retorno = true;
        }

        // não encontrou
        if (!$retorno) $this->erro = "Sem acesso em ".strtoupper($modulo);
        return $retorno;
    }

    // bloqueia acesso no contexto do módulo
    public function valida() {
        global $__MODULO__;
        global $__PAGINA__;

        if($this->libera($__MODULO__, $__PAGINA__)){
            return true;
        }

        return false;
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