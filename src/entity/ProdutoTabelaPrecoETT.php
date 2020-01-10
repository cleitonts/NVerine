<?php
namespace src\entity;

class ProdutoTabelaPrecoETT extends ObjectETT
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

    public function cadastra()
    {
        global $conexao;
        $this->handle = newHandle("K_FN_TABELAPRECOS", $conexao);

        $stmt = $this->insertStatement("K_FN_TABELAPRECOS",
            array(
                "HANDLE" => $this->handle,
                "INDICE" => $this->tabela_preco,
                "PRODUTO" => $this->codigo,
                "PORCENTAGEM" => $this->perc_tab,
                "NOME" => left($this->nome, 100),
                "QUANTIDADE" => intval($this->qtd_tab),
            )
        );

        retornoPadrao($stmt, "Tabela de preços {$this->tabela_preco} inserida.", "Não foi possível salvar a tabela de preços {$this->tabela_preco}");
    }

    public function limpa()
    {
        $stmt = $this->deleteStatement("K_FN_TABELAPRECOS", array("PRODUTO" => $this->codigo));
        retornoPadrao($stmt, "Tabela de preços do produto #{$this->codigo} limpa.", "Não foi possível limpar a tabela de preços do produto #{$this->codigo}");
    }

    public static function getTabPrecos($precos = 0, $lista_precos = false)
    {
        $array_base = array(
            "PDV", "E-Commerce", "Venda Externa", "Venda Assistida",
            "Atacado", "Varejo", "Padrão"
        );

        if ($lista_precos) {
            return $array_base;
        }

        return $array_base[$precos];
    }
}
