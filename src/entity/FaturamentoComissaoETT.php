<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 11:23
 */

namespace src\entity;


class FaturamentoComissaoETT extends ObjectETT
{
// nota à qual pertence (setado no construtor)
    private $nota;

    //getter
    public function getNota()
    {
        return $this->nota;
    }

    const COM_TIPO_VENDEDOR = 1;
    const COM_TIPO_INDICACAO = 2;
    const COM_TIPO_SOBREPRECO = 3;

    // propriedades
    public $pessoa;            /* destinatário do financeiro. é uma pessoa, não um usuário.
							 * o cliente (javascript/php) deve listar as pessoas correspondentes
							 */
    public $valor_base;        /* "base de cálculo" do desconto, alimentada pelo cliente (javascript)
							 * pode ser o valor integral da venda ou ter um limite de desconto!
							 */
    public $tipo;

    public $valor_total;
    public $percentual;

    public $valor;            // valor_base * (percentual / 100)
    public $historico;        // campo livre de anotações

    //apenas para montar a comissao
    public $cod_produto;

    // apenas gui e relatorio
    public $cod_pessoa;
    public $data;
    public $status;
    public $valor_nota;
    public $valor_comissao;
    public $cor_status;

    // ----------------------------------------------------------------------------
    public function __construct($nota)
    {
        $this->nota = $nota;
    }

    public function cadastra()
    {
        global $conexao;

        // gera novas comissões
        $this->handle = newHandle("K_NOTACOMISSAO", $conexao);

        // insere
        $stmt = $this->insertStatement("K_NOTACOMISSAO",
            array(
                "HANDLE" => $this->handle,
                "NOTA" => $this->nota,
                "PESSOA" => validaVazio($this->pessoa),
                "TIPO" => $this->tipo,
                "VALORBASE" => $this->valor_base,
                "PERCENTUAL" => $this->percentual,
                "VALOR" => $this->valor,
                "HISTORICO" => left($this->historico, 250),
            ));

        retornoPadrao($stmt, "Cadastrada comissão de {$this->percentual}% sobre R$ " . formataValor($this->valor_base), "Não foi possível cadastrar a comissão");
    }

    public function atualiza()
    {
        /* não atualiza comissões (por enquanto) -- todas são apagadas e recadastradas.
         * podemos fazer isso, mas nada pode ser amarrado a estes itens de comissão;
         * o financeiro é gerado sem constraints, apenas amarrando à nota/duplicata original.
         */
        return false;
    }

    public function limpa()
    {
        $this->deleteStatement("K_NOTACOMISSAO", array("NOTA" => $this->nota));

        mensagem("Limpando valores antigos de comissão");
    }
}