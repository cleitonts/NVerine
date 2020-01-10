<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 16:00
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class FaturamentoStatusTransicaoETT extends ObjectETT
{
    // propriedades
    public $de;                    // número do status
    public $para;                // ||
    public $entrada;            // S/N
    public $saida;                // ||

    // ----------------------------------------------------------------------------
    /* não há edição!
     * chame esta função uma única vez antes de recadastrar.
     */
    public function apagaTransicoes()
    {
        $this->deleteStatement("K_STATUSTRANSICOES", array(1 => 1));
    }

    public function cadastra()
    {
        global $conexao;

        $this->handle = newHandle("K_STATUSTRANSICOES", $conexao);

        // insere
        $stmt = $this->insertStatement("K_STATUSTRANSICOES",
            array(
                "HANDLE" => $this->handle,
                "DE" => $this->de,
                "PARA" => $this->para,
                "ENTRADA" => left($this->entrada, 1),
                "SAIDA" => left($this->saida, 1),
            ));

        retornoPadrao($stmt, "Transição {$this->de}->{$this->para} cadastrada", "Não foi possível cadastrar a transição de status");
    }

    public static function getStatus($status_handle, $tipo_transicao)
    {
        global $conexao;

        // tras os indices da lista
        $sql = "SELECT S.NOME, S.HANDLE 
                FROM K_STATUSTRANSICOES T 
                LEFT JOIN K_STATUS S ON T.PARA = S.HANDLE
                WHERE T.DE = {$status_handle} AND T.{$tipo_transicao} = 'S' ORDER BY HANDLE ASC";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if(!empty($listas)){
            $arr = array();
            foreach($listas as $r){
                $arr["handle"][] = $r->HANDLE;
                $arr["nome"][] = $r->NOME;
            }
            return $arr;
        }
    }
}
