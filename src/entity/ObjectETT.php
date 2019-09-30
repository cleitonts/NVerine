<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/01/2019
 * Time: 11:03
 */

namespace src\entity;

abstract class ObjectETT
{
    public $nome;
    public $handle;
    public $cont;

    // ------------------------------------------------------------------------------------------------------------------
    /* FUN��ES COMPARTILHADAS
     * gera��o de instru��es SQL padr�o.
     *
     * @tabela: nome da tabela a alterar
     * @campos_valores: um array na forma ["NOMEDOCAMPO"] => [$valor]
     * 					no caso do updateStatement, o campo "HANDLE" ser� uma cl�usula WHERE
     * @retorno: um ExtPDOStatement
     */
    protected function insertStatement($tabela, $campos_valores) {
        global $conexao;

        // valida se valores s�o um array v�lido
        if(!is_array($campos_valores) || empty($campos_valores)) {
            mensagem("insertStatement: \$campos_valores deve ser um array v�lido", MSG_ERRO);
            finaliza();
        }

        // cria��o da instru��o SQL
        $sql = "INSERT INTO {$tabela} (";

        // mapeia nomes das colunas
        foreach($campos_valores as $campo => $valor) {
            $sql .= "{$campo}, ";
        }
        $sql = trim($sql, ", ") . ")\n VALUES (";

        // mapeia valores (chaves)
        foreach($campos_valores as $campo => $valor) {
            $sql .= ":".strtolower($campo).", ";
        }
        $sql = trim($sql, ", ") . ")";

        // gera e executa o statement
        $stmt = $conexao->prepare($sql);

        foreach($campos_valores as $campo => $valor) {
            $stmt->bindValue(":".strtolower($campo), $valor);
            // mensagem(":{$campo} = '{$valor}'", MSG_DEBUG);
        }

        $stmt->execute();

        return $stmt;
    }

    protected function updateStatement($tabela, $campos_valores, $where_adicional = "") {
        global $conexao;

        // valida se valores s�o um array v�lido
        if(!is_array($campos_valores) || empty($campos_valores)) {
            mensagem("updateStatement: \$campos_valores deve ser um array v�lido", MSG_ERRO);
            finaliza();
        }

        /* valida se HANDLE existe e separa das outras chaves
         * (N�O, voc� n�o pode ter uma chave prim�ria que n�o se chame HANDLE. o nome disso � padr�o.)
         */
        if(!isset($campos_valores["HANDLE"])) {
            mensagem("updateStatement: HANDLE n�o foi definido", MSG_ERRO);
            finaliza();
        }

        $handle = $campos_valores["HANDLE"];
        unset($campos_valores["HANDLE"]);

        if(!is_numeric($handle) || $handle < 1) {
            mensagem("updateStatement: HANDLE n�o � um valor num�rico v�lido", MSG_ERRO);
            finaliza();
        }

        // cria��o da instru��o SQL
        $sql = "UPDATE {$tabela} SET\n";

        foreach($campos_valores as $campo => $valor) {
            $sql .= "{$campo} = :".strtolower($campo).", ";
            // mensagem(":{$campo} = '{$valor}'", MSG_DEBUG);
        }
        $sql = trim($sql, ", ")."\n";

        $sql .= "WHERE HANDLE = :handle ".$where_adicional;

        // gera e executa o statement
        $stmt = $conexao->prepare($sql);

        foreach($campos_valores as $campo => $valor) {
            $stmt->bindValue(":".strtolower($campo), $valor);
        }

        $stmt->bindValue(":handle", $handle);
        $stmt->execute();

        return $stmt;
    }

    // sim, podemos usar isso eventualmente, apesar de n�o ser o melhor design.
    public function deleteStatement($tabela, $campos_valores) {
        global $conexao;

        // valida se valores s�o um array v�lido
        if(!is_array($campos_valores) || empty($campos_valores)) {
            mensagem("updateStatement: \$campos_valores devem ser um array v�lido", MSG_ERRO);
            finaliza();
        }

        // cria��o da instru��o SQL
        $sql = "";

        foreach($campos_valores as $campo => $valor) {
            if(!empty($sql)) $sql .= " AND ";
            $sql .= "{$campo} = :".strtolower($campo);
            // mensagem(":{$campo} = '{$valor}'", MSG_DEBUG);
        }

        $sql = "DELETE FROM {$tabela} WHERE {$sql}";

        // gera e executa o statement
        $stmt = $conexao->prepare($sql);

        foreach($campos_valores as $campo => $valor) {
            $stmt->bindValue(":".strtolower($campo), $valor);
        }

        $stmt->execute();

        return $stmt; // geralmente n�o se passa o retorno de um DELETE para retornoPadrao
    }
}