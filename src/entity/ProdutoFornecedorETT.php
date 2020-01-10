<?php
namespace src\entity;

class ProdutoFornecedorETT extends ObjectETT
{
    public $cod_fornecedor;
    public $fornecedor;
    public $codigo_fornecedor; // este � o c�digo DO PRODUTO NO fornecedor
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

        retornoPadrao($stmt, "Fornecedor inserido", "N�o foi poss�vel inserir o fornecedor");
    }

    public function limpa()
    {
        $stmt = $this->deleteStatement("PD_PRODUTOSFORNECEDORES", array("PRODUTO" => $this->cod_produto));
        
//        retornoPadrao($stmt, "Tabela fornecedores do produto #{$this->cod_produto} limpa.", 
//            "N�o foi poss�vel limpar a tabela de fornecedores do produto #{$this->cod_produto}");
    }
}
