<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:59
 */

namespace src\services\UAC;

use src\services\ExtPDO as PDO;

/**
 * Class UAC
 * @package src\services\UAC
 * atualiza permissões de grupo
 */
class UAC {
    public $grupo;

    // isso só pode ser rodado de actions.php :( criar um bloqueio?
    public function cadastra() {
        global $conexao;

        mensagem("Apagando configurações antigas...");
        $sql = "DELETE FROM K_FN_PERMISSOES WHERE GRUPO = {$this->grupo}";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        // monta lista de grupos
        $handle = newHandle("K_FN_PERMISSOES", $conexao);
        $values = "";
        for($i = 1; $i <= 100; $i++) {
            if(isset($_REQUEST["alcada_{$i}"]) || isset($_REQUEST["bloqueia_{$i}"])) {
                if(isset($_REQUEST["bloqueia_{$i}"]))
                    $bloqueio = "'S'";
                else
                    $bloqueio = "'N'";

                $values .= "({$handle}, {$i}, {$this->grupo}, {$bloqueio}), ";
                $handle++;
            }
        }
        $values = trim($values, ", ");

        if(!empty($values)) {
            mensagem("Criando novos acessos...");

            $sql = "INSERT INTO K_FN_PERMISSOES (HANDLE, ALCADA, GRUPO, BLOQUEIO) VALUES {$values}";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
        }
        else {
            mensagem("Grupo não possuirá nenhuma permissão!", MSG_AVISO);
        }
    }
}