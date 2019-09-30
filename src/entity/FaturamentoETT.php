<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 11:28
 */

namespace src\entity;

use ExtPDO as PDO;

class FaturamentoETT extends ObjectETT
{
// finalidades de nota
    const FINALIDADE_NORMAL = 1;
    const FINALIDADE_COMPLEMENTAR = 2;
    const FINALIDADE_AJUSTE = 3;
    const FINALIDADE_DEVOLUCAO = 4;

    // origens de compra/venda
    const ORIGEM_SISTEMA = 1;
    const ORIGEM_LOJA_VIRTUAL = 2;
    const ORIGEM_PDV = 3;
    const ORIGEM_API = 4;
    const ORIGEM_CONTRATO = 5;

    // propriedades
    public $nota;
    public $filial;
    public $fatura;                // S | N - reservado para contratos
    public $tipo;                // E - entrada | S - sa�da
    public $origem;
    public $destino;            // 1 - interna | 2 - interestadual | 3 - exterior
    public $finalidade;            // 1 - normal  | 2 - complementar  | 3 - ajuste   | 4 - devolu��o
    public $numero;                // numera��o alternativa ao handle da nota -- por enquanto inutilizado! (NUMORCAMENTO)
    public $folha_pagamento;    // numera��o alternativa para folhas/contratos, tamb�m inutilizado
    public $data_emissao;
    public $pessoa;
    public $usuario;
    public $vendedor;
    public $supervisor;            // para comiss�es. n�o � utilizado mais no gest�o?
    public $plano_contas;        // usado nos contratos; compras e vendas t�m plano padr�o do sistema
    public $doc_fornecedor;        // n�mero de nota do fornecedor (compras)
    public $fonte;

    public $descricao;            // campos de texto
    public $historico;
    public $contrato;

    public $data_inicio;        // dados da folha de pagamento / contrato firmado
    public $data_termino;        // ||
    public $dia_vencimento;        // ||
    public $data_fatura;        // �ltima fatura executada
    public $prefixo_contrato;    // prefixo default do contrato (EXTRA3)

    public $valor_total;        // total da nota (somat�rio dos produtos + impostos)

    public $valor_creditado;    // para gera��o de nota/vale de devolu��o - regra espec�fica gest�o de tudo
    public $credito_utilizado;    // handle da nota que baixou o cr�dito gerado

    // componentes (�rvore de objetos da nota!)
    public $produtos;
    public $duplicatas;
    public $entrega;
    public $comissao;
    public $nota_fiscal;
    public $total_produtos;        // totalizadores (gui)
    public $total_duplicatas;

    public $status;                // propriedades do status (nome, cor, grupo...)

    // ** apenas gui
    public $cod_filial;
    public $cod_tipo;
    public $cod_origem;
    public $cod_pessoa;
    public $cod_usuario;
    public $cod_vendedor;
    public $cod_plano_contas;
    public $cod_prefixo_contrato;
    public $cod_fonte;
    public $uf_pessoa;          //relatorio simplificado

    public $numero_doc;            // financeiro gerado (primeira parcela)
    public $forma_pagamento;    // 0 - � vista | 1 - � prazo | 2 - outros
    public $email;                // prefetch para emiss�o da nota fiscal
    public $email_responsavel;    // ||
    public $cod_transacao;        // o c�digo salvo do pagseguro (EXTRA1)
    public $cod_correios;        // informado na expedi��o (EXTRA2)
    public $total_relatorio;

    // propriedades para a loja virtual
    public $status_loja_virtual;
    public $url_boleto_loja_virtual;
    public $forma_pagamento_loja_virtual;

    // retornar informa�?es da xml
    public $xml;
    public $dados_retorno;
    public $txt_cupom;
    public $protocolo;
    public $chave;

    // ----------------------------------------------------------------------------
    public function __construct($handle = null)
    {
        global $conexao;

        // gera��o do handle no construtor. garante a integridade dos objetos abaixo!
        $this->handle = $handle;
        if(empty($handle)){
            $this->handle = newHandle("K_NOTA", $conexao);
        }

        // define se a entrada � uma nota de faturamento ou folha de pagamento (futuro)
        $this->fatura = "S";

        // gera estrutura de objetos
        $this->produtos = array();
        $this->duplicatas = array();
        $this->comissao = array();
        $this->entrega = new FaturamentoEntregaETT($this->handle);
        $this->nota_fiscal = new FaturamentoNotaFiscalETT($this->handle);
        $this->total_produtos = new FaturamentoProdutoServicoETT($this->handle);
        $this->total_duplicatas = new FaturamentoDuplicataETT($this->handle);
        $this->status = new FaturamentoStatusETT();
    }

