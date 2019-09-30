<?php
namespace src\entity;
use ExtPDO as PDO;

class TabelaPrecosETT extends ObjectETT
{
    public $indice;
    public $nome;
    public $produto;
    public $porcentagem;
    public $quantidade;
    public $codigo;
    public $perc_tab;
    public $tabela_preco;

    public $valor;
    public $qtd_tab;
    public $pe_cod_filho;
    public $pe_filho;
    public $pe_unidade;
    public $pe_quantidade;
    public $pe_valor_unitario;
    public $pe_valor_total;
//    public $table = array();

    public function cadastra(){

        global $conexao;
        $sql = "INSERT INTO K_FN_TABELAPRECOS (HANDLE, INDICE, PRODUTO, PORCENTAGEM, NOME, QUANTIDADE)
                VALUES (:handle, :indice, :codigo, :porcentagem, :nome, :quantidade)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", newHandle("K_FN_TABELAPRECOS", $conexao));
        $stmt->bindValue(":indice", $this->tabela_preco);
        $stmt->bindValue(":codigo", $this->codigo);
        $stmt->bindValue(":porcentagem", $this->perc_tab);
        $stmt->bindValue(":nome", left($this->nome, 100));
        $stmt->bindValue(":quantidade", intval($this->qtd_tab));
        $stmt->execute();

        retornoPadrao($stmt, "Tabela de pre�os {$this->tabela_preco} inserida.", "N�o foi poss�vel salvar a tabela de pre�os {$this->tabela_preco}");
    }

    public function limpa(){

        global $conexao;
        // atualiza tabelas de pre�o
        $sql = "DELETE FROM K_FN_TABELAPRECOS WHERE PRODUTO = :codigo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $this->codigo);
        $stmt->execute();
        retornoPadrao($stmt, "Tabela de pre�os {$this->tabela_preco} limpa.", "N�o foi poss�vel limpar a tabela de pre�os {$this->tabela_preco}");

    }



}
