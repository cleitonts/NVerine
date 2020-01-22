<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 13:51
 */

namespace src\services\Transact;

use PDO;
use PDOStatement;

class ExtPDO extends PDO {
    // guarda uma cópia dos parâmetros
    public $last_sql_statement;

    // implementa paginação cross-plataforma
    public $pagina = null;

    // ---------------------------------------------------------------------------------------------------------
    // construtor obrigatório
    public function __construct($pdo_string, $user, $pass, $options = array()) {
        // new PDO
        parent::__construct($pdo_string, $user, $pass, $options);

        // implementa nossa classe statement
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("ExtPDOStatement", array($this)));
    }

    /* extensão de prepare()
     *
     * propósito: tratar o SQL para manter portabilidade entre BDs
     */
    public function prepare($sql, array $options = array()) {
        if(__DB_DRIVER__ == "mysql") {
            //

            // mysql não usa esse schema dbo
            $sql = str_replace('"dbo".', '', $sql);

            // mysql não gosta de aspas duplas
            $sql = str_replace('"', '', $sql);

            // as vezes pode ser de proposito
            $sql = str_replace('\\quote', '"', $sql);

            // conversão de tipos
            $sql = str_replace(' image', ' blob', $sql); // cuidado porque posso passar parâmetros :imagem

            // exceções
            $sql = str_replace('CAST(BANCO AS VARCHAR(3))', 'BANCO', $sql);
            $sql = str_replace('CAST(C.BANCO AS VARCHAR(3))', 'BANCO', $sql);
            $sql = str_replace('DATEDIFF(DD,', 'DATEDIFF(', $sql);
            // $sql = str_replace('CONVERT(VARCHAR(8000), P.DESCRICAO) DESCRICAO', 'P.DESCRICAO', $sql);
            $sql = str_replace('CONVERT(VARCHAR(1000), TRANSTORNOSJSON) TRANSTORNOSJSON', 'TRANSTORNOSJSON', $sql);
            $sql = str_replace('CONVERT(VARCHAR(10), DATA, 20)', 'DATA', $sql);

            // chamadas com alternativas simples
            $sql = str_replace('GETDATE()', "'".date("Y-m-d H:i:s")."'", $sql);
            $sql = str_replace('ISNULL', 'IFNULL', $sql);
            $sql = str_replace('BEGIN TRANSACTION', 'START TRANSACTION', $sql);
            $sql = str_replace('LEN(', 'LENGTH(', $sql);
            $sql = str_replace("ALTER COLUMN", "MODIFY", $sql);
            $sql = str_replace("DROP CONSTRAINT", "DROP FOREIGN KEY", $sql);
            $sql = str_replace("AS INTEGER", "AS UNSIGNED", $sql);
            // transação não funciona no mysql porque o driver não suporta múltiplos statements? SET AUTOCOMMIT = 0; START TRANSACTION

            // conversão de TOP no início para LIMIT no final (ver os casos específicos de subselects com TOP 1!)
            $pos = strpos($sql, "TOP ");
            while($pos !== false) {
                // tenta determinar o valor do top
                $valor = intval(substr($sql, $pos + 4, 3)); // "TOP XXX"; <- $valor = intval(xxx)

                // se não encontrou TOP XXX, tente encontrar TOP XX
                if(empty($valor)) $valor = intval(substr($sql, $pos + 4, 2));
;
                // só execute se encontrou um valor válido
                if($valor > 1) {
                    // remove o valor encontrado da query
                    $sql = str_replace("TOP {$valor}", "", $sql);

                    /* insere no final
                     * agora testa se usamos paginação ou não!
                     */
                    if(is_null($this->pagina)) {
                        $sql = "{$sql} LIMIT {$valor}";
                    }
                    else {
                        /* calcula o valor do offset da paginação.
                         * a contagem de páginas começa no 1 e não no zero!
                         */
                        $pagina = intval($this->pagina) - 1;
                        if($pagina < 0) $pagina = 0; // just in case

                        $offset = $valor * $pagina;

                        $sql = "{$sql} LIMIT {$offset}, {$valor}";
                    }
                }

                // busca próximo
                $pos = strpos("TOP ", $sql);
            }

            // conversão de TOP 1 para LIMIT 1 em subqueries
            $pos = strpos($sql, "TOP 1");
            while($pos !== false) {
                // encontra o parêntese de fechamento do contexto
                $cont = 0;

                for($i = $pos; $i <= $pos + 500; $i++) { // qual é um limite aceitável para a busca?
                    $char = substr($sql, $i, 1);

                    if($char == "(") $cont++;
                    if($char == ")") $cont--;
                    if($cont < 0) break;
                }

                // insere LIMIT 1 no final do contexto
                $inicio = substr($sql, 0, $i);
                $final = substr($sql, $i + 1);
                $sql = str_replace("TOP 1", "", $inicio)." LIMIT 1)".$final;

                // encontra próximo
                $pos = strpos($sql, "TOP 1");
            }
        }

        /* conversão de TOP no início para cláusula OFFSET/FETCH do sql server.
         * isso é um copicola rápido da lógica do mysql acima; encontrar uma forma mais elegante
         */
        elseif(__DB_DRIVER__ == "dblib" && !is_null($this->pagina)) {
            $pos = strpos($sql, "TOP ");

            // as vezes pode ser de proposito
            $sql = str_replace('\\quote', '"', $sql);

            while($pos !== false) {
                // tenta determinar o valor do top
                $valor = intval(substr($sql, $pos + 4, 3)); // "TOP XXX"; <- $valor = intval(xxx)

                // se não encontrou TOP XXX, tente encontrar TOP XX
                if(empty($valor)) $valor = intval(substr($sql, $pos + 4, 2));

                // só execute se encontrou um valor válido
                if($valor > 1) {
                    // remove o valor encontrado da query
                    $sql = str_replace("TOP {$valor}", "", $sql);

                    /* calcula o valor do offset da paginação.
                     * a contagem de páginas começa no 1 e não no zero!
                     */
                    $pagina = intval($this->pagina) - 1;
                    if($pagina < 0) $pagina = 0; // just in case

                    $offset = $valor * $pagina;

                    $sql = "{$sql} OFFSET {$offset} ROWS FETCH NEXT {$valor} ROWS ONLY";
                }

                // busca próximo
                $pos = strpos("TOP ", $sql);
            }
        }

        $this->last_sql_statement = $sql; 	// guarda para referência
        $this->pagina = null;				// reseta paginação para próximo statement

        return parent::prepare($sql, $options);
    }
}

