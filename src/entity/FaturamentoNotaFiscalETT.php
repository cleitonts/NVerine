<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 15:00
 */

namespace src\entity;


class FaturamentoNotaFiscalETT extends ObjectETT
{
    const MODELO_NFE = 55;
    const MODELO_NFC = 65;

    // nota � qual pertence (setado no construtor)
    protected $nota;

    // identifica��o da nota fiscal, numera��o, aprova��o
    public $numero;                // enquanto n�o for salvo, calcula a numera��o base em fetchSingle()
    public $chave;                /* quando a nota for gerada, a chave vai ser salva para refer�ncia e consulta
								 * mas � apenas uma uni�o de alguns dados da nota + o c�digo de acesso
								 */
    public $chave_referencia;    // chave da nota fiscal para devolu��o ou ajuste
    public $protocolo;            // possui protocolo: status = aprovada
    public $xml_retorno;        // string completa de retorno da aprova��o ou do cancelamento ("carimbo")
    public $data_emissao;        // para auditoria do envio (diferente da data de emiss�o do cabe�alho!)
    public $hora_emissao;        // ||

    public $serie;                // usamos s�rie 1
    public $lote;                // deve ser informado apenas para processamento ass�ncrono
    public $recibo;                // recibo do lote para consulta posterior

    // propriedades espec�ficas da nota fiscal
    public $versao;                // transi��o 3.10 | 4.00
    public $modelo;                // define se vamos gerar uma NF-e normal ou NF consumidor
    public $natureza_operacao;    // para fins fiscais
    public $informacoes_fisco;    // informa��es padr�o do modelo tribut�rio da empresa

    // n�o cadastra, s�o par�metros para a emiss�o -- controlados pelo MODELO
    public $tipo_emissao;
    public $tipo_impressao;
    public $consumidor_final;
    public $operacao_presencial;

    // estas propriedades s�o alimentadas por montaChave().
    public $codigo_acesso;        // um n�mero aleat�rio de 8 d�gitos que comp�e a chave
    public $chave_dv;            // d�gito verificador (m�dulo 11)

    // endere�o do QR Code da consulta de NFC-e | gerado por FaturamentoExportacao::montaQRCode()
    public $qr_code;

    // ----------------------------------------------------------------------------
    // m�todos p�blicos
    public function __construct($nota)
    {
        $this->nota = $nota;

        // s�rie padr�o � 1. definir aqui ou chamar o cadastrado em NotaGUI::fetch
        $this->serie = 1;

        // transi��o de vers�o das notas
        $this->versao = "4.00";
    }

    public function cadastra()
    {
        // este m�todo n�o cadastra porque salva na tabela da nota. use se a estrutura for alterada.
        return false;
    }

    /* a atualiza��o de protocolo, data e hora deve ser feita por outra rotina espec�fica!
     * isso atualiza os dados do cabe�alho do faturamento, n�o registra uma emiss�o de nota fiscal
     */
    public function atualiza()
    {
        // para a chave de refer�ncia informada manualmente, descartar os poss�veis prefixos
        $this->chave_referencia = str_ireplace("NFe", "", $this->chave_referencia);
        $this->chave_referencia = str_replace("#", "", $this->chave_referencia);
        $this->chave_referencia = str_replace(" ", "", $this->chave_referencia); // se por acaso o usu�rio digitar com espa�os...

        $campos = array(
            "HANDLE" => $this->nota,
            "CHAVEREFERENCIA" => left($this->chave_referencia, 44),
            "NATUREZAOPERACAO" => left($this->natureza_operacao, 60),
            "INFORMACOESFISCO" => left($this->informacoes_fisco, 250),
            "MODELO" => left($this->modelo, 2),
        );

        // o numero da nota pode ser cadastrado manualmente caso o parametro esteja positivo
        if (__EDITA_NUM_NF__) {
            $campos["NUMNOTA"] = $this->numero;
        }

        $stmt = $this->updateStatement("K_NOTA", $campos);

        retornoPadrao($stmt, "Dados de nota fiscal salvos", "N�o foi poss�vel atualizar os dados da nota fiscal");
    }

