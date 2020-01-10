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

        retornoPadrao($stmt, "Tabela de pre�os {$this->tabela_preco} inserida.", "N�o foi poss�vel salvar a tabela de pre�os {$this->tabela_preco}");
    }

    public function limpa()
    {
        $stmt = $this->deleteStatement("K_FN_TABELAPRECOS", array("PRODUTO" => $this->codigo));
        retornoPadrao($stmt, "Tabela de pre�os do produto #{$this->codigo} limpa.", "N�o foi poss�vel limpar a tabela de pre�os do produto #{$this->codigo}");
    }

    public static function getTabPrecos($precos = 0, $lista_precos = false)
    {
        $array_base = array(
            "PDV", "E-Commerce", "Venda Externa", "Venda Assistida",
            "Atacado", "Varejo", "Padr�o"
        );

        if ($lista_precos) {
            return $array_base;
        }

        return $array_base[$precos];
    }
}