class ExtPDOStatement extends PDOStatement {
    // guarda uma cópia dos parâmetros
    public $bound_params = array();

    // ---------------------------------------------------------------------------------------------------------
    // construtor obrigatório
    protected function __construct() {}

    /* extensão de bindValue()
     *
     * propósito: guardar os parâmetros passados para o statement
     * para debug e auditoria
     */
    public function bindValue($key, $value, $type = PDO::PARAM_STR) {
        parent::bindValue($key, $value, $type);

        $key = trim($key, ":");
        $this->bound_params[$key] = $value;
    }

    /* extensão de fetchAll()
     *
     * propósito: tratar o bug canalha do SQL Server que retorna
     * uma string vazia ('') como um phantom space (' ')
     */
    public function fetchAll($how = NULL, $class_name = NULL, array $ctor_args = NULL) {
        $f = parent::fetchAll($how);

        return $this->trataRetorno($f, $how);
    }

    // ações em cima dos dados de fetch() e fetchAll(), de acordo com o parâmetro de fetch
    private function trataRetorno($f, $fetch_style) {
        // você nem precisava estar aqui!
        if(__DB_DRIVER__ == "mysql") return $f;

        // ok, trate
        switch($fetch_style) {
            case PDO::FETCH_OBJ:
                if(!empty($f)) {
                    foreach($f as $r) { // itera linhas
                        foreach($r as $key => $value) { // itera colunas
                            if($value == ' ') $r->$key = '';
                        }
                    }
                }
                break;
            default:
                // faça nada...
        }

        return $f;
    }
}