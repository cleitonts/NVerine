<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:57
 */

namespace src\services\UAC;

use src\services\Transact\ExtPDO as PDO;

/**
 * Class PermissionsDummy
 * @package src\services\UAC
 * para usuarios que não precisam estar logados no sistema
 */
class PermissionsDummy {
    private $compartilhados;

    function __construct() {
        global $conexao;

        // guarda os módulos compartilhados
        $this->compartilhados = array();

        $sql = "SELECT NOME FROM K_PD_ALCADAS WHERE COMPARTILHADO = 'S'";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $this->compartilhados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // trata strings
        for($i = 0; $i < count($this->compartilhados); $i++) {
            $this->compartilhados[$i]["NOME"] = $this->trataString($this->compartilhados[$i]["NOME"]);
        }
    }

    public function libera($modulo) {
        return true;
    }

    public function bloqueia($pagina) {
        return false;
    }

    public function valida() {
        return 0;
    }

    public function compartilhado($modulo) {
        $modulo = $this->trataString($modulo);

        foreach($this->compartilhados as $r) {
            if($r["NOME"] == $modulo) return true;
        }

        return false;
    }

    public function validaSenhaMestra($senha) {
        return true;
    }

    private function trataString($str) {
        $str = strtolower(sanitize($str));
        $str = str_replace(" ", "", $str);

        return $str;
    }
}