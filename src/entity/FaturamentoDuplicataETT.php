<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 11:09
 */

namespace src\entity;

use ExtPDO as PDO;

class FaturamentoDuplicataETT extends ObjectETT
{
    // c�digos de forma de pagamento
    const FORMA_PAGSEGURO = 99;
    const FORMA_BOLETO = 13;
    const FORMA_DUPLICATA = 14;
    const FORMA_DINHEIRO = 1;
    const FORMA_SEM_PAGAMENTO = 11;

    // nota � qual pertence (setado no construtor)
    private $nota;

    // propriedades
    public $numero;
    public $forma_pagamento;
    public $prefixo;
    public $data_emissao;
    public $data_vencimento_original;
    public $data_vencimento_real;
    public $data_baixa;
    public $valor;
    public $baixado;
    public $numero_caixa;
    public $cheque;

    public $valor_aditivo;
    public $valor_juros;
    public $valor_multa;
    public $valor_desconto;
    public $valor_total;
    public $valor_baixa;

    // ** propriedades de forma de pagamento boleto
    public $banco;                // c�digo de 3 d�gitos
    public $nosso_numero;        // sequencial do boleto
    public $boleto_hash;        // calculado no fetch
    public $boleto_url;            // ||

    // ** apenas gui
    public $tipo_pagamento_nfe;    // c�digo de tipo de pagamento para nota fiscal
    public $cod_forma_pagamento;
    public $cod_prefixo;
    public $dias;
    public $intervalo;            // ?

    // minimo de seguran�a na altera��o
    public function setNota($nota){
        $this->nota = intval($nota);
    }

    // ----------------------------------------------------------------------------
    public function __construct($nota = 0)
    {
        $this->nota = $nota;
        $this->numero_caixa = 1;

        // zera valores
        $this->valor = 0;
        $this->valor_aditivo = 0;
        $this->valor_juros = 0;
        $this->valor_multa = 0;
        $this->valor_desconto = 0;
        $this->valor_total = 0;
    }

    public function cadastra()
    {
        global $conexao;

        $this->handle = newHandle("K_NOTADUPLICATAS", $conexao);

        // insere
        $stmt = $this->insertStatement("K_NOTADUPLICATAS",
            array(
                "HANDLE" => $this->handle,
                "NOTA" => $this->nota,
                "BAIXADO" => "N"
            ));
        // n�o precisa do retorno padr�o aqui porque a falha nesse insert vai ocasionar uma falha no pr�ximo update.
        if (__GLASS_DEBUG__) retornoPadrao($stmt, "Alocando tabela de duplicatas...", "N�o foi poss�vel fazer o cadastro inicial da duplicata");

        $this->atualiza();
    }

    public function atualiza()
    {
        global $conexao;

        $stmt = $this->updateStatement("K_NOTADUPLICATAS",
            array(
                "HANDLE" => $this->handle,
                "NUMERO" => $this->numero,
                "FORMAPAGAMENTO" => validaVazio($this->forma_pagamento),
                "PREFIXO" => validaVazio($this->prefixo),
                "DATAEMISSAO" => $this->data_emissao,
                "DATAVENCIMENTOORIGINAL" => $this->data_vencimento_original,
                "DATAVENCIMENTOREAL" => $this->data_vencimento_real,
                "CHEQUE" => $this->cheque,
                "VALOR" => $this->valor,
                "VALORTOTAL" => $this->valor,
            ));

        retornoPadrao($stmt, "Dados da duplicata #{$this->numero} salvos", "N�o foi poss�vel atualizar a duplicata #{$this->numero}");

        /* se for um boleto, calcula o pr�ximo nosso n�mero
         * n�o requer a passagem de nenhuma propriedade nova para o objeto
         */
        if ($this->forma_pagamento == self::FORMA_BOLETO) {
            // valida banco informado
            if (empty($this->banco)) {
                mensagem("Favor informar o banco para forma de pagamento boleto", MSG_ERRO);
                finaliza();
            }

            $sql = "UPDATE K_NOTADUPLICATAS SET
					NOSSONUMERO = (
						SELECT X.PROXIMO FROM ( -- we have to go deeper!
							SELECT ISNULL(MAX(CAST(D.NOSSONUMERO AS INTEGER)), 0) + 1 AS PROXIMO
							FROM K_NOTADUPLICATAS D
							WHERE FORMAPAGAMENTO = :boleto
							AND BANCO = :banco
						) X
					),
					BANCO = :banco
					WHERE HANDLE = :handle
					AND NOTA = :nota
					AND (NOSSONUMERO = '' OR NOSSONUMERO IS NULL)";
            $stmt = $conexao->prepare($sql);

            $stmt->bindValue(":handle", $this->handle);
            $stmt->bindValue(":nota", $this->nota);
            $stmt->bindValue(":banco", $this->banco); // vai precisar ser informado na tela!
            $stmt->bindValue(":boleto", self::FORMA_BOLETO);
            $stmt->execute();

            // s� acusa sucesso se for a primeira vez
            if ($stmt->rowCount() > 0) {
                mensagem("Registrando nosso n�mero para forma de pagamento boleto (C�d. banco: {$this->banco})");
            }
        }
    }