    public function setNota($nota)
    {
        $this->handle = $nota;
        $this->nota = $nota;
        $this->entrega = new FaturamentoEntregaETT($nota);
        $this->nota_fiscal = new FaturamentoNotaFiscalETT($nota);
        $this->total_produtos = new FaturamentoProdutoServicoETT($nota);
        $this->total_duplicatas = new FaturamentoDuplicataETT($nota);
        $this->status = new FaturamentoStatusETT();
    }

    public function validaForm(){
        global $transact;
        // campos obrigatorios
        $transact->validaCampo($this->handle, "Handle");
        $transact->validaCampo($this->finalidade, "Finalidade");
        $transact->validaCampo($this->data_emissao, "Data emiss�o");
        $transact->validaCampo($this->status->handle, "Status");
        $transact->validaCampo($this->cod_pessoa, "Pessoa");
    }

    public function cadastra()
    {
        $this->cod_filial = __FILIAL__;
        $this->cod_usuario = $_SESSION["ID"];

        // muda o tipo de nota se a finalidade for devolu��o
        if ($this->finalidade == self::FINALIDADE_DEVOLUCAO) {
            $this->tipo = ($this->tipo == "S") ? "E" : "S";

            mensagem("Finalidade devolu��o: trocando tipo de nota para <b>{$this->tipo}</b>", MSG_AVISO);
        }
        // insere
        $stmt = $this->insertStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "FILIAL" => validaVazio($this->cod_filial),
                "USUARIO" => validaVazio($this->cod_usuario),
                "FATURA" => left($this->fatura, 1),
                "TIPO" => left($this->cod_tipo, 1),
                "ORIGEM" => $this->cod_origem,
            ));

        retornoPadrao($stmt, "Alocando tabela de faturamento...", "N�o foi poss�vel fazer o cadastro inicial do faturamento");

        $this->atualiza();
    }

    public function atualiza()
    {
        $this->validaForm();

        if(empty($this->cod_vendedor)){
            $this->cod_vendedor = $this->cod_usuario;
        }
        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "DESTINO" => $this->destino,
                "FINALIDADE" => $this->finalidade,
                "NUMORCAMENTO" => $this->handle,
                "FOLHAPAGAMENTO" => $this->folha_pagamento,
                "DATA" => $this->data_emissao,
                "STATUS" => $this->status->handle,
                "FONTE" => $this->cod_fonte,
                "PESSOA" => validaVazio($this->cod_pessoa),
                //"USUARIO" => validaVazio($this->usuario),
                "VENDEDOR" => validaVazio($this->cod_vendedor),
                "SUPERVISOR" => $this->supervisor,
                "PLANOCONTAS" => validaVazio($this->cod_plano_contas),
                "DESCRICAO" => left($this->descricao, 250),
                "HISTORICO" => $this->historico,
                "CONTRATO" => $this->contrato,
                "DATAINICIO" => $this->data_inicio,
                "DATATERMINO" => $this->data_termino,
                "DIAVENCIMENTO" => $this->dia_vencimento,
                "VALORTOTAL" => $this->valor_total,
                "DOCFORNECEDOR" => $this->doc_fornecedor,
                "EXTRA3" => validaVazio($this->prefixo_contrato),
            ));

        retornoPadrao($stmt, "Dados do faturamento #{$this->handle} salvos", "N�o foi poss�vel atualizar a nota de faturamento");

        $this->nota_fiscal->atualiza();
        $this->entrega->atualiza();
    }

    /* atualiza para um status de forma literal (passa o nome, n�o o c�digo)
     * dessa forma, permite que o usu�rio parametrize os status na ordem que quiser,
     * desde que alguns status sejam cadastrados com estes nomes obrigat�rios
     */
    public function atualizaStatus($nome_status)
    {
        global $conexao;

        // valida status vazio (isso pode ser desastroso!)
        if (empty($nome_status)) {
            mensagem("atualizaStatus: nome vazio!", MSG_ERRO);
            finaliza();
        }

        // testa se o status correspondente existe
        $sql = "SELECT HANDLE FROM K_STATUS WHERE NOME = :nomestatus";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":nomestatus", $nome_status);
        $stmt->execute();
        $status = $stmt->fetch(PDO::FETCH_OBJ);

        // se n�o houver o status marcado, tenta cadastrar
        if (empty($status->HANDLE)) {
            $stmt = $this->insertStatement("K_STATUS",
                array(
                    "HANDLE" => newHandle("K_STATUS", $conexao),
                    "NOME" => $nome_status,
                    "COR" => 7,
                    "GRUPOENTRADA" => null,
                    "GRUPOSAIDA" => null,
                ));

            retornoPadrao($stmt, "O sistema cadastrou o status obrigat�rio '{$nome_status}' que n�o existia", "N�o foi poss�vel cadastrar o status obrigat�rio '{$nome_status}'");

            // tenta novamente
            $this->atualizaStatus($nome_status);
            return;
        }

        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "STATUS" => $status->HANDLE,
            ));

        retornoPadrao($stmt, "Status da nota atualizado para {$nome_status}", "N�o foi poss�vel atualizar o status da nota");
    }

    /* salva o valor de cr�dito da nota de devolu��o (regra espec�fica e-commerce/gest�o de tudo)
     * deve usar estoque() de alguma forma para controlar as quantidades devolvidas,
     * sen�o a rotina de devolu��o deve ser bloqueada para rodar uma s� vez!
     */
    public function atualizaCredito()
    {
        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "VALORCREDITADO" => $this->valor_creditado,
                "CREDITOUTILIZADO" => null,
            ));

        retornoPadrao($stmt, "Valor dos cr�ditos salvo na nota #{$this->handle}", "N�o foi poss�vel atualizar o valor dos cr�ditos");
    }

    // marca o valor de cr�dito como utilizado por outra nota.
    public function utilizaCredito($nota)
    {
        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "CREDITOUTILIZADO" => $nota,
            ));

        retornoPadrao($stmt, "Cr�ditos da nota #{$this->handle} compensados pela nota #{$nota}", "N�o foi poss�vel compensar o cr�dito da nota {$nota}");
    }

    /* guarda o c�digo de retorno das operadoras de pagamento na nota especificada.
     * usa o campo EXTRA1 (?)
     */
    public function atualizaCodigoTransacao($valor)
    {
        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "EXTRA1" => $valor,
            ));

        retornoPadrao($stmt, "Campo extra alterado para {$valor}", "N�o foi poss�vel atualizar o campo extra");
    }

    // atualiza informa��es de expedi��o
    public function atualizaExpedicao()
    {
        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->handle,
                "EXTRA2" => $this->cod_correios,
            ));

        retornoPadrao($stmt, "Dados de expedi��o salvos", "n�o foi poss�vel atualizar os dados de expedi��o");
    }

    // nomes de propriedades
    public function getNomeTipo($tipo)
    {
        switch ($tipo) {
            case "E":
                return "Entrada";
            case "S":
                return "Sa�da";
            default:
                return "Indefinido";
        }
    }

    public static function getHistorico(){
        global $conexao;

        // tras os indices da lista
        $sql = "SELECT TEXTO FROM K_HISTORICO WHERE ATIVO = 'S'";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if(!empty($listas)){
            $arr = array();
            foreach($listas as $r){
                $arr["handle"][] = $r->TEXTO;
                $arr["nome"][] = $r->TEXTO;
            }
            return $arr;
        }
    }

    public static function getNomeOrigem($origem, $lista = false)
    {
        $origens = array(
            self::ORIGEM_SISTEMA => "Sistema",
            self::ORIGEM_LOJA_VIRTUAL => "Loja virtual",
            self::ORIGEM_PDV => "PDV",
            self::ORIGEM_API => "API",
            self::ORIGEM_CONTRATO => "Contrato",
        );

        if($lista){
            return $origens;
        }
        return $origens[$origem];
    }

    public static function getFinalidade($finalidade, $lista = false)
    {
        $arr = array(
            self::FINALIDADE_NORMAL => "Normal",
            self::FINALIDADE_COMPLEMENTAR => "Complementar",
            self::FINALIDADE_AJUSTE => "Ajuste",
            self::FINALIDADE_DEVOLUCAO => "Devolu��o"
        );
        if($lista){
            return $arr;
        }

        return $arr[$finalidade];
    }

    // converte tipo para num�rico nota fiscal
    public function getCodigoTipo()
    {
        switch ($this->cod_tipo) {
            case "E":
                return 0;
            case "S":
                return 1;
            default:
                return "?";
        }
    }

    /* definindo as interfaces de Exportacao aqui just in case.
     * se por acaso as chamadas s�o feitas em Nota, causa erro fatal
     */
    public function exportaXMLNotaFiscal()
    {
        return "Objeto Nota n�o implementa XML. Use Exporta��o";
    }

    public function importaXMLNotaFiscal()
    {
        return false;
    }

    public function trataXML($conteudo)
    {
        return "Objeto Nota n�o implementa XML. Use Exporta��o";
    }

    public function getErrosExportacao()
    {
        return array("Objeto Nota n�o implementa exporta��o");
    }

    public function exportaTXTCupom()
    {
        return "Objeto Nota n�o exporta cupom. Use Exporta��o";
    }

    /* atualiza��o de totalizadores:
     * separando porque isso pode ser usado tanto em fetchSingle()
     * quanto na importa��o de notas de fornecedor
     */
    public function atualizaTotaisProduto(FaturamentoProdutoServicoETT $produto)
    {
        $this->total_produtos->quantidade += $produto->quantidade;
        $this->total_produtos->quantidade_entregue += $produto->quantidade_entregue;
        $this->total_produtos->quantidade_baixada += $produto->quantidade_baixada;
        $this->total_produtos->quantidade_saldo += $produto->quantidade_saldo;
        $this->total_produtos->medida_t += $produto->medida_t;
        $this->total_produtos->peso += $produto->peso;
        $this->total_produtos->valor_unitario += $produto->valor_unitario;
        $this->total_produtos->valor_bruto += $produto->valor_bruto;
        $this->total_produtos->valor_desconto += $produto->valor_desconto;
        $this->total_produtos->valor_ipi += $produto->valor_ipi;
        $this->total_produtos->valor_bc_ipi += $produto->valor_bc_ipi;
        $this->total_produtos->valor_total += $produto->valor_total;
        $this->total_produtos->valor_icms += $produto->valor_icms;
        $this->total_produtos->valor_bc_icms += $produto->valor_bc_icms;
        $this->total_produtos->valor_frete += $produto->valor_frete;
        $this->total_produtos->valor_pis += formataValor($produto->valor_pis);
        $this->total_produtos->valor_cofins += formataValor($produto->valor_cofins);
        $this->total_produtos->valor_issqn += formataValor($produto->valor_issqn);
        $this->total_produtos->valor_bc_issqn += $produto->valor_bc_issqn;
        $this->total_produtos->valor_icms_st += $produto->valor_icms_st;
        $this->total_produtos->valor_bc_icms_st += $produto->valor_bc_icms_st;
        $this->total_produtos->valor_cofins_st += $produto->valor_cofins_st;
        $this->total_produtos->valor_total_tributos += $produto->valor_total_tributos;

        // total da nota
        $this->total_produtos->valor_total_nota += $produto->valor_bruto
            - $produto->valor_desconto
            + $produto->valor_icms_st
            + $produto->valor_frete
            + $produto->valor_ipi;
    }

    public function getNotas($handle){
        $notasGUI = new FaturamentoGUI($handle);
        $notasGUI->usa_exportacao = true;
        $notasGUI->fetch();
        $nota = $notasGUI->itens[0];

        $xml_nfe = "uploads/xml/nfe{$nota->nota_fiscal->chave}.xml";
        if(file_exists($xml_nfe)) {
            $xml = file_get_contents($xml_nfe);
            $xml = utf8_decode($xml);
            $erros = array();

            // confere se existe protocolo de aprova��o
            $xml_prot = "uploads/xml/prot{$nota->nota_fiscal->chave}.xml";

            if(file_exists($xml_prot)) {
                $protocolo = file_get_contents($xml_prot);
                $protocolo_inicio = strpos($protocolo, "<protNFe");
                $protocolo_fim = strpos($protocolo, "</protNFe") + 10; //soma os caracteres da string
                $protocolo = substr($protocolo, $protocolo_inicio, ($protocolo_fim - $protocolo_inicio));

                $xml = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "", $xml);
                $xml = str_replace("<?xml version='1.0' encoding='UTF-8'?>", "", $xml);
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><nfeProc versao=\"3.10\" xmlns=\"http://www.portalfiscal.inf.br/nfe\">{$xml}{$protocolo}</nfeProc>";
            }
        }
        else {
            $this->xml = $nota->exportaXMLNotaFiscal();
            $erros = $nota->getErrosExportacao();
        }
        $this->txt_cupom = $nota->exportaTXTCupom();

        $retorno = $nota->nota_fiscal->xml_retorno;
        $retorno = str_replace("><", ">\n<", $retorno); // quebra de linha tupiniquim
//        $retorno = str_replace("&lt;", "<", $retorno); // quebra de linha tupiniquim
        $this->dados_retorno = $retorno; //str_replace("&gt;", ">", $retorno); // quebra de linha tupiniquim


        $this->protocolo = $nota->nota_fiscal->protocolo;
        $this->chave = $nota->nota_fiscal->chave;
    }
}