<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 11/02/2019
 * Time: 13:39
 */

namespace src\entity;


class ListaPrecoETT extends ObjectETT
{
    //variáveis recebidas da controller da lista de preço
    public $indice;
    public $nome;
    public $filial;
    public $ativo;
    public $data_inicio;
    public $data_fim;
    public $produtos = array();   // array com os itens de desconto


    /**
     * @param mixed $produto
     */
    public function setProduto($produto)
    {
        $this->produto = $produto;
    }

    /**
     * @param mixed $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * @param mixed $perc_desconto
     */
    public function setPercDesconto($perc_desconto)
    {
        $this->perc_desconto = $perc_desconto;
    }

    /**
     * limpa o cadastro caso ja tenha itens, senão retorna o numero
     */
    public static function limpa($indice){
        global $conexao;

        //limpa as linhas do banco de dados, para deletar as que forem removidas
        if(!empty($indice)){
            $sql = "DELETE FROM K_LISTAPRECO WHERE INDICE = :indice";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":indice", $indice);
            $stmt->execute();

            retornoPadrao($stmt, "Dados antigos limpos da lista de preço", "Não foi possível limpar dados antigos da lista de preço");

            // salva o indice atual para salvar os proximos itens
            $retorno = $indice;
        }

        // cria novo indice
        else{
            $stmt = $conexao->prepare("SELECT INDICE FROM K_LISTAPRECO ORDER BY INDICE DESC");
            $stmt->execute();
            $f = $stmt->fetchObject();

            if($f)
                $retorno = $f->INDICE + 1;
            else
                $retorno = 1;
        }

        return $retorno;
    }

    public function cadastra(){
        global $conexao;

        // campos obrigatorios
        validaCampo($this->data_inicio, "Data início");
        validaCampo($this->nome, "nome");
        validaCampo($this->data_fim, "Data fim");
        validaCampo($this->indice, "Indice");
        validaCampo($this->produto, "Produto");


        // gera novas listas de preço
        $this->handle = newHandle("K_LISTAPRECO", $conexao);

        $sql = "INSERT INTO K_LISTAPRECO 
				(HANDLE, INDICE, NOME, DATAINICIO, DATAFIM, PERCDESCONTO, PRODUTO, VALOR, ATIVO) VALUES 
				(:handle, :indice, :nome, :inicio, :fim, :perc_desconto, :produto, :valor, :ativo) \n ";

        $stmt = $conexao->prepare($sql);

        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":indice", $this->indice);
        $stmt->bindValue(":nome", $this->nome);
        $stmt->bindValue(":inicio", $this->data_inicio);
        $stmt->bindValue(":fim", $this->data_fim);
        $stmt->bindValue(":perc_desconto", validaVazio($this->perc_desconto));
        $stmt->bindValue(":produto", $this->produto);
        $stmt->bindValue(":valor", validaVazio($this->valor));
        $stmt->bindValue(":ativo", $this->ativo);
        $stmt->execute();

        retornoPadrao($stmt, "Cadastrada a lista de preço #{$this->handle}", "Não foi possível cadastrar a lista de preço {$this->handle}");

    }
}