    public function baixa()
    {
        /* atualiza os valores BAIXADO e DATABAIXA
         * realiza integra��o com financeiro se houver obriga��o?
         */
        global $conexao;

        $stmt = $this->updateStatement("K_NOTADUPLICATAS",
            array(
                "HANDLE" => $this->handle,
                "DATABAIXA" => converteData(hoje()),
                "BAIXADO" => validaVazio($this->baixado),
                "VALORBAIXA" => validaVazio($this->valor_baixa),
                "VALORDESCONTO" => $this->valor_desconto,
                "CAIXA" => $this->numero_caixa,
            ));

        retornoPadrao($stmt, "Parcela #{$this->handle} teve status de baixa alterado", "N�o foi poss�vel atualizar o status de baixa da duplicata");

        // se for cancelamento de baixa, cancele os cr�ditos utilizados por esta nota tamb�m!
        if ($this->baixado == "N") {
            $sql = "UPDATE K_NOTA SET CREDITOUTILIZADO = NULL WHERE CREDITOUTILIZADO = :handle";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":handle", $this->nota);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                mensagem("Nota(s) de devolu��o com cr�dito compensado foram liberadas novamente.", MSG_AVISO);
            }
        }
    }

    public function cancela()
    {
        /* n�o "cancela" as duplicatas, s� deleta para recriar.
         * n�o pode ser em status or�amento ou forma boleto!
         */
        $this->deleteStatement("K_NOTADUPLICATAS", array("HANDLE" => $this->handle));

        mensagem("Apagando duplicata #{$this->handle}...");
    }

    public static function getFormaPagamento()
    {
        global $conexao;

        // tras os indices da lista
        $sql = "SELECT NOME, HANDLE FROM FN_FORMASPAGAMENTO";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($listas)) {
            $arr = array();
            foreach ($listas as $r) {
                $arr["handle"][] = $r->HANDLE;
                $arr["nome"][] = $r->NOME;
            }
            return $arr;
        }
    }

    public static function getCondicaoPagamento()
    {
        global $conexao;

        // tras os indices da lista
        $sql = "SELECT DESCRICAO, HANDLE FROM CP_CONDICOESPAGAMENTO";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($listas)) {
            $arr = array();
            foreach ($listas as $r) {
                $arr["handle"][] = $r->HANDLE;
                $arr["nome"][] = $r->DESCRICAO;
            }
            return $arr;
        }
    }

    public static function getPrefixo()
    {
        global $conexao;
        // lista din�mica de prefixos
        $sql = "SELECT NUMERO, NOME FROM K_PREFIXO ORDER BY NUMERO ASC"; // poss�vel regra de filtrar filial aqui
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        if(empty($f)) {
            $arr["handle"] = array("", "1", "2");
            $arr["nome"] = array("", "1", "2");
        }
        else {
            foreach($f as $r) {
                $arr["handle"][] = $r->NUMERO;
                $arr["nome"][] = $r->NUMERO.") ". $r->NOME;
            }
        }
        return $arr;
    }

    public static function getBanco()
    {
        $arr = array(
            "" => "",
            "001" => "Banco do Brasil",
            "033" => "Santander",
            "104" => "Caixa",
            "341" => "Ita� Unibanco",
            "756" => "Sicoob",
            "237" => "Bradesco"
        );

        return $arr;
    }
}