    /* gera numera��o de nota e salva recibo para consulta de lote
     * (para webservices que s� suportam processamento ass�ncrono -- isso � um p� no saco!)
     */
    public function salvaRecibo($protocolo = "PROCESSAM")
    {
        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "LOTE" => $this->lote,
                "NUMNOTA" => $this->numero,
                "CHAVE" => left($this->chave, 44),
                "PROTOCOLO" => $protocolo,
                "RECIBO" => left($this->recibo, 15),
                "XMLRETORNO" => $this->xml_retorno,
            ));

        retornoPadrao($stmt, "Lote registrado #{$this->lote} (Recibo n� {$this->recibo})",
            "N�o foi poss�vel salvar o n�mero do recibo ({$this->recibo})");
    }

    /* gera numera��o de nota fiscal
     * altera o protocolo de aprova��o, guarda o xml de retorno
     * essa rotina � chamada no envio de nota para aprova��o, mas tamb�m no cancelamento!
     */
    public function aprova()
    {
        // n�o precisa realmente puxar a timestamp exata da resposta; salve a hora de agora (aproximada)
        if (empty($this->data_emissao)) $this->data_emissao = hoje();
        if (empty($this->hora_emissao)) $this->hora_emissao = date("H:i:s");

        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "NUMNOTA" => $this->numero,
                "CHAVE" => left($this->chave, 44),
                "DATANOTA" => $this->data_emissao,
                "HORANOTA" => left($this->hora_emissao, 8),
                "PROTOCOLO" => left($this->protocolo, 15),
                "XMLRETORNO" => $this->xml_retorno,
            ));

        retornoPadrao($stmt, "Protocolo registrado para a nota fiscal {$this->chave}",
            "O protocolo de aprova��o foi recebido, mas n�o p�de ser salvo! Guarde: {$this->protocolo}");
    }

    /* gera a string de chave da nota fiscal completa.
     * n�mero da nota fiscal precisa ser informado anteriormente!
     * @obj_empresa: dados da classe Empresa (filial)
     */
    public function montaChave(FilialETT $obj_empresa)
    {
        // gera c�digo de acesso (n�mero aleat�rio)
        srand($this->nota); // seed do gerador de n�meros aleat�rios � a chave prim�ria do faturamento
        $this->codigo_acesso = rand(10000001, 99999999);

        // se est� gerando uma nova chave, ainda n�o h� valores de data de emiss�o, s�rie e n�mero.
        if (empty($this->data_emissao)) $this->data_emissao = hoje();
        $ano = substr(converteDataSqlOrdenada($this->data_emissao), 2, 2); // esses �tomos s�o necess�rios para a chave
        $mes = substr(converteDataSqlOrdenada($this->data_emissao), 5, 2);    // ||

        if (empty($this->hora_emissao)) $this->hora_emissao = date("H:i:s");

        // se n�o houver modelo salvo, � NF-e
        if (empty($this->modelo)) $this->modelo = self::MODELO_NFE;

        // faz tratativas por modelo
        if ($this->modelo == self::MODELO_NFE) {
            $this->tipo_impressao = 1;        // formato danfe retrato
            $this->consumidor_final = 0;    // opera��o normal
            $this->operacao_presencial = 9;    // opera��o n�o presencial, outros
        } elseif ($this->modelo == self::MODELO_NFC) {
            $this->tipo_impressao = 4;        // formato danfe nota consumidor (qual especifica��o?)
            $this->consumidor_final = 1;    // opera��o consumidor final
            $this->operacao_presencial = 1;    // opera��o presencial
        }

        // o primeiro d�gito de s�rie n�o pode ser zero?.
        if (empty($this->serie)) $this->serie = 1;

        // o tipo de emiss�o 1 - NORMAL s� vai mudar se houver algum tipo de conting�ncia.
        $this->tipo_emissao = 1;

        // gera chave completa
        $chave = $obj_empresa->endereco->cod_estado_ibge    // cUF
            . $ano . $mes                                    // "AAMM"
            . apenasNumeros($obj_empresa->cpf_cnpj)            // CNPJ emitente
            . $this->modelo                                // mod
            . insereZeros($this->serie, 3)                // serie
            . insereZeros($this->numero, 9)                // nNF
            . $this->tipo_emissao                        // TpEmis
            . $this->codigo_acesso;                        // cNF

        // c�lculo do d�gito verificador
        $this->chave_dv = $this->modulo11($chave);

        // salva a chave na propriedade.
        $this->chave = $chave . $this->chave_dv;

        // retorna true ou false para tratamento de erro se o tamanho da chave for v�lido
        if (strlen($this->chave) == 44)
            return true;
        else
            return false;
    }
    // ----------------------------------------------------------------------------
    // m�todos privados

    /* c�lculo do m�dulo 11 para d�gito verificador
     */
    protected function modulo11($num, $base = 9, $r = 0)
    {
        $soma = 0;
        $fator = 2;

        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }

        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;
            if ($digito == 10) $digito = 0;
            return $digito;
        } elseif ($r == 1) {
            $resto = $soma % 11;
            return $resto;
        }
    }
}