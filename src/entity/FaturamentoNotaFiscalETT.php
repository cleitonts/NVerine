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

    // nota à qual pertence (setado no construtor)
    protected $nota;

    // identificação da nota fiscal, numeração, aprovação
    public $numero;                // enquanto não for salvo, calcula a numeração base em fetchSingle()
    public $chave;                /* quando a nota for gerada, a chave vai ser salva para referência e consulta
								 * mas é apenas uma união de alguns dados da nota + o código de acesso
								 */
    public $chave_referencia;    // chave da nota fiscal para devolução ou ajuste
    public $protocolo;            // possui protocolo: status = aprovada
    public $xml_retorno;        // string completa de retorno da aprovação ou do cancelamento ("carimbo")
    public $data_emissao;        // para auditoria do envio (diferente da data de emissão do cabeçalho!)
    public $hora_emissao;        // ||

    public $serie;                // usamos série 1
    public $lote;                // deve ser informado apenas para processamento assíncrono
    public $recibo;                // recibo do lote para consulta posterior

    // propriedades específicas da nota fiscal
    public $versao;                // transição 3.10 | 4.00
    public $modelo;                // define se vamos gerar uma NF-e normal ou NF consumidor
    public $natureza_operacao;    // para fins fiscais
    public $informacoes_fisco;    // informações padrão do modelo tributário da empresa

    // não cadastra, são parâmetros para a emissão -- controlados pelo MODELO
    public $tipo_emissao;
    public $tipo_impressao;
    public $consumidor_final;
    public $operacao_presencial;

    // estas propriedades são alimentadas por montaChave().
    public $codigo_acesso;        // um número aleatório de 8 dígitos que compõe a chave
    public $chave_dv;            // dígito verificador (módulo 11)

    // endereço do QR Code da consulta de NFC-e | gerado por FaturamentoExportacao::montaQRCode()
    public $qr_code;

    // ----------------------------------------------------------------------------
    // métodos públicos
    public function __construct($nota)
    {
        $this->nota = $nota;

        // série padrão é 1. definir aqui ou chamar o cadastrado em NotaGUI::fetch
        $this->serie = 1;

        // transição de versão das notas
        $this->versao = "4.00";
    }

    public function cadastra()
    {
        // este método não cadastra porque salva na tabela da nota. use se a estrutura for alterada.
        return false;
    }

    /* a atualização de protocolo, data e hora deve ser feita por outra rotina específica!
     * isso atualiza os dados do cabeçalho do faturamento, não registra uma emissão de nota fiscal
     */
    public function atualiza()
    {
        // para a chave de referência informada manualmente, descartar os possíveis prefixos
        $this->chave_referencia = str_ireplace("NFe", "", $this->chave_referencia);
        $this->chave_referencia = str_replace("#", "", $this->chave_referencia);
        $this->chave_referencia = str_replace(" ", "", $this->chave_referencia); // se por acaso o usuário digitar com espaços...

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

        retornoPadrao($stmt, "Dados de nota fiscal salvos", "Não foi possível atualizar os dados da nota fiscal");
    }

    /* gera numeração de nota e salva recibo para consulta de lote
     * (para webservices que só suportam processamento assíncrono -- isso é um pé no saco!)
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

        retornoPadrao($stmt, "Lote registrado #{$this->lote} (Recibo nº {$this->recibo})",
            "Não foi possível salvar o número do recibo ({$this->recibo})");
    }

    /* gera numeração de nota fiscal
     * altera o protocolo de aprovação, guarda o xml de retorno
     * essa rotina é chamada no envio de nota para aprovação, mas também no cancelamento!
     */
    public function aprova()
    {
        // não precisa realmente puxar a timestamp exata da resposta; salve a hora de agora (aproximada)
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
            "O protocolo de aprovação foi recebido, mas não pôde ser salvo! Guarde: {$this->protocolo}");
    }

    /* gera a string de chave da nota fiscal completa.
     * número da nota fiscal precisa ser informado anteriormente!
     * @obj_empresa: dados da classe Empresa (filial)
     */
    public function montaChave(FilialETT $obj_empresa)
    {
        // gera código de acesso (número aleatório)
        srand($this->nota); // seed do gerador de números aleatórios é a chave primária do faturamento
        $this->codigo_acesso = rand(10000001, 99999999);

        // se está gerando uma nova chave, ainda não há valores de data de emissão, série e número.
        if (empty($this->data_emissao)) $this->data_emissao = hoje();
        $ano = substr(converteDataSqlOrdenada($this->data_emissao), 2, 2); // esses átomos são necessários para a chave
        $mes = substr(converteDataSqlOrdenada($this->data_emissao), 5, 2);    // ||

        if (empty($this->hora_emissao)) $this->hora_emissao = date("H:i:s");

        // se não houver modelo salvo, é NF-e
        if (empty($this->modelo)) $this->modelo = self::MODELO_NFE;

        // faz tratativas por modelo
        if ($this->modelo == self::MODELO_NFE) {
            $this->tipo_impressao = 1;        // formato danfe retrato
            $this->consumidor_final = 0;    // operação normal
            $this->operacao_presencial = 9;    // operação não presencial, outros
        } elseif ($this->modelo == self::MODELO_NFC) {
            $this->tipo_impressao = 4;        // formato danfe nota consumidor (qual especificação?)
            $this->consumidor_final = 1;    // operação consumidor final
            $this->operacao_presencial = 1;    // operação presencial
        }

        // o primeiro dígito de série não pode ser zero?.
        if (empty($this->serie)) $this->serie = 1;

        // o tipo de emissão 1 - NORMAL só vai mudar se houver algum tipo de contingência.
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

        // cálculo do dígito verificador
        $this->chave_dv = $this->modulo11($chave);

        // salva a chave na propriedade.
        $this->chave = $chave . $this->chave_dv;

        // retorna true ou false para tratamento de erro se o tamanho da chave for válido
        if (strlen($this->chave) == 44)
            return true;
        else
            return false;
    }
    // ----------------------------------------------------------------------------
    // métodos privados

    /* cálculo do módulo 11 para dígito verificador
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