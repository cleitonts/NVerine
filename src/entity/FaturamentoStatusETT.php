<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 15:52
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class FaturamentoStatusETT extends ObjectETT
{
// propriedades
    public $nome;
    public $cor;
    public $grupo_entrada;        // grupos restringem o acesso às notas em um determinado status.
    public $grupo_saida;        // no cadastro, informamos handles; na view, puxamos nomes [para cruzar com $permissoes->libera()]

    // gui apenas
    public $cod_grupo_entrada;
    public $cod_grupo_saida;

    // ----------------------------------------------------------------------------
    public function __construct()
    {
        $this->transicoes = array();
    }

    /* IMPORTANTE: não há função cadastra! (por enquanto)
     * existe somente edição dos 6 espaços reservados.
     * a base cadastral tem que vir populada do script de faturamento
     */
    public function atualiza()
    {
        $stmt = $this->updateStatement("K_STATUS",
            array(
                "HANDLE" => $this->handle,
                "NOME" => left($this->nome, 25),
                "COR" => left($this->cor, 2),
                "GRUPOENTRADA" => validaVazio($this->grupo_entrada),
                "GRUPOSAIDA" => validaVazio($this->grupo_saida),
            ));

        retornoPadrao($stmt, "Status {$this->handle}: {$this->nome} atualizado.", "Não foi possível atualizar o status de faturamento");
    }

    public static function getStatus($status = "")
    {
        global $conexao;

        if(!empty($status)){
            $where = "WHERE HANDLE = :status ";
        }
        $sql = "SELECT * FROM K_STATUS {$where}";
        $stmt = $conexao->prepare($sql);

        if (!empty($status)) $stmt->bindValue(":status", $status);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($f)) {
            $arr = array();
            foreach ($f as $r) {
                $arr["handle"][] = $r->HANDLE;
                $arr["nome"][] = $r->NOME;
            }
            return $arr;
        }
    }
}