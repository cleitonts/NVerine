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
    // guarda uma c�pia dos par�metros
    public $last_sql_statement;

    // implementa pagina��o cross-plataforma
    public $pagina = null;

    // ---------------------------------------------------------------------------------------------------------
    // construtor obrigat�rio
    public function __construct($pdo_string, $user, $pass, $options = array()) {
        // new PDO
        parent::__construct($pdo_string, $user, $pass, $options);

        // implementa nossa classe statement
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("ExtPDOStatement", array($this)));
    }

    /* extens�o de prepare()
     *
     * prop�sito: tratar o SQL para manter portabilidade entre BDs
     */
    public function prepare($sql, array $options = array()) {
        if(__DB_DRIVER__ == "mysql") {
            //

            // mysql n�o usa esse schema dbo
            $sql = str_replace('"dbo".', '', $sql);

            // mysql n�o gosta de aspas duplas
            $sql = str_replace('"', '', $sql);

            // as vezes pode ser de proposito
            $sql = str_replace('\\quote', '"', $sql);

            // convers�o de tipos
            $sql = str_replace(' image', ' blob', $sql); // cuidado porque posso passar par�metros :imagem

            // exce��es
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
            // transa��o n�o funciona no mysql porque o driver n�o suporta m�ltiplos statements? SET AUTOCOMMIT = 0; START TRANSACTION

            // convers�o de TOP no in�cio para LIMIT no final (ver os casos espec�ficos de subselects com TOP 1!)
            $pos = strpos($sql, "TOP ");
            while($pos !== false) {
                // tenta determinar o valor do top
                $valor = intval(substr($sql, $pos + 4, 3)); // "TOP XXX"; <- $valor = intval(xxx)

                // se n�o encontrou TOP XXX, tente encontrar TOP XX
                if(empty($valor)) $valor = intval(substr($sql, $pos + 4, 2));
;
                // s� execute se encontrou um valor v�lido
                if($valor > 1) {
                    // remove o valor encontrado da query
                    $sql = str_replace("TOP {$valor}", "", $sql);

                    /* insere no final
                     * agora testa se usamos pagina��o ou n�o!
                     */
                    if(is_null($this->pagina)) {
                        $sql = "{$sql} LIMIT {$valor}";
                    }
                    else {
                        /* calcula o valor do offset da pagina��o.
                         * a contagem de p�ginas come�a no 1 e n�o no zero!
                         */
                        $pagina = intval($this->pagina) - 1;
                        if($pagina < 0) $pagina = 0; // just in case

                        $offset = $valor * $pagina;

                        $sql = "{$sql} LIMIT {$offset}, {$valor}";
                    }
                }

                // busca pr�ximo
                $pos = strpos("TOP ", $sql);
            }

            // convers�o de TOP 1 para LIMIT 1 em subqueries
            $pos = strpos($sql, "TOP 1");
            while($pos !== false) {
                // encontra o par�ntese de fechamento do contexto
                $cont = 0;

                for($i = $pos; $i <= $pos + 500; $i++) { // qual � um limite aceit�vel para a busca?
                    $char = substr($sql, $i, 1);

                    if($char == "(") $cont++;
                    if($char == ")") $cont--;
                    if($cont < 0) break;
                }

                // insere LIMIT 1 no final do contexto
                $inicio = substr($sql, 0, $i);
                $final = substr($sql, $i + 1);
                $sql = str_replace("TOP 1", "", $inicio)." LIMIT 1)".$final;

                // encontra pr�ximo
                $pos = strpos($sql, "TOP 1");
            }
        }

        /* convers�o de TOP no in�cio para cl�usula OFFSET/FETCH do sql server.
         * isso � um copicola r�pido da l�gica do mysql acima; encontrar uma forma mais elegante
         */
        elseif(__DB_DRIVER__ == "dblib" && !is_null($this->pagina)) {
            $pos = strpos($sql, "TOP ");

            // as vezes pode ser de proposito
            $sql = str_replace('\\quote', '"', $sql);

            while($pos !== false) {
                // tenta determinar o valor do top
                $valor = intval(substr($sql, $pos + 4, 3)); // "TOP XXX"; <- $valor = intval(xxx)

                // se n�o encontrou TOP XXX, tente encontrar TOP XX
                if(empty($valor)) $valor = intval(substr($sql, $pos + 4, 2));

                // s� execute se encontrou um valor v�lido
                if($valor > 1) {
                    // remove o valor encontrado da query
                    $sql = str_replace("TOP {$valor}", "", $sql);

                    /* calcula o valor do offset da pagina��o.
                     * a contagem de p�ginas come�a no 1 e n�o no zero!
                     */
                    $pagina = intval($this->pagina) - 1;
                    if($pagina < 0) $pagina = 0; // just in case

                    $offset = $valor * $pagina;

                    $sql = "{$sql} OFFSET {$offset} ROWS FETCH NEXT {$valor} ROWS ONLY";
                }

                // busca pr�ximo
                $pos = strpos("TOP ", $sql);
            }
        }

        $this->last_sql_statement = $sql; 	// guarda para refer�ncia
        $this->pagina = null;				// reseta pagina��o para pr�ximo statement

        return parent::prepare($sql, $options);
    }
}

class ExtPDOStatement extends PDOStatement {
    // guarda uma c�pia dos par�metros
    public $bound_params = array();

    // ---------------------------------------------------------------------------------------------------------
    // construtor obrigat�rio
    protected function __construct() {}

    /* extens�o de bindValue()
     *
     * prop�sito: guardar os par�metros passados para o statement
     * para debug e auditoria
     */
    public function bindValue($key, $value, $type = PDO::PARAM_STR) {
        parent::bindValue($key, $value, $type);

        $key = trim($key, ":");
        $this->bound_params[$key] = $value;
    }

    /* extens�o de fetchAll()
     *
     * prop�sito: tratar o bug canalha do SQL Server que retorna
     * uma string vazia ('') como um phantom space (' ')
     */
    public function fetchAll($how = NULL, $class_name = NULL, array $ctor_args = NULL) {
        $f = parent::fetchAll($how);

        return $this->trataRetorno($f, $how);
    }

    // a��es em cima dos dados de fetch() e fetchAll(), de acordo com o par�metro de fetch
    private function trataRetorno($f, $fetch_style) {
        // voc� nem precisava estar aqui!
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
                // fa�a nada...
        }

        return $f;
    }
}