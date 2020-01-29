<?php


namespace src\entity;


class FaturamentoOperacaoETT extends ObjectETT
{
    // constantes
    const DESTINO_ESTADUAL = 1;
    const DESTINO_INTERESTADUAL = 2;
    const DESTINO_EXTERIOR = 3;

    const ICMS_NORMAL = 1;
    const ICMS_SUBSTITUICAO = 2;
    const ICMS_OUTROS = 3;

    // propriedades
    public $codigo;				// 3 d�gitos | 0-499 = entrada | 500-999 = sa�da
    public $nome;
    public $descricao;			// hist�rico legisla��o

    // classifica��o
    public $tipo_movimento;		// E/S
    public $modalidade;			// propriedade ainda n�o utilizada
    // public $destino;			// 1 - estadual | 2 - interestadual | 3 - exterior
    // public $estado;			// ainda n�o utilizado. se precisar definir...

    // CFOP e CST
    public $cfop;				// c�digo 9.999
    public $cst_origem;			// primeiro d�gito
    public $cst_tributacao;		// �ltimos 2 d�gitos (ICMS)
    public $csosn;				// c�digo tribut�rio para simples nacional
    public $cst_pis_cofins;		// c�digo de 2 d�gitos espec�fico para pis e cofins
    public $cst_ipi;			// c�digo de 2 d�gitos separado para IPI

    // ICMS
    public $tipo_icms;			// 1 - normal | 2 - substitui��o | 3 - outros
    public $base_calculo_icms;	// %

    // IPI
    public $enquadramento_ipi;	// cEnq - tabela que casa com CST do IPI

    // booleanos
    public $gera_financeiro;	// enfor�ar na exporta��o de financeiro
    public $tributado;			// ainda n�o � utilizado
    public $credita_icms;		// enfor�ar na tela de faturamento (json produto_valores?)
    public $credita_ipi;		// ||
    public $substituicao_tributaria; // nem entramos nisso ainda

    // al�quotas (s� n�o define IPI que � do NCM)
    public $aliquota_icms;		// inutilizado; deve ser um fallback?
    public $aliquota_pis;
    public $aliquota_cofins;
    public $aliquota_issqn;

    // apenas gui
    public $cod_tipo_movimento;
    public $cod_modalidade;
    public $cod_destino;
    public $cod_tipo_icms;
    public $cst;				// completo 3 d�gitos

    // ---------------------------------------------------------------
    public function cadastra() {
        global  $conexao;

        // � importante setar handle na propriedade ao inv�s de passar direto para o sql
        $this->handle = newHandle("K_TIPOOPERACAO", $conexao);

        // insere cabe�alho
        $stmt = $this->insertStatement("K_TIPOOPERACAO", array(
            "HANDLE" => $this->handle,
            "CODIGO" => $this->codigo,
            "TIPO" => $this->tipo_movimento,
            "NOME" => $this->nome
        ));

        retornoPadrao($stmt, "Tipo de opera��o cadastrado com sucesso", "N�o foi poss�vel cadastrar o tipo de opera��o");

        // passa para atualiza��o dos demais dados
        $this->atualiza();
    }

    public function atualiza() {
        // valida tamanho do c�digo
        if(strlen($this->codigo) != 3) {
            mensagem("C�digo num�rico deve ter 3 d�gitos", MSG_ERRO);
            finaliza();
        }

        $stmt = $this->updateStatement("K_TIPOOPERACAO", array(
            "HANDLE" => $this->handle,
            "CODIGO" => left($this->codigo, 3),
            "NOME" => left($this->nome, 250),
            "DESCRICAO" => left($this->descricao, 250),
            "TIPO" => $this->tipo_movimento,
            "MODALIDADE" => $this->modalidade,
            "CFOP" => left($this->cfop, 5),
            "CSTORIGEM" => left($this->cst_origem, 1),
            "CSTTRIBUTACAO" => left($this->cst_tributacao, 2),
            "CSTIPI" => left($this->cst_ipi, 2),
            "CSTPISCOFINS" => left($this->cst_pis_cofins, 2),
            "CSOSN" => left($this->csosn, 3),
            "CENQIPI" => left($this->enquadramento_ipi, 3),
            "GERAFINANCEIRO" => left($this->gera_financeiro, 1),
            "TRIBUTADO" => left($this->tributado, 1),
            "CREDITAICMS" => left($this->credita_icms, 1),
            "CREDITAIPI" => left($this->credita_ipi, 1),
            "SUBSTITUICAOTRIB" => left($this->substituicao_tributaria, 1),
            "TIPOICMS" => $this->tipo_icms,
            "CALCULOBASE" => $this->base_calculo_icms,
            "ALIQUOTAICMS" => null, // inutilizado!
            "ALIQUOTAPIS" => $this->aliquota_pis,
            "ALIQUOTACOFINS" => $this->aliquota_cofins,
            "ALIQUOTAISSQN" => $this->aliquota_issqn
        ));

        retornoPadrao($stmt, "Tipo de opera��o atualizado com sucesso", "N�o foi poss�vel atualizar o tipo de opera��o");
    }

    /*
    public function getNomeDestino($destino) {
        switch($destino) {
            case self::DESTINO_ESTADUAL: 		return "Estadual";
            case self::DESTINO_INTERESTADUAL: 	return "Interestadual";
            case self::DESTINO_EXTERIOR: 		return "Exterior";
            default:							return "Indefinido";
        }
    }
    */
}