<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 11:09
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class FaturamentoDuplicataETT extends ObjectETT
{
    // códigos de forma de pagamento
    const FORMA_PAGSEGURO = 99;
    const FORMA_BOLETO = 13;
    const FORMA_DUPLICATA = 14;
    const FORMA_DINHEIRO = 1;
    const FORMA_SEM_PAGAMENTO = 11;

    // nota à qual pertence (setado no construtor)
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
    public $banco;                // código de 3 dígitos
    public $nosso_numero;        // sequencial do boleto
    public $boleto_hash;        // calculado no fetch
    public $boleto_url;            // ||

    // ** apenas gui
    public $tipo_pagamento_nfe;    // código de tipo de pagamento para nota fiscal
    public $cod_forma_pagamento;
    public $cod_prefixo;
    public $dias;
    public $intervalo;            // ?

    // minimo de segurança na alteração
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
        // não precisa do retorno padrão aqui porque a falha nesse insert vai ocasionar uma falha no próximo update.
        if (__GLASS_DEBUG__) retornoPadrao($stmt, "Alocando tabela de duplicatas...", "Não foi possível fazer o cadastro inicial da duplicata");

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

        retornoPadrao($stmt, "Dados da duplicata #{$this->numero} salvos", "Não foi possível atualizar a duplicata #{$this->numero}");

        /* se for um boleto, calcula o próximo nosso número
         * não requer a passagem de nenhuma propriedade nova para o objeto
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

            // só acusa sucesso se for a primeira vez
            if ($stmt->rowCount() > 0) {
                mensagem("Registrando nosso número para forma de pagamento boleto (Cód. banco: {$this->banco})");
            }
        }
    }

    public function baixa()
    {
        /* atualiza os valores BAIXADO e DATABAIXA
         * realiza integração com financeiro se houver obrigação?
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

        retornoPadrao($stmt, "Parcela #{$this->handle} teve status de baixa alterado", "Não foi possível atualizar o status de baixa da duplicata");

        // se for cancelamento de baixa, cancele os créditos utilizados por esta nota também!
        if ($this->baixado == "N") {
            $sql = "UPDATE K_NOTA SET CREDITOUTILIZADO = NULL WHERE CREDITOUTILIZADO = :handle";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":handle", $this->nota);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                mensagem("Nota(s) de devolução com crédito compensado foram liberadas novamente.", MSG_AVISO);
            }
        }
    }

    public function cancela()
    {
        /* não "cancela" as duplicatas, só deleta para recriar.
         * não pode ser em status orçamento ou forma boleto!
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
        // lista dinâmica de prefixos
        $sql = "SELECT NUMERO, NOME FROM K_PREFIXO ORDER BY NUMERO ASC"; // possível regra de filtrar filial aqui
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
            "341" => "Itaú Unibanco",
            "756" => "Sicoob",
            "237" => "Bradesco"
        );

        return $arr;
    }
}