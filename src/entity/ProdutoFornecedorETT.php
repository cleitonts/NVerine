<?php
namespace src\entity;

class ProdutoFornecedorETT extends ObjectETT
{
    public $cod_fornecedor;
    public $fornecedor;
    public $codigo_fornecedor; // este é o código DO PRODUTO NO fornecedor
    public $preco;
    public $cod_produto;
    public $produto;

    public function cadastra()
    {
        global $conexao;
        $this->handle = newHandle("PD_PRODUTOSFORNECEDORES", $conexao);        
        
        $stmt = $this->insertStatement("PD_PRODUTOSFORNECEDORES",
            array(
                "HANDLE" => $this->handle,
                "FORNECEDOR" => $this->cod_fornecedor,
                "CODIGOFORNECEDOR" => $this->codigo_fornecedor,
                "PRECOFORNECEDOR" => $this->preco,
                "PRODUTO" => $this->cod_produto
            )
        );

        retornoPadrao($stmt, "Fornecedor inserido", "Não foi possível inserir o fornecedor");
    }

    public function limpa()
    {
        $stmt = $this->deleteStatement("PD_PRODUTOSFORNECEDORES", array("PRODUTO" => $this->cod_produto));
        
//        retornoPadrao($stmt, "Tabela fornecedores do produto #{$this->cod_produto} limpa.", 
//            "Não foi possível limpar a tabela de fornecedores do produto #{$this->cod_produto}");
    }
}
