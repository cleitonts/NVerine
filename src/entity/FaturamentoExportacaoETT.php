<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 16:17
 */

namespace src\entity;

include_once("class/Atualizacoes.php");
include_once("class/NotaFiscal.php");

use NotaFiscal4\DocumentoXML;
use Faturamento\NotaFiscal;
use SVN\Atualizacoes;
use XMLWriter;

class FaturamentoExportacaoETT extends FaturamentoImportacaoETT
{
// tipos de baixa automática
    const BAIXA_A_VISTA = 1;
    const BAIXA_TODAS = 2;
    const A_RECEBER = 3;

    // tipos de evento da nota fiscal a exportar
    const EV_CORRECAO = "110110";
    const EV_CANCELAMENTO = "110111";
    const EV_CIENCIA_OPERACAO = "210210";
    const INUTILIZACAO = "999990"; // inutilização é tratada como um evento, mas não é!

    // modelos de cupom
    const MODELO_DANFE_NFC = 1;
    const MODELO_NAO_FISCAL = 2;

    /* número de colunas da impressora do cupom fiscal
     * darumas são geralmente 40 ou 48, mas em 40 não dá pra mostrar nada.
     */
    const COLUNAS = 48;

    // define se ações de exportação são permitidas
    public $exportacao;

    // parâmetro de controle da nota fiscal: sistema em homologação ou produção
    public $tipo_ambiente;

    // parâmetros de evento da nota fiscal
    public $justificativa;

    // parâmetros do cupom (impressão/TXT)
    public $modelo_cupom;        // 1: DANFE | 2: pedido (não-fiscal)

    // uma interface externa para erros de exportação (array) - use getErrosExportacao()
    private $erros_exportacao;    // quando implantar a exportação do xml pelo actions, testar se há dados aqui e exibir com mensagem()

    // cópia dos objetos de exportação para interface interna e externa (envio de e-mails, cupom...)
    public $obj_empresa = FilialETT::class;
    public $obj_pessoa = PessoaEnderecoETT::class;
    public $obj_sistema = Atualizacoes::class;            // descrição/versão do sistema (SVN)

    // ----------------------------------------------------------------------------
    // métodos públicos
    public function __construct($nota = null)
    {
        // roda construtor de Importacao
        parent::__construct($nota);

        // instancia array de erros
        $this->erros_exportacao = array();

        // modelo padrão de cupom (txt)
        $this->modelo_cupom = self::MODELO_DANFE_NFC;

        // trata tipo de ambiente: homologação ou produção
        $this->tipo_ambiente = __HOMOLOGACAO__ ? 2 : 1;
    }

    /* gera objetos de exportação para interface externa e interna.
     * dados do cliente, empresa, e sistema
     *
     * converter as rotinas que usavam exportaXMLNotaFiscal para esse fim
     * para usar apenas essa chamada!
     */
    public function geraObjetosExportacao()
    {
        $pessoa = new PessoaGUI();
        $pessoa->top = "TOP 10";
        $pessoa->pesquisa["pesq_codigo"] = $this->cod_pessoa;
        $pessoa->fetch();
        $pessoa = $pessoa->itens[0];

        $empresa = new FilialGUI();
        $empresa->filial = $this->cod_filial;
        $empresa->fetch();
        $empresa = $empresa->itens[0];

        $sistema = new Atualizacoes(1);
        $sistema->fetch();
        $sistema = intval($sistema->itens[0]->versao);

        // guarda cópias dos dados cadastrais para interface externa
        $this->obj_empresa = $empresa;
        $this->obj_pessoa = $pessoa;
        $this->obj_sistema = $sistema;

        // puxa responsável financeiro, se houver
        $vinculos = new PessoaVinculoGUI();
        $vinculos->pessoa = $this->cod_pessoa;
        $vinculos->pesquisa["pesq_responsavel"] = "S";
        $vinculos->fetch();

        if (!empty($vinculos->itens)) {
            $vinculo = $vinculos->itens[0];

            // busca cadastro completo do responsável
            if (!empty($vinculo->cod_pai)) {
                $pessoa = new PessoaGUI();
                $pessoa->top = "TOP 10";
                $pessoa->pesquisa["pesq_codigo"] = $vinculo->cod_pai; // aluno é sempre o filho do vínculo
                $pessoa->fetch();
                $pessoa = $pessoa->itens[0];

                $this->obj_pessoa = $pessoa;
                $this->pessoa = $vinculo->nome_pai;
                $this->cod_pessoa = $vinculo->cod_pai;
            }
        }
    }

    /* rotina de montagem da estrutura do XML da nota fiscal.
     *
     * aqui não é permitida manipulação ou cálculo de dados
     * apenas informação de variáveis já carregadas ou constantes!
     *
     * o XML é formatado e identado; precisa ser tratado para exportação.
     *
     * a nota fiscal eletrônica adota uma nomenclatura HORRÍVEL de campos --
     * tento comentar todos os campos e todas as seções para maior compreensão,
     * assim como as listas de possíveis valores numéricos.
     */
    public function exportaXMLNotaFiscal()
    {
        // importa dados cadastrais extras
        $this->geraObjetosExportacao();

        // dados de transportadora
        $transportadora = new PessoaGUI();
        $transportadora->top = "TOP 10";
        $transportadora->pesquisa["pesq_codigo"] = $this->entrega->cod_transportadora;
        $transportadora->fetch();
        $transportadora = $transportadora->itens[0];

        // cria link simbólico com objetos para simplificar código
        $sistema = &$this->obj_sistema;
        $empresa = &$this->obj_empresa;
        $pessoa = &$this->obj_pessoa;

        // gera a chave da nota fiscal, parâmetros padrão, assinatura, etc.
        if (!$this->nota_fiscal->montaChave($empresa)) {
            $this->erros_exportacao[] = "Chave da nota fiscal possui tamanho inválido (<> 44). Confira os dados cadastrais da filial.";
        }

        // faz a pré-validação de todos os dados cadastrais possíveis (WIP)
        if (empty($empresa->razao_social)) $this->erros_exportacao[] = "Empresa não possui razão social cadastrada.";
        if (strlen($empresa->cnae) != 7) $this->erros_exportacao[] = "Empresa possui CNAE inválido (deve ter 7 dígitos)";
        if (strlen($empresa->cnpj) != 14) $this->erros_exportacao[] = "Empresa possui CNPJ inválido (deve ter 14 dígitos)";
        if (strlen($empresa->inscricao_estadual) < 2)
            $this->erros_exportacao[] = "Empresa possui IE inválida.";
        if (empty($empresa->inscricao_municipal))
            $this->erros_exportacao[] = "Empresa não possui inscrição municipal cadastrada.";
        if (empty($empresa->telefone)) $this->erros_exportacao[] = "Empresa não possui telefone cadastrado.";

        if (empty($empresa->endereco->cidade)) $this->erros_exportacao[] = "O endereço do emitente (filial) está incompleto.";
        if (empty($empresa->endereco->bairro)) $this->erros_exportacao[] = "O endereço do emitente (filial) está incompleto.";
        if (empty($empresa->endereco->cep)) $this->erros_exportacao[] = "O endereço do emitente (filial) está incompleto.";
        if (empty($empresa->endereco->logradouro)) $this->erros_exportacao[] = "O endereço do emitente (filial) está incompleto.";

        if (empty($pessoa->endereco->cep)) $this->erros_exportacao[] = "O endereço do destinatário (filial) está incompleto.";
        if (empty($pessoa->endereco->cidade)) $this->erros_exportacao[] = "O endereço do destinatário (filial) está incompleto.";
        if (empty($pessoa->endereco->bairro)) $this->erros_exportacao[] = "O endereço do destinatário (filial) está incompleto.";
        if (empty($pessoa->endereco->cep)) $this->erros_exportacao[] = "O endereço do destinatário (filial) está incompleto.";

        if (!empty($this->entrega->transportadora)) {
            if (empty($transportadora->endereco->cep)) $this->erros_exportacao[] = "O endereço da transportadora informada está incompleto.";
            if (empty($transportadora->endereco->cidade)) $this->erros_exportacao[] = "O endereço da transportadora informada está incompleto.";
            if (empty($transportadora->endereco->bairro)) $this->erros_exportacao[] = "O endereço da transportadora informada está incompleto.";
            if (empty($transportadora->endereco->cep)) $this->erros_exportacao[] = "O endereço da transportadora informada está incompleto.";
        }

        if (empty($this->produtos)) $this->erros_exportacao[] = "As linhas de produtos/serviços estão vazias.";
        if (empty($this->duplicatas)) $this->erros_exportacao[] = "As linhas de duplicatas estão vazias.";
        if (empty($this->nota_fiscal->natureza_operacao))
            $this->erros_exportacao[] = "Favor preencher a natureza da operação.";
        if (!empty($this->nota_fiscal->chave_referencia) && strlen($this->nota_fiscal->chave_referencia) != 44)
            $this->erros_exportacao[] = "A chave de referência informada possui tamanho inválido (deve ter 44 dígitos)";
        // if(empty($this->doc_financeiro))	$this->erros_exportacao[] = "Não foi encontrado o documento financeiro. Favor faturar antes de emitir a nota.";
        if (!empty($this->entrega->volume_peso_bruto) && empty($this->entrega->volume_peso_liquido))
            $this->erros_exportacao[] = "Informado apenas peso bruto dos volumes. Informe o peso líquido também.";
        if (!empty($this->entrega->volume_quantidade) && empty($this->entrega->volume_peso_liquido))
            $this->erros_exportacao[] = "Foi informada quantidade de volumes sem peso líquido.";

        // trata tipo de ambiente: homologação ou produção
        if (__HOMOLOGACAO__) {
            $pessoa->nome = "NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL";

            // NFC também precisa incluir exatamente esta descrição no primeiro produto
            if ($this->nota_fiscal->modelo == FaturamentoNotaFiscalETT::MODELO_NFC) {
                $this->produtos[0]->produto = "NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL";
            }
        }

        // flags de controle tributário
        $gera_icms = false;

        /* QUIRKS DA SUBSTITUIÇÃO TRIBUTÁRIA
         * isso é temporário. enquanto o sistema não considera ST,
         * precisamos usar os valores dos impostos normais.
         */
        $cst_origem = left($this->produtos[0]->cst_icms, 1);
        $cst_tributacao = right($this->produtos[0]->cst_icms, 2);
        $csosn = $this->produtos[0]->csosn;

        /*
        if($cst_tributacao == 30 || $empresa->regime_tributario == Empresa\Filial::SIMPLES_NACIONAL) {
            if($csosn == "201" || $csosn == "202") {
                $this->total_produtos->valor_bc_icms_st = $this->total_produtos->valor_bc_icms;
                $this->total_produtos->valor_bc_icms = 0;

                $this->total_produtos->valor_icms_st = $this->total_produtos->valor_icms;
                $this->total_produtos->valor_icms = 0;

                $this->total_produtos->valor_total_nota += $this->total_produtos->valor_icms_st;
            }
            elseif($csosn == "101" || $csosn == "102") {
                $this->total_produtos->valor_bc_icms = 0;
                $this->total_produtos->valor_icms = 0;
            }
        }
        */

        /* regra geral: se pessoa é física, operação é com consumidor final (não contribuinte)
         * também se incluem nisso agora os PJs não-contribuintes
         */
        if ($pessoa->tipo == "F" || $pessoa->getIndIEDest() == 9) {
            $this->nota_fiscal->consumidor_final = 1;
        }

        // lei distrital: endereço e número do procon
        /* -- vamos remover isso do XML e inserir no DANFE. ocupa muitos caracteres
        if($empresa->endereco->sigla_estado == "DF") {
            $this->historico .= " - PROCON 151 - Venâncio 2000, Setor Comercial Sul, Quadra 08, Bloco B-60, Sala 240";
        }
        */

        // retira caracteres problemáticos dos campos de texto/histórico
        $this->historico = str_replace("\n", "", $this->historico);
        $this->historico = str_replace("\r", "", $this->historico);

        // instancia novo objeto XML
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument("1.0");
        $xml->setIndent(1);

        // -----------------------------
        // nota foi aprovada? (abre nfeProc)
        if (!empty($this->nota_fiscal->protocolo)
            && $this->nota_fiscal->protocolo != "CANCELADO"
            && $this->nota_fiscal->protocolo != "PROCESSAM"
            && $this->nota_fiscal->protocolo != "REJEICAO") {
            $xml->startElement("nfeProc");
            $xml->writeAttribute("versao", $this->nota_fiscal->versao);
            $xml->writeAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
        }

        // -----------------------------
        // grupo principal NFe
        $xml->startElement("NFe");
        $xml->writeAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
        // -----------------------------
        // informações da nota fiscal
        $xml->startElement("infNFe");
        $xml->writeAttribute("versao", $this->nota_fiscal->versao);
        $xml->writeAttribute("Id", "NFe{$this->nota_fiscal->chave}");
        // -----------------------------
        // identificação da nota
        $xml->startElement("ide");
        $xml->writeElement("cUF", $empresa->endereco->cod_estado_ibge);    // código da UF do emitente (estado)
        $xml->writeElement("cNF", $this->nota_fiscal->codigo_acesso);// chave de acesso - número aleatório gerado pelo emitente
        $xml->writeElement("natOp", $this->nota_fiscal->natureza_operacao);// descrição da natureza de operação
        /*
        if(floatval($this->nota_fiscal->versao) < 4)				// não inclui forma de pagamento da NFE 4.00 em diante
        $xml->writeElement("indPag", $this->forma_pagamento);		// forma de pagamento: 0 - à vista | 1 - à prazo | 2 - outros
        */
        $xml->writeElement("mod", $this->nota_fiscal->modelo);        // modelo do documento fiscal
        $xml->writeElement("serie", $this->nota_fiscal->serie);        // série do documento fiscal
        $xml->writeElement("nNF", $this->nota_fiscal->numero);        // número do documento fiscal
        $xml->writeElement("dhEmi", formataUTC(converteDataSqlOrdenada($this->nota_fiscal->data_emissao), $this->nota_fiscal->hora_emissao)); // data/hora emissão
        if ($this->nota_fiscal->modelo == FaturamentoNotaFiscalETT::MODELO_NFE)    // não inclui data/hora de saída na NFC!
            $xml->writeElement("dhSaiEnt", formataUTC(converteDataSqlOrdenada($this->nota_fiscal->data_emissao), $this->nota_fiscal->hora_emissao)); // data/hora saída/entrada do produto
        $xml->writeElement("tpNF", $this->getCodigoTipo());            // tipo de nota: 0 - entrada | 1 - saída
        $xml->writeElement("idDest", $this->destino);                /* local de destino: 1 - operação interna
																				 * 2 - operação interestadual | 3 - exterior
																				 */
        $xml->writeElement("cMunFG", $empresa->endereco->cod_cidade); // código do município do fato gerador do ICMS
        $xml->writeElement("tpImp", $this->nota_fiscal->tipo_impressao); /* formato impressão do danfe: 1 - retrato | 2 - paisagem
																					  * 4 ou 5 - NFC-e
																					  */
        $xml->writeElement("tpEmis", $this->nota_fiscal->tipo_emissao);/* emissão nfe: 1 - normal | 2 - contingência FS
																				    * 3 - contingência SCAN | 4 - contingência DPEC | 5 - cont. FS-DA
																				    */
        $xml->writeElement("cDV", $this->nota_fiscal->chave_dv);    // dígito verificador da chave de acesso (módulo 11)
        $xml->writeElement("tpAmb", $this->tipo_ambiente);            // tipo de ambiente: 1 - produção | 2 - homologação
        $xml->writeElement("finNFe", $this->finalidade);            /* finalidade: 1 - normal | 2 - complementar | 3 - ajuste
																				 * 4 - devolução de mercadoria
																				 */
        $xml->writeElement("indFinal", $this->nota_fiscal->consumidor_final); // 0 - normal | 1 - consumidor final (NFC-e)
        $xml->writeElement("indPres", $this->nota_fiscal->operacao_presencial);    /* indicador de presença do comprador no estabelecimento:
																				 * 0 - não se aplica (nota complementar ou de ajuste)
																				 * 1 - operação presencial | 2 - pela internet (e-commerce?)
																				 * 3 - teleatendimento | 4 - NFC-e entrega a domicílio
																				 * 9 - operação não presencial, outros.
																				 */
        $xml->writeElement("procEmi", 0);                            // processo de emissão: 0 - APLICATIVO DO CONTRIBUINTE
        $xml->writeElement("verProc", "MC{$sistema}");                // versão do aplicativo emissor

        // há chave de referência para ajuste ou devolução?
        if (!empty($this->nota_fiscal->chave_referencia)) {
            // -----------------------------
            // grupo da nota referenciada
            $xml->startElement("NFref");
            $xml->writeElement("refNFe", $this->nota_fiscal->chave_referencia);
            $xml->endElement(); // NFref
        }
        $xml->endElement(); // ide

        // -----------------------------
        // dados do emitente
        $xml->startElement("emit");
        $xml->writeElement("CNPJ", apenasNumeros($empresa->cnpj));    // CNPJ
        $xml->writeElement("xNome", $empresa->razao_social);        // razão social
        $xml->writeElement("xFant", $empresa->nome);                // nome fantasia

        // -----------------------------
        // endereço do emitente
        $xml->startElement("enderEmit");
        $xml->writeElement("xLgr", $empresa->endereco->logradouro);        // logradouro
        $xml->writeElement("nro", $empresa->endereco->numero);            // número
        if (!empty($empresa->endereco->complemento))
            $xml->writeElement("xCpl", $empresa->endereco->complemento);    // complemento
        $xml->writeElement("xBairro", $empresa->endereco->bairro);        // bairro
        $xml->writeElement("cMun", $empresa->endereco->cod_cidade);        // código IBGE do município
        $xml->writeElement("xMun", $empresa->endereco->cidade);            // nome do município
        $xml->writeElement("UF", $empresa->endereco->sigla_estado);        // sigla da UF
        $xml->writeElement("CEP", insereZeros(apenasNumeros($empresa->endereco->cep), 8)); // código CEP - preencher zeros (8)
        $xml->writeElement("cPais", $empresa->endereco->cod_pais_bacen);// código do país
        $xml->writeElement("xPais", $empresa->endereco->pais);            // nome do país
        $xml->writeElement("fone", apenasNumeros($empresa->telefone));    // preencher com código DDD + número do telefone
        $xml->endElement(); // enderEmit

        $xml->writeElement("IE", $empresa->inscricao_estadual);        // inscrição estadual
        // $xml->writeElement("IEST", null);						// IE do substituto tributário quando ouver ICMS ST
        $xml->writeElement("IM", $empresa->inscricao_municipal);    // inscrição municipal
        $xml->writeElement("CNAE", $empresa->cnae);                    // CNAE fiscal - obrigatório quando IM for informado
        $xml->writeElement("CRT", $empresa->regime_tributario);        /* código do regime tributário: 1 - simples nacional
																				 * 2 - simples nacional - excesso | 3 - regime normal
																				 */
        $xml->endElement(); // emit

        // -----------------------------
        // dados do destinatário (classe de pessoa - cliente)
        $xml->startElement("dest");
        $xml->writeElement($pessoa->getDocumentoBase(), $pessoa->getCpfCnpj());  /* nome do campo é CPF ou CNPJ
																							  * preencher zeros (11 ou 14)
																							  */
        $xml->writeElement("xNome", trim(left($pessoa->nome, 60)));            // nome

        // -----------------------------
        // endereço do destinatário (e contatos?)
        $xml->startElement("enderDest");
        $xml->writeElement("xLgr", $pessoa->endereco->logradouro);        // logradouro
        $xml->writeElement("nro", $pessoa->endereco->numero);            // número
        if (!empty($pessoa->endereco->complemento))
            $xml->writeElement("xCpl", $pessoa->endereco->complemento);        // complemento
        $xml->writeElement("xBairro", $pessoa->endereco->bairro);        // bairro
        $xml->writeElement("cMun", $pessoa->endereco->cod_cidade);        // código do município
        $xml->writeElement("xMun", $pessoa->endereco->cidade);            // nome do município
        $xml->writeElement("UF", $pessoa->endereco->sigla_estado);        // sigla da UF
        if (!empty(intval(apenasNumeros($pessoa->endereco->cep))))
            $xml->writeElement("CEP", insereZeros(apenasNumeros($pessoa->endereco->cep), 8)); // código do CEP
        $xml->writeElement("cPais", $pessoa->endereco->cod_pais_bacen);    // código do país
        $xml->writeElement("xPais", $pessoa->endereco->pais);            // nome do país
        if (!empty($pessoa->telefone))
            $xml->writeElement("fone", apenasNumeros($pessoa->telefone));    // preencher com código DDD + número do telefone
        $xml->endElement(); // enderDest

        $xml->writeElement("indIEDest", $pessoa->getIndIEDest());    // indicador de isenção da IE
        if (!empty(apenasNumeros($pessoa->getIE())))
            $xml->writeElement("IE", apenasNumeros($pessoa->getIE()));    // inscrição estadual quando cliente for contribuinte do ICMS
        if (!empty($pessoa->email))
            $xml->writeElement("email", $pessoa->email);                // e-mail de contato
        $xml->endElement(); // dest


        // endereço diferente de entrega?
        if (!$this->entrega->endereco->vazio()) { // precisa testar se é diferente do endereço do cliente!
            // -----------------------------
            // dados de entrega
            $xml->startElement("entrega");
            $xml->writeElement($pessoa->getDocumentoBase(), $pessoa->getCpfCnpj());    /* nome do campo é CPF ou CNPJ
																									 * preencher zeros (11 ou 14)
																									 */
            $xml->writeElement("xLgr", $this->entrega->endereco->logradouro);            // logradouro
            $xml->writeElement("nro", $this->entrega->endereco->numero);                // número
            if (!empty($this->entrega->endereco->complemento))
                $xml->writeElement("xCpl", $this->entrega->endereco->complemento);            // complemento
            $xml->writeElement("xBairro", $this->entrega->endereco->bairro);            // bairro
            $xml->writeElement("cMun", $this->entrega->endereco->cod_cidade_ibge);        // código do município
            $xml->writeElement("xMun", $this->entrega->endereco->cidade);                // nome do município
            $xml->writeElement("UF", $this->entrega->endereco->sigla_estado);            // sigla da UF
            $xml->endElement(); // entrega
        }
        // =============================================
        // itera produtos e serviços
        $num_produto = 1;

        foreach ($this->produtos as $produto) {
            /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
             * validação dos valores dos produtos
             * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
             */
            if (empty($produto->ncm)) {
                $this->erros_exportacao[] = "Produto da linha {$num_produto} ({$produto->produto}) não possui NCM";
            } elseif (empty($produto->cest)) {
                $this->erros_exportacao[] = "Produto não possui CEST vinculado ao NCM {$produto->ncm}";
            }

            /*	Adaptação de M2 para PC, ajustando valores
             * 	Quantidade, valor total e unidario
             */
            $this->total_produtos->valor_bruto -= $produto->valor_bruto;        // precisa alterar essa linha para os totalizadores baterem
            $this->total_produtos->valor_total_nota -= $produto->valor_bruto;

            if ($produto->unidade == "M2" && __PRODUCAO__) {
                $produto->unidade = "PC";
                $produto->valor_unitario = round(($produto->medida_t / $produto->quantidade) * $produto->valor_unitario, 4);
                $produto->valor_bruto = formataValor($produto->valor_unitario * $produto->quantidade);
                $nome_produto = "{$produto->produto} {$produto->medida_z}X{$produto->medida_x}mm";

            } elseif (($produto->unidade == "MT" || $produto->unidade == "M") && __PRODUCAO__) {
                $nome_produto = "{$produto->produto} por {$produto->medida_z}mm";
                $produto->valor_unitario = round(($produto->medida_t / $produto->quantidade) * $produto->valor_unitario, 4);
                $produto->valor_bruto = formataValor($produto->valor_unitario * $produto->quantidade);
            } else {
                $nome_produto = $produto->produto;
            }

            $this->total_produtos->valor_bruto += $produto->valor_bruto;
            $this->total_produtos->valor_total_nota += $produto->valor_bruto;
            //$diferença -= $produto->valor_bruto;

            // -----------------------------
            // detalhe dos itens
            $xml->startElement("det");
            $xml->writeAttribute("nItem", $num_produto);
            // -----------------------------
            // grupo do produto/serviço
            $xml->startElement("prod");
            $xml->writeElement("cProd", $produto->cod_produto);        // codificação própria do contribuinte
            $xml->writeElement("cEAN", "SEM GTIN");                        // global trade item number - não informar?
            $xml->writeElement("xProd", $nome_produto);                    // descrição do produto/serviço
            $xml->writeElement("NCM", apenasNumeros($produto->ncm));    // código NCM 8 dígitos
            // $xml->writeElement("NVE", null);							// especificação necessária para alguns NCMs?
            if (!empty($produto->cest))
                $xml->writeElement("CEST", apenasNumeros($produto->cest));    // cód. espec. situação tributária (NT 2015-003)
            if (!empty($produto->codigo_ex))
                $xml->writeElement("EXTIPI", $produto->ncm_codigo_ex);        // código EX da TIPI (não incluir para serviços)
            $xml->writeElement("CFOP", apenasNumeros($produto->cfop));    // código CFOP 4 dígitos
            $xml->writeElement("uCom", $produto->unidade);                // unidade comercial abreviada (6 dígitos)
            $xml->writeElement("qCom", $produto->quantidade);            // quantidade comercializada
            $xml->writeElement("vUnCom", round($produto->valor_unitario, 4));// valor unitário comercializado
            $xml->writeElement("vProd", formataValor($produto->valor_bruto));// valor total bruto dos produtos/serviços
            $xml->writeElement("cEANTrib", "SEM GTIN");                    // código GTIN da unidade tributável
            $xml->writeElement("uTrib", $produto->unidade);                // unidade tributável (integral?)
            $xml->writeElement("qTrib", $produto->quantidade);            // quantidade tributável (integral?)
            $xml->writeElement("vUnTrib", round($produto->valor_unitario, 4));// valor unitário de tributação
            if (!empty($produto->valor_frete))
                $xml->writeElement("vFrete", formataValor($produto->valor_frete));// valor total do frete
            // $xml->writeElement("vSeg", 0);							// valor total do seguro
            if (!empty($produto->valor_desconto))
                $xml->writeElement("vDesc", formataValor($produto->valor_desconto));// valor do desconto
            // $xml->writeElement("vOutro", 0);							// outras despesas acessórias
            $xml->writeElement("indTot", 1);                            // 0 - valor não compõe total da NFE | 1 - valor compõe total
            $xml->endElement(); // prod

            // -----------------------------
            // tributos incidentes no produto ou serviço
            $xml->startElement("imposto");
            $xml->writeElement("vTotTrib", formataValor($produto->valor_total_tributos));// valor aproximado total de tributos

            // é tributado ISSQN? (tratar por código de serviço)
            if ($produto->codigo_servico > 0) {
                // -----------------------------
                // ISSQN: é mutuamente exclusivo com ICMS e II
                $xml->startElement("ISSQN");
                $xml->writeElement("vBC", formataValor($produto->valor_total)); // valor da base de cálculo
                $xml->writeElement("vAliq", $produto->perc_issqn); // alíquota do ISSQN
                $xml->writeElement("vISSQN", formataValor($produto->valor_issqn)); // valor do ISSQN
                $xml->writeElement("cMunFG", $empresa->endereco->cod_cidade); // código do município do fato gerador do ISSQN
                $xml->writeElement("cListServ", $produto->codigo_servico); // código da lista de serviços (NN.NN)
                $xml->writeElement("indISS", 3);                /* indicador de exigibilidade do ISS:
																					 * 1=exigível 2=não incidência 3=isenção 4=exportação
																					 * 5=imunidade 6=suspensa judicial 7=suspensa administrativo
																					 */
                $xml->writeElement("cMun", $empresa->endereco->cod_cidade); // código do município de incidência ISSQN
                $xml->writeElement("cPais", $empresa->endereco->cod_pais_bacen);// código do país
                $xml->writeElement("indIncentivo", 2);            // incentivo fiscal: 1 - sim | 2 = não
                $xml->endElement(); // ISSQN
            } // ICMS e IPI são exclusivos com ISSQN (?)
            else {
                // é tributado ICMS?
                $cst_origem = left($produto->cst_icms, 1);
                $cst_tributacao = right($produto->cst_icms, 2);

                if (in_array($cst_tributacao, array("00", "10", "20", "30", "40", "41", "50", "51", "60", "70", "90"))) {
                    // -----------------------------
                    // detalhe do ICMS
                    $gera_icms = true; // flag para zerar ou não os valores de produtos relativos ao ICMS
                    $xml->startElement("ICMS");
                    // simples nacional tem um tratamento diferente
                    if ($empresa->regime_tributario == FilialETT::SIMPLES_NACIONAL) {
                        // -----------------------------
                        // grupo tributação do ICMSSN = XXX
                        $xml->startElement("ICMSSN{$produto->csosn}");
                        $xml->writeElement("orig", $cst_origem);        // código da origem (1º dígito CST)
                        $xml->writeElement("CSOSN", $produto->csosn);    // código da operação - simples nacional

                        // especial 201, 202: cobrança por substituição tributária
                        if ($produto->csosn == "201" || $produto->csosn == "202") {
                            $xml->writeElement("modBCST", $produto->modalidade_bc_icms); // código de modalidade (tipo de operação)
                            $xml->writeElement("vBCST", formataValor($produto->valor_bc_icms_st)); // valor da base de cálculo
                            $xml->writeElement("pICMSST", $produto->perc_icms_st); // alíquota do ICMS
                            $xml->writeElement("vICMSST", formataValor($produto->valor_icms_st)); // valor do ICMS
                        }

                        // especial 101, 201: aproveitamento de crédito
                        if ($produto->csosn == "101" || $produto->csosn == "201") {
                            $xml->writeElement("pCredSN", $produto->perc_icms);
                            $xml->writeElement("vCredICMSSN", formataValor($produto->valor_icms));
                        }
                        $xml->endElement(); // ICMSSNXXX
                    } // regime tributário normal
                    else {
                        // trata o caso do 40, 41 e 50
                        $grupo_icms = $cst_tributacao;
                        if (in_array($grupo_icms, array("40", "41", "50"))) $grupo_icms = "40";

                        // -----------------------------
                        // grupo tributação do ICMS = XX
                        $xml->startElement("ICMS{$grupo_icms}");
                        $xml->writeElement("orig", $cst_origem);        // código da origem (1º dígito CST)
                        $xml->writeElement("CST", $cst_tributacao);        // código da tributação (2 últimos dígitos CST)

                        // tem ICMS normal?
                        if (!empty($produto->valor_icms) || in_array($grupo_icms, array("00", "10", "20", "70"))) {
                            $xml->writeElement("modBC", (int)$produto->modalidade_bc_icms); // código de modalidade (tipo de operação)
                            $xml->writeElement("vBC", formataValor($produto->valor_bc_icms)); // valor da base de cálculo
                            $xml->writeElement("pICMS", $produto->perc_icms); // alíquota do ICMS
                            $xml->writeElement("vICMS", formataValor($produto->valor_icms)); // valor do ICMS
                        }

                        // tem substituição tributária?
                        if (!empty($produto->valor_icms_st)) {
                            $xml->writeElement("modBCST", $produto->modalidade_bc_icms); // código de modalidade (tipo de operação)

                            // para MVA ST
                            if (!empty($produto->margem_valor_agregado)) {
                                $xml->writeElement("pMVAST", formataValor($produto->margem_valor_agregado));
                                $xml->writeElement("pRedBCST", formataValor($produto->perc_reducao_bc_icms));
                            }

                            $xml->writeElement("vBCST", formataValor($produto->valor_bc_icms_st)); // valor da base de cálculo
                            $xml->writeElement("pICMSST", $produto->perc_icms_st); // alíquota do ICMS
                            $xml->writeElement("vICMSST", formataValor($produto->valor_icms_st)); // valor do ICMS
                        }

                        /* deveria fazer tratamento pelo CST?
                         * 30 - substituição tributária (modo antigo)
                         * 40 - desonerado (declara vICMSDeson)
                         *
                         * TO-DO: outras situações tributárias!
                         * MANUAL 6.0 - PÁGINAS 204 E SEGUINTES
                         */
                        $xml->endElement(); // ICMSXX
                    }
                    $xml->endElement(); // ICMS
                }

                // é tributado IPI? (NFC não tem grupo IPI!)
                $cst_origem = left($produto->cst_ipi, 1);
                $cst_tributacao = right($produto->cst_ipi, 2);

                if ($this->nota_fiscal->modelo != FaturamentoNotaFiscalETT::MODELO_NFC
                    && in_array($cst_tributacao, array("00", "49", "50", "99", "01", "02", "03", "04", "51", "52", "53", "54", "55"))) {
                    // -----------------------------
                    // detalhe do IPI
                    $xml->startElement("IPI");
                    /* tabela de enquadramento do IPI: nova obrigação legal desde 2016.
                     * informar 999 para a maioria dos casos resolve
                     */
                    $xml->writeElement("cEnq", $produto->enquadramento_ipi);

                    // é tributado ou isento?
                    if (in_array($cst_tributacao, array("00", "49", "50", "99"))) {
                        // -----------------------------
                        // IPI tributado
                        $xml->startElement("IPITrib");
                        $xml->writeElement("CST", $cst_tributacao);    // código de situação tributária
                        $xml->writeElement("vBC", formataValor($produto->valor_bc_ipi)); // valor da base de cálculo
                        $xml->writeElement("pIPI", $produto->perc_ipi); // alíquota do IPI
                        $xml->writeElement("vIPI", formataValor($produto->valor_ipi)); // valor do IPI
                        $xml->endElement(); // IPITrib
                    } else {
                        // -----------------------------
                        // IPI não-tributado
                        $xml->startElement("IPINT");
                        $xml->writeElement("CST", $cst_tributacao);    // CST justificativo da isenção do IPI
                        $xml->endElement(); // IPIINT
                    }
                    $xml->endElement(); // IPI
                }
            }

            /* trata PIS/COFINS de acordo com o código específico do tipo de operação
             * 01 - base calc. alíquota normal | 02 - base calc. alíquota diferenciada
             */
            if (in_array($produto->cst_pis_cofins, array("01", "02"))) {
                // -----------------------------
                // detalhe do PIS
                $xml->startElement("PIS");
                // -----------------------------
                // por alíquota; código 01
                $xml->startElement("PISAliq");
                $xml->writeElement("CST", $produto->cst_pis_cofins);
                $xml->writeElement("vBC", formataValor($produto->valor_bc_pis)); // valor da base de cálculo
                $xml->writeElement("pPIS", $produto->perc_pis);    // alíquota do PIS
                $xml->writeElement("vPIS", formataValor($produto->valor_pis)); // valor do PIS
                $xml->endElement(); // PISALiq
                $xml->endElement(); // PIS

                // -----------------------------
                // detalhe do COFINS
                $xml->startElement("COFINS");
                // -----------------------------
                // por alíquota; código 01
                $xml->startElement("COFINSAliq");
                $xml->writeElement("CST", $produto->cst_pis_cofins);
                $xml->writeElement("vBC", formataValor($produto->valor_bc_cofins)); // valor da base de cálculo
                $xml->writeElement("pCOFINS", $produto->perc_cofins);    // alíquota do COFINS
                $xml->writeElement("vCOFINS", formataValor($produto->valor_cofins)); // valor do COFINS
                $xml->endElement(); // COFINSALiq
                $xml->endElement(); // COFINS
            }

            /* 04 - tributável monofásica | 05 - tributável ST
             * 06 - tributável alíquota zero | 07 - isenta
             * 08 - sem incidência | 09 - suspensão
             */
            if (in_array($produto->cst_pis_cofins, array("04", "05", "06", "07", "08", "09"))) {
                // -----------------------------
                // detalhe do PIS
                $xml->startElement("PIS");
                // -----------------------------
                // PIS não tributado
                $xml->startElement("PISNT");
                $xml->writeElement("CST", $produto->cst_pis_cofins);
                $xml->endElement(); // PISNT
                $xml->endElement(); // PIS

                // -----------------------------
                // detalhe do COFINS
                $xml->startElement("COFINS");
                // -----------------------------
                // COFINS não tributado
                $xml->startElement("COFINSNT");
                $xml->writeElement("CST", $produto->cst_pis_cofins);
                $xml->endElement(); // COFINSNT
                $xml->endElement(); // COFINS
            }

            /* TO-DO:
             * 03 - por quantidade
             * >49 - outras operações!
             */
            $xml->endElement(); // imposto
            $xml->endElement(); // det

            $num_produto++; // incrementa contador
        } // [próximo produto]
        // =============================================

        // -----------------------------
        // totalizadores
        $xml->startElement("total");
        // -----------------------------
        // totais relativos ao ICMS
        $xml->startElement("ICMSTot");
        // aqui os totais são relativos aos tributos, então se não há grupo do ICMS os totalizadores devem ser zero!
        if (!$gera_icms) {
            $xml->writeElement("vBC", formataValor($this->total_produtos->valor_bc_icms));
            $xml->writeElement("vICMS", formataValor(0));
            $xml->writeElement("vICMSDeson", formataValor(0));
            $xml->writeElement("vFCP", formataValor(0));
            $xml->writeElement("vBCST", formataValor(0));
            $xml->writeElement("vST", formataValor(0));
            $xml->writeElement("vFCPST", formataValor(0));
            $xml->writeElement("vFCPSTRet", formataValor(0));
            $xml->writeElement("vProd", formataValor(0));
            $xml->writeElement("vFrete", formataValor(0));
            $xml->writeElement("vSeg", formataValor(0));
            $xml->writeElement("vDesc", formataValor(0));
            $xml->writeElement("vII", formataValor(0));
            $xml->writeElement("vIPI", formataValor(0));
            $xml->writeElement("vIPIDevol", formataValor(0));
            $xml->writeElement("vPIS", formataValor(0));
            $xml->writeElement("vCOFINS", formataValor(0));
            $xml->writeElement("vOutro", formataValor(0));
            $xml->writeElement("vNF", formataValor($this->total_produtos->valor_total_nota)); // VALOR TOTAL DA NF-e
            $xml->writeElement("vTotTrib", formataValor($this->total_produtos->valor_total_tributos)); // total tributos
        } // valores normais (integrais?)
        else {
            $xml->writeElement("vBC", formataValor($this->total_produtos->valor_bc_icms)); // base de cálculo do ICMS
            $xml->writeElement("vICMS", formataValor($this->total_produtos->valor_icms)); // valor total do ICMS
            $xml->writeElement("vICMSDeson", formataValor(0));                // valor ICMS desonerado??
            $xml->writeElement("vFCP", formataValor(0));
            $xml->writeElement("vBCST", formataValor($this->total_produtos->valor_bc_icms_st)); // base cálculo ICMS substituição trib.
            $xml->writeElement("vST", formataValor($this->total_produtos->valor_icms_st)); // total ICMS substituição trib.
            $xml->writeElement("vFCPST", formataValor(0));
            $xml->writeElement("vFCPSTRet", formataValor(0));
            $xml->writeElement("vProd", formataValor($this->total_produtos->valor_bruto)); // total produtos e serviços
            $xml->writeElement("vFrete", formataValor($this->total_produtos->valor_frete)); // total frete
            $xml->writeElement("vSeg", formataValor(0));                    // total seguro (não se aplica?)
            $xml->writeElement("vDesc", formataValor($this->total_produtos->valor_desconto)); // total descontos
            $xml->writeElement("vII", formataValor(0));                        // total imposto sobre importações
            $xml->writeElement("vIPI", formataValor($this->total_produtos->valor_ipi)); // valor total IPI
            $xml->writeElement("vIPIDevol", formataValor(0));
            $xml->writeElement("vPIS", formataValor($this->total_produtos->valor_pis)); // valor total PIS
            $xml->writeElement("vCOFINS", formataValor($this->total_produtos->valor_cofins)); // valor total COFINS
            $xml->writeElement("vOutro", formataValor(0));                    // outras despesas acessórias
            $xml->writeElement("vNF", formataValor($this->total_produtos->valor_total_nota)); // VALOR TOTAL DA NF-e
            $xml->writeElement("vTotTrib", formataValor($this->total_produtos->valor_total_tributos)); // total tributos
        }
        $xml->endElement(); // ICMSTot

        // exibe totalizador do ISSQN?
        if (!$gera_icms && $this->produtos[0]->codigo_servico > 0) {
            $xml->startElement("ISSQNtot");
            $xml->writeElement("vServ", formataValor($this->total_produtos->valor_total_nota));
            $xml->writeElement("vBC", formataValor($this->total_produtos->valor_total_nota));
            // $xml->writeElement("vPIS", formataValor($this->total_produtos->valor_pis));
            // $xml->writeElement("vCOFINS", formataValor($this->total_produtos->valor_cofins));
            $xml->writeElement("dCompet", converteDataSqlOrdenada($this->nota_fiscal->data_emissao));
            $xml->endElement(); // ISSQNtot
        }
        $xml->endElement(); // total

        // -----------------------------
        // transporte
        $xml->startElement("transp");
        $xml->writeElement("modFrete", (int)$this->entrega->cod_tipo_frete);        // modalidade do frete (documentado na classe Entrega)

        // possui transportadora?
        if (!empty($this->entrega->transportadora)) {
            // -----------------------------
            // dados da transportadora
            $xml->startElement("transporta");
            $xml->writeElement($transportadora->getDocumentoBase(), $transportadora->getCpfCnpj()); // CPF ou CNPJ
            $xml->writeElement("xNome", $transportadora->nome);            // razão social
            if (!empty($transportadora->getIE()))
                $xml->writeElement("IE", $transportadora->getIE());            /* inscrição estadual quando cliente for contribuinte do ICMS
																						 * ou informar "ISENTO"
																						 */
            // aqui precisa ser o endereço COMPLETO
            $xml->writeElement("xEnder", trim($transportadora->endereco->logradouro . " " . $transportadora->endereco->numero));
            $xml->writeElement("xMun", $transportadora->endereco->cidade); // nome do município
            $xml->writeElement("UF", $transportadora->endereco->sigla_estado); // sigla da UF
            $xml->endElement(); // transporta
        }

        /* informação do veículo (nfe 4.0 apenas estadual
         */
        if (!empty($this->entrega->placa) && $this->destino == 1) {
            // -----------------------------
            // dados do veículo
            $xml->startElement("veicTransp");
            $xml->writeElement("placa", $this->entrega->placa);            // placa do veículo
            $xml->writeElement("UF", $this->entrega->uf_placa);            // sigla da UF
            if (!empty($this->entrega->rntc))
                $xml->writeElement("RNTC", $this->entrega->rntc);            // registro nacional de transportador de carga (ANTT)
            $xml->endElement(); // veicTransp
        }

        // possui volumes?
        if (!empty($this->entrega->volume_peso_liquido)) {
            // -----------------------------
            // grupo de volumes
            $xml->startElement("vol");
            if (!empty($this->entrega->volume_quantidade))
                $xml->writeElement("qVol", $this->entrega->volume_quantidade);    // quantidade de volumes transportados
            if (!empty($this->entrega->volume_especie))
                $xml->writeElement("esp", $this->entrega->volume_especie);        // espécie			||
            if (!empty($this->entrega->volume_marca))
                $xml->writeElement("marca", $this->entrega->volume_marca);        // marca			||
            if (!empty($this->entrega->volume_numeracao))
                $xml->writeElement("nVol", $this->entrega->volume_numeracao);    // numeração dos volumes
            $xml->writeElement("pesoL", $this->entrega->volume_peso_liquido);// peso líquido (em kg)
            if (!empty($this->entrega->volume_peso_bruto))
                $xml->writeElement("pesoB", $this->entrega->volume_peso_bruto);    // peso bruto (em kg)
            $xml->endElement(); // vol
        }
        $xml->endElement(); // transp

        // informa elemento padrão de cobrança se for duplicata mercantil
        if ($this->nota_fiscal->modelo == FaturamentoNotaFiscalETT::MODELO_NFE
            && $this->duplicatas[0]->tipo_pagamento_nfe == FaturamentoDuplicataETT::FORMA_DUPLICATA) {
            // -----------------------------
            // cobrança
            $xml->startElement("cobr");
            // grupo da fatura
            $xml->startElement("fat");
            $xml->writeElement("nFat", "001"); // é obrigatório que sejam 3 dígitos
            $xml->writeElement("vOrig", formataValor($this->total_produtos->valor_total_nota));
            $xml->writeElement("vDesc", formataValor(0));
            $xml->writeElement("vLiq", formataValor($this->total_produtos->valor_total_nota));
            $xml->endElement(); // fat

            // itera duplicatas
            foreach ($this->duplicatas as $duplicata) {
                // -----------------------------
                // grupo da duplicata
                $xml->startElement("dup");
                $xml->writeElement("nDup", insereZeros($duplicata->numero, 3));    // número da duplicata
                $xml->writeElement("dVenc", converteDataSqlOrdenada($duplicata->data_vencimento_real)); // vencimento - formato AAAA-MM-DD
                $xml->writeElement("vDup", formataValor($duplicata->valor)); // valor da duplicata
                $xml->endElement(); // dup
            }
            $xml->endElement(); // cobr
            /* TO-DO:
             * grupo fatura (entra em vigor 9/2018)
             */
        }

        // -----------------------------
        // informações de pagamento
        $xml->startElement("pag");
        // para o cálculo do valor do troco
        $total = 0;

        // itera duplicatas
        foreach ($this->duplicatas as $duplicata) {
            // -----------------------------
            // detalhe da forma de pagamento
            $xml->startElement("detPag");
            $xml->writeElement("tPag", $duplicata->tipo_pagamento_nfe); /* código da forma de pagamento
																						 * (se desconhecido, informar 99=outros)
																						 * para devolução e ajuste, 90=sem pagamento
																						 */
            if ($duplicata->tipo_pagamento_nfe == 90) $duplicata->valor = 0;
            $xml->writeElement("vPag", formataValor($duplicata->valor));
            /* TO-DO:
             * grupo cartões (para PDV)
             * valor do troco ||
             */
            $xml->endElement(); // detPag

            $total += formataValor($duplicata->valor);
        }

        // valor do troco (workaround para problemas de arredondamento)
        $troco = $total - formataValor($this->total_produtos->valor_total_nota);
        if ($troco >= 0) {
            $xml->writeElement("vTroco", formataValor($troco));
        }
        $xml->endElement(); // pag

        // possui informações adicionais?
        if (!empty($this->nota_fiscal->informacoes_fisco) || !empty($this->historico)) {
            // -----------------------------
            // informações adicionais
            $xml->startElement("infAdic");
            if (!empty($this->nota_fiscal->informacoes_fisco))
                $xml->writeElement("infAdFisco", strip_tags(trim($this->nota_fiscal->informacoes_fisco)));
            if (!empty($this->historico))
                $xml->writeElement("infCpl", strip_tags(trim($this->historico)));
            $xml->endElement(); // infAdic
        }

        // para operação com exterior
        if ($this->destino == 3) {
            // -----------------------------
            // exportação
            $xml->startElement("exporta");
            $xml->writeElement("UFSaidaPais", $empresa->endereco->sigla_estado);
            $xml->writeElement("xLocExporta", $empresa->endereco->cidade);
            $xml->endElement(); // exporta
        }
        $xml->endElement(); // infNfe

        // -----------------------------
        // informação do QR Code (NFC)
        if ($this->nota_fiscal->modelo == FaturamentoNotaFiscalETT::MODELO_NFC) {
            $this->montaQRCode();

            // -----------------------------
            // informações suplementares
            $xml->startElement("infNFeSupl");
            $xml->startElement("qrCode");
            $xml->writeCData($this->nota_fiscal->qr_code);
            $xml->endElement(); // qrCode
            $xml->endElement(); // infNFeSupl
        }

        // -----------------------------
        // assinatura digital (esqueleto)
        $xml = $this->signature($xml, "#NFe{$this->nota_fiscal->chave}");
        $xml->endElement(); // NFe

        // -----------------------------
        // nota foi aprovada? (fecha nfeProc)
        if (!empty($this->nota_fiscal->protocolo)
            && $this->nota_fiscal->protocolo != "CANCELADO"
            && $this->nota_fiscal->protocolo != "PROCESSAM"
            && $this->nota_fiscal->protocolo != "REJEICAO") {
            $xml->startElement("protNFe");
            /* vai ser preenchido com o campo xml_retorno. se não houver, informe aqui para ter os dados no danfe
            $xml->writeElement("nProt", $this->nota_fiscal->protocolo);
            $xml->writeElement("chNFe", $this->nota_fiscal->chave);
            $xml->writeElement("dhRecbto", formataUTC(converteDataSqlOrdenada($this->nota_fiscal->data_emissao), $this->nota_fiscal->hora_emissao));
            */
            $xml->endElement(); // protNfe
            $xml->endElement(); // nfeProc
        }

        // guarda a string de resultado
        $str = $xml->outputMemory();

        // altera o cabeçalho se necessário
        $str = str_replace("<?xml version=\"1.0\"?>", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", $str);

        // preenche o protNfe com os dados no campo de retorno
        if (strlen($this->nota_fiscal->xml_retorno) > 50) {
            // converte aspas simples para evitar erro de malformação do XML com webservices que usam
            $this->nota_fiscal->xml_retorno = str_replace("'", "\"", $this->nota_fiscal->xml_retorno);

            // por que você faz isso, goiás?
            $this->nota_fiscal->xml_retorno = str_replace("versao=3.10", "versao=\"3.10\"", $this->nota_fiscal->xml_retorno);
            $this->nota_fiscal->xml_retorno = str_replace("versao=4.00", "versao=\"4.00\"", $this->nota_fiscal->xml_retorno);

            $partes = explode("<protNFe", $this->nota_fiscal->xml_retorno);
            $partes = explode("</protNFe>", $partes[1]);

            if (!empty($partes[0])) {
                $str = str_replace("<protNFe/>", "<protNFe" . $partes[0] . "</protNFe>", $str);
            }
        }

        // encerra o xmlwriter corretamente para evitar vazamento de memória
        $xml->endDocument();
        $xml->flush();

        return $str;
    }

    /* monta o XML de eventos da nota fiscal (cancelamento, carta de correção, etc.)
     * para uma nota previamente aprovada.
     *
     * @tipo 			= código do tipo de evento como esperado pela fazenda
     * @num_sequencia 	= sequência controlável do evento. cancelamento é 1, correção pode até 20?
     *
     * TO-DO: tudo que pode ser parametrizável pelo webservice precisa vir de algum lugar
     */
    public function exportaXMLEvento($tipo = self::EV_CANCELAMENTO, $num_sequencia = 1)
    {
        // importa dados cadastrais extras
        if (empty($this->obj_empresa)) {
            $empresa = new FilialGUI();
            $empresa->filial = $this->cod_filial;
            $empresa->fetch();
            $empresa = $empresa->itens[0];
            $this->obj_empresa = $empresa;
        } else {
            $empresa = $this->obj_empresa;
        }

        /* monta a ID do evento
         * aqui não precisamos montar a chave de novo. ela e o protocolo devem estar salvos
         */
        $id = "ID" . $tipo . $this->nota_fiscal->chave . insereZeros($num_sequencia, 2);

        // parâmetros do webservice
        $codigo_uf = $empresa->endereco->cod_estado_ibge;

        // instancia novo objeto XML
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument("1.0");
        $xml->setIndent(1);

        // -----------------------------
        // grupo principal evento
        $xml->startElement("evento");
        $xml->writeAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
        $xml->writeAttribute("versao", "1.00");
        // -----------------------------
        // informações do evento
        $xml->startElement("infEvento");
        $xml->writeAttribute("Id", $id);
        $xml->writeElement("cOrgao", $codigo_uf);                    // código do órgão de recepção do evento (UF)
        $xml->writeElement("tpAmb", $this->tipo_ambiente);            // 1 - produção | 2 - homologação
        $xml->writeElement("CNPJ", apenasNumeros($empresa->cnpj));    // CNPJ do autor do evento (emitente)
        $xml->writeElement("chNFe", $this->nota_fiscal->chave);        // chave da nota fiscal vinculada ao evento
        $xml->writeElement("dhEvento", formataUTC());                // data/hora do evento: sem parâmetros, puxa a data/hora atual
        $xml->writeElement("tpEvento", $tipo);                        // código do tipo de evento definido no cabeçalho da classe
        $xml->writeElement("nSeqEvento", $num_sequencia);            // número da sequência: só precisa ser >1 se for carta de correção
        $xml->writeElement("verEvento", "1.00");                    // versão do evento: pode variar por webservice?

        // -----------------------------
        // detalhe do evento
        $xml->startElement("detEvento");
        $xml->writeAttribute("versao", "1.00");
        // trata campos específicos por tipo de evento
        if ($tipo == self::EV_CANCELAMENTO) {
            $xml->writeElement("descEvento", "Cancelamento");
            $xml->writeElement("nProt", $this->nota_fiscal->protocolo);        // protocolo de aprovação da nota fiscal vinculada
            $xml->writeElement("xJust", strip_tags($this->justificativa));
        } elseif ($tipo == self::EV_CORRECAO) {
            $xml->writeElement("descEvento", "Carta de Correcao");
            $xml->writeElement("xCorrecao", strip_tags($this->justificativa));
            $xml->writeElement("xCondUso", "A Carta de Correcao e disciplinada pelo paragrafo 1o-A do art. 7o do Convenio S/N, de 15 de dezembro de 1970 e pode ser utilizada para regularizacao de erro ocorrido na emissao de documento fiscal, desde que o erro nao esteja relacionado com: I - as variaveis que determinam o valor do imposto tais como: base de calculo, aliquota, diferenca de preco, quantidade, valor da operacao ou da prestacao; II - a correcao de dados cadastrais que implique mudanca do remetente ou do destinatario; III - a data de emissao ou de saida.");
        } elseif ($tipo == self::EV_CIENCIA_OPERACAO) {
            $xml->writeElement("descEvento", "Ciencia da Operacao");
        }
        $xml->endElement(); // detEvento
        $xml->endElement(); // infEvento

        // -----------------------------
        // assinatura digital (esqueleto)
        $xml = $this->signature($xml, "#{$id}");
        $xml->endElement(); // evento

        // guarda a string de resultado
        $str = $xml->outputMemory();

        // altera o cabeçalho se necessário
        $str = str_replace("<?xml version=\"1.0\"?>", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", $str);

        // encerra o xmlwriter corretamente para evitar vazamento de memória
        $xml->endDocument();
        $xml->flush();

        return $str;
    }

    /* monta o XML para solicitação de inutilização de nota
     *
     * não sei qual é a melhor forma de fazer isso, se vinculado a uma nota (assim)
     * ou totalmente separado (aí seria em NotaFiscal)
     *
     * agora você pode informar o número inicial e final
     * para inutilizar uma faixa de numeração fora do sistema (cuidado!)
     */
    public function exportaXMLInutilizacao($num_inicial = null, $num_final = null)
    {
        /* ::: NUMERAÇÃO :::
         * se $num_inicial e $num_final não forem informados,
         * assumir o intervalo de numeração para uma única nota (esta mesma)
         *
         * a série sempre será única (1)?
         */
        if (empty($num_inicial)) $num_inicial = $this->nota_fiscal->numero;
        if (empty($num_final)) $num_final = $this->nota_fiscal->numero;
        $serie = $this->nota_fiscal->serie;

        // importa dados cadastrais extras
        if (empty($this->obj_empresa)) {
            $empresa = new FilialGUI();
            $empresa->filial = $this->cod_filial;
            $empresa->fetch();
            $empresa = $empresa->itens[0];
            $this->obj_empresa = $empresa;
        } else {
            $empresa = $this->obj_empresa;
        }

        /* monta a ID da inutilização
         * ID + UF + ano[2] + CNPJ + modelo + série[3] + num_inicial[9] + num_final[9]
         */
        $codigo_uf = $empresa->endereco->cod_estado_ibge;
        $ano = substr(converteDataSqlOrdenada($this->nota_fiscal->data_emissao), 2, 2);

        $id = "ID" . $codigo_uf . $ano . apenasNumeros($empresa->cnpj) . $this->nota_fiscal->modelo
            . insereZeros($serie, 3) . insereZeros($num_inicial, 9) . insereZeros($num_final, 9);

        // instancia novo objeto XML
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument("1.0");
        $xml->setIndent(1);

        // -----------------------------
        // grupo principal inutilização
        $xml->startElement("inutNFe");
        $xml->writeAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
        $xml->writeAttribute("versao", "3.10");
        // -----------------------------
        // informações do pedido
        $xml->startElement("infInut");
        $xml->writeAttribute("Id", $id);
        // $xml->writeElement("cOrgao", $codigo_uf);				// código do órgão de recepção do evento (UF)
        $xml->writeElement("tpAmb", $this->tipo_ambiente);            // 1 - produção | 2 - homologação
        $xml->writeElement("xServ", "INUTILIZAR");                    // serviço requisitado
        $xml->writeElement("cUF", $codigo_uf);                        // código da UF do solicitante
        $xml->writeElement("ano", $ano);                            // ano de inutilização da numeração
        $xml->writeElement("CNPJ", apenasNumeros($empresa->cnpj));    // CNPJ do emitente
        $xml->writeElement("mod", $this->nota_fiscal->modelo);        // modelo do documento fiscal
        $xml->writeElement("serie", $serie);                        // série do documento fiscal
        $xml->writeElement("nNFIni", $num_inicial);                    // numeração inicial
        $xml->writeElement("nNFFin", $num_final);                    // numeração final
        $xml->writeElement("xJust", strip_tags($this->justificativa));// justificativa da inutilização
        $xml->endElement(); // infInut

        // -----------------------------
        // assinatura digital (esqueleto)
        $xml = $this->signature($xml, "#{$id}");
        $xml->endElement(); // inutNFe

        // guarda a string de resultado
        $str = $xml->outputMemory();

        // altera o cabeçalho se necessário
        $str = str_replace("<?xml version=\"1.0\"?>", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", $str);

        // encerra o xmlwriter corretamente para evitar vazamento de memória
        $xml->endDocument();
        $xml->flush();

        return $str;
    }

    /* monta o XML de consulta de lote com o número de recibo salvo
     * para um lote de nota fiscal enviado anteriormente
     *
     * isso é um pouco mais comprido do que montar a string do XML
     * direto na classe NotaFiscal, mas quero manter o padrão
     * (como o tratamento do tipo de ambiente por aqui)
     */
    public function exportaXMLConsulta()
    {
        // instancia novo objeto XML
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument("1.0");
        $xml->setIndent(1);

        //-----------------------------
        // passa o número do recibo para consulta
        $xml->writeElement("tpAmb", $this->tipo_ambiente);
        $xml->writeElement("nRec", $this->nota_fiscal->recibo);

        // guarda a string de resultado
        $str = $xml->outputMemory();

        // altera o cabeçalho se necessário
        $str = str_replace("<?xml version=\"1.0\"?>", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", $str);

        // encerra o xmlwriter corretamente para evitar vazamento de memória
        $xml->endDocument();
        $xml->flush();

        return $str;
    }

    /* monta o XML de download da nota fiscal do fornecedor
     * --
     * depende do $this->obj_empresa setado;
     * essa chamada geralmente é feita na sequência do envio do evento de "ciência da operação"
     * (ver exportaXMLEvento)
     */
    public function exportaXMLDownload()
    {
        // instancia novo objeto XML
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument("1.0");
        $xml->setIndent(1);

        // -----------------------------
        // grupo principal download
        $xml->startElement("downloadNFe");
        $xml->writeAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
        $xml->writeAttribute("versao", "1.00");
        // -----------------------------
        // detalhes da requisição
        $xml->writeElement("tpAmb", $this->tipo_ambiente);
        $xml->writeElement("xServ", "DOWNLOAD NFE");
        $xml->writeElement("CNPJ", apenasNumeros($this->obj_empresa->cnpj));
        $xml->writeElement("chNFe", $this->nota_fiscal->chave);
        $xml->endElement(); // downloadNFe

        // guarda a string de resultado
        $str = $xml->outputMemory();

        // altera o cabeçalho se necessário
        $str = str_replace("<?xml version=\"1.0\"?>", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", $str);

        // encerra o xmlwriter corretamente para evitar vazamento de memória
        $xml->endDocument();
        $xml->flush();

        return $str;
    }

    /* junta os XMLs de envio e retorno de um evento
     * em um arquivo combinado, pronto para exportação.
     *
     * o sistema salva as duas partes separadamente
     * para preservar os dados originais, mas este é o formato final
     * que deve ser disponibilizado (download/e-mail)
     *
     * as partes originais ficam em uploads/xml.
     * salvo o novo arquivo em uploads/nfe, só para aproveitar
     * a estrutura de diretórios já existente
     *
     * TO-DO: tratar se retorno foi aprovado?
     */
    public function getArquivoXMLEvento($xml_envio, $xml_retorno)
    {
        // extrai as tags das duas partes
        $envio = $this->extraiTag("evento", $xml_envio);
        $retorno = $this->extraiTag("retEvento", $xml_retorno);

        // combina o conteúdo
        $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><procEventoNFe versao=\"1.0\">{$envio}{$retorno}</procEventoNFe>";

        /* puxa dados da nota: tipo e chave
         * estão presentes tanto no envio quanto no retorno
         */
        $tipo = $this->extraiTag("tpEvento", $envio);
        $tipo = substr($tipo, 10, 6);

        $chave = $this->extraiTag("chNFe", $envio); // a chave pode estar em self::nota_fiscal->chave, mas é bom não ser obrigatório?
        $chave = substr($chave, 7, 44);

        // escreve arquivo novo com o nome proposto pelo manual da NF-e
        $arq = _base_path."nfe/{$chave}_{$tipo}_procEventoNFe.xml";

        if (file_put_contents($arq, $str)) {
            // retorna o nome do arquivo criado para referência
            return $arq;
        } else {
            // para tratamento de erro
            return false;
        }
    }

    /* quando for exportado, o XML final precisa:
     * 1: ser minificado (sem trailing spaces, line feeds)
     * 2: ser convertido para UTF-8
     * 3: ter campos vazios removidos
     */
    public function trataXML($conteudo)
    {
        $novo = "";

        // remove trailing spaces e quebras de linha
        $linhas = explode("\n", $conteudo);

        foreach ($linhas as $linha) {
            $linha = trim($linha);
            $novo .= $linha;
        }

        // remove quebras de linha windows, se houver?
        $novo = str_replace("\r", "", $novo);

        // escapa caracteres especiais (podem dar pau no danfe)
        if ($this->nota_fiscal->modelo != FaturamentoNotaFiscalETT::MODELO_NFC) { // não pode alterar o QR Code da NFC; achar outra forma?
            $novo = str_replace("&", "&amp;", $novo);
        }

        // acerta codificação
        $novo = utf8_encode($novo);

        /* remove campos vazios <node/> -- pode ser lento?
         * AVISO: isso pode facilitar a geração de XML válido, mas reduzirá os detalhes da validação.
         * é melhor declarar campos opcionais com um if(!empty($valor)) $xml->writeElement(...)
         */
        // $novo = preg_replace('~<[^\\s>]+\\s*/>~si', null, $novo);

        return $novo;
    }

    /* retorna todo o array de erros de exportação, ou false se não houve
     */
    public function getErrosExportacao()
    {
        return !empty($this->erros_exportacao) ? $this->erros_exportacao : false;
    }

    /* rotina de montagem do texto do cupom fiscal para impressão
     * IMPORTANTE: não faz a impressão! deve ser enviado para o servidor de impressão local
     * IMPORTANTE[2]: precisa ser chamado depois do exportaXML
     */
    public function exportaTXTCupom()
    {
        // define o tamanho de uma linha
        $linha = "";
        for ($i = 0; $i < self::COLUNAS; $i++) $linha .= "-";
        $linha .= "\n";

        /* confere se os dados cadastrais de empresa e pessoa já foram puxados pela exportação de XML.
         * se foram, use o objeto salvo para agilizar.
         * senão, puxe aqui
         */
        if (empty($this->obj_pessoa)) {
            $pessoa = new PessoaGUI();
            $pessoa->top = "TOP 10";
            $pessoa->pesquisa["pesq_codigo"] = $this->cod_pessoa;
            $pessoa->fetch();
            $pessoa = $pessoa->itens[0];
            $this->obj_pessoa = $pessoa;
        } else {
            $pessoa = $this->obj_pessoa;
        }

        if (empty($this->obj_empresa)) {
            $empresa = new FilialGUI();
            $empresa->filial = $this->cod_filial;
            $empresa->fetch();
            $empresa = $empresa->itens[0];
            $this->obj_empresa = $empresa;
        } else {
            $empresa = $this->obj_empresa;
        }

        // começa a montar o texto da mensagem
        $txt = left($empresa->razao_social, self::COLUNAS) . "\n";
        $txt .= "CNPJ: {$empresa->cnpj} IE:{$empresa->inscricao_estadual}\n";
        $txt .= "{$empresa->endereco->logradouro} {$empresa->endereco->numero}\n";
        $txt .= "{$empresa->endereco->bairro} {$empresa->endereco->cidade}/{$empresa->endereco->sigla_estado}\n";
        $txt .= "CEP: {$empresa->endereco->cep}\n";

        $txt .= $linha;

        // se não for DANFE aqui, precisa exibir um outro cabeçalho!
        if ($this->modelo_cupom == self::MODELO_DANFE_NFC) {
            $txt .= $this->centro("DANFE NFC-e - Documento Auxiliar");
            $txt .= $this->centro("da Nota Fiscal de Consumidor Eletrônica");
            $txt .= $this->centro("Não permite aproveitamento de crédito de ICMS");
        } elseif ($this->modelo_cupom == self::MODELO_NAO_FISCAL) {
            $txt .= $this->centro("PEDIDO #{$this->handle}");
        }

        $txt .= $linha;

        /* linhas dos produtos
         * 4   3  6     (X/Y)
         * QTD UN COD   PRODUTO
         *
         * abaixo:
         * 9        9
         *   V. UNIT V. TOTAL
         *
         * 4+3+6 = 13
         * 9+9   = 18
         *       + 31
         * X = COLUNAS - 31 (cabeçalho)
         * Y = COLUNAS - 13 (tamanho do produto na primeira linha)
         *
         * --
         * poderia usar insereZeros e insereBrancos, mas essas rotinas só chamam str_pad.
         * melhor aprender a chamar direto
         * PROTIP: STR_PAD_LEFT ou STR_PAD_RIGHT se referem ao alinhamento do padding, não do conteúdo!
         */
        $cabecalho_produto = str_pad("PRODUTO", self::COLUNAS - 31, " ", STR_PAD_RIGHT);
        $txt .= "QTD UN COD   {$cabecalho_produto}  V. UNIT V. TOTAL\n";

        $txt .= $linha;

        if (!empty($this->produtos)) {
            foreach ($this->produtos as $produto) {
                $linha1 = str_pad($produto->quantidade, 4, " ", STR_PAD_RIGHT);
                $linha1 .= str_pad($produto->unidade, 3, " ", STR_PAD_RIGHT);
                $linha1 .= str_pad($produto->cod_produto, 6, "0", STR_PAD_LEFT);
                $linha1 .= str_pad($produto->produto, self::COLUNAS - 13, " ", STR_PAD_RIGHT) . "\n"; // nome do produto

                $linha2 = str_pad(formataValor($produto->valor_unitario), 9, " ", STR_PAD_LEFT);
                $linha2 .= str_pad(formataValor($produto->valor_total), 9, " ", STR_PAD_LEFT); // valor total ou bruto?
                // aqui em baixo pode vir a continuação do nome ou outras especificações?
                $linha2 = $this->alinhaEsqDir("", $linha2);

                $txt .= $linha1 . $linha2 . $linha;
            }
        }

        // totalizadores
        $txt .= $this->alinhaEsqDir("QTD. TOTAL DE ITENS", count($this->produtos));
        $txt .= $this->alinhaEsqDir("VALOR TOTAL R$", formataValor($this->total_produtos->valor_total));

        if (!empty($this->duplicatas)) {
            $txt .= $this->alinhaEsqDir("FORMA PAGAMENTO", "VALOR PAGO R$");
            $txt .= $this->alinhaEsqDir($this->duplicatas[0]->forma_pagamento, formataValor($this->total_duplicatas->valor));
            $txt .= $this->alinhaEsqDir("TROCO", "0.00"); // precisa tratar o troco de alguma forma!
        }

        $txt .= $linha;

        $txt .= $this->alinhaEsqDir("Total tributos incidentes R$", formataValor($this->total_produtos->valor_total_tributos));

        $txt .= $linha;

        // início dados fiscais
        if (!empty($this->nota_fiscal->protocolo)
            && $this->nota_fiscal->modelo == NotaFiscal::MODELO_NFC
            && $this->modelo_cupom == self::MODELO_DANFE_NFC) {
            /* tenta puxar a informação do QR Code assinado (aprovado)
             * pelo arquivo xml que já foi salvo no servidor.
             *
             * se não for encontrado, o cupom usará o endereço de montaQRCode(),
             * que não possui o digest value do xml nem o parâmetro de hash!
             *
             * TO-DO: exibir uma mensagem pedindo para reconstruir xml assinado
             * pela rotina "exportar xml"
             */
            $arq = _base_path."xml/nfe{$this->nota_fiscal->chave}.xml";

            if (file_exists($arq)) {
                include_once("class/NotaFiscal.php");

                $xml = new DocumentoXML();
                $xml->conteudo = file_get_contents($arq);

                $dom = $xml->getDOMInstance();
                $this->nota_fiscal->qr_code = $dom->getElementsByTagName("qrCode")->item(0)->nodeValue;
            }

            // área de mensagem fiscal
            // $txt .= $this->centro("NOTA FISCAL");
            $txt .= $this->alinhaEsqDir("Num. {$this->nota_fiscal->numero} Série {$this->nota_fiscal->serie}",
                "Emissão " . converteDataSql($this->nota_fiscal->data_emissao) . " {$this->nota_fiscal->hora_emissao}");
            $txt .= $this->centro("Protocolo de aprovação: {$this->nota_fiscal->protocolo}");
            $txt .= "\n";
            $txt .= $this->centro("Chave de acesso");
            $txt .= $this->centro($this->nota_fiscal->chave);

            $txt .= $linha;

            // dados do consumidor
            $documento = $this->obj_pessoa->getCpfCnpj();
            if ($documento != "00000000000") {
                $txt .= $this->centro("CONSUMIDOR");
                $txt .= $this->alinhaEsqDir("Doc.: {$documento}", $this->obj_pessoa->nome);

                $endereco = $this->obj_pessoa->endereco;
                $txt .= "{$endereco->logradouro} {$endereco->numero}\n";
                $txt .= "{$endereco->bairro} {$endereco->cidade}/{$endereco->sigla_estado}\n";
                $txt .= "CEP: {$endereco->cep}\n";
            } else {
                $txt .= $this->centro("Consumidor não informado");
            }

            $txt .= $linha;

            /* ---------------------------------------
             * QR Code
             *
             * comando daruma:
             * ESC 129 -size +size width ecc D001 D002 ... Dnnn
             */
            $txt .= $this->centro("Consulta via leitor de QR Code");
            $txt .= "\n";

            $qr_data = "00{$this->nota_fiscal->qr_code}"; // inclui bytes de controle
            $tam = strlen($qr_data);

            // escapa os bytes de tamanho na string
            $byte1 = $tam % 256;
            $byte2 = intval($tam / 256);
            $tamhex = chr($byte1) . chr($byte2);

            $qr_code = "\x1B\x81{$tamhex}{$qr_data}\n";

            $txt .= $qr_code;

            // separa a url de consulta do QR Code gerado
            $partes = explode(".br", $this->nota_fiscal->qr_code);
            $txt .= $this->centro("Consulta pela chave de acesso em");
            $txt .= $this->centro("{$partes[0]}.br");

            $txt .= $linha;
        }

        $txt .= $this->centro("maiscompleto.com.br");
        $txt .= $this->centro(hoje() . " " . date("H:i:s") . " REV {$this->obj_sistema}");

        // faz quebras de linha
        for ($i = 0; $i < 2; $i++) {
            $linhas = explode("\n", $txt);
            $txt = "";

            foreach ($linhas as $linha) {
                // impede que o QR Code seja quebrado
                if (strpos($linha, "\x1B\x81") !== false) continue;

                if (strlen($linha) > self::COLUNAS) {
                    $linha = substr($linha, 0, self::COLUNAS) . "\n" . substr($linha, self::COLUNAS);
                }

                $txt .= $linha . "\n";
            }
        }

        // retorna mensagem
        return $txt;
    }

    // ----------------------------------------------------------------------------
    // métodos privados

    /* cria a estrutura da assinatura digital do XML
     * isto é compartilhado pelos diferentes documentos da NFe
     * (o corpo da nota fiscal, os eventos de cancelamento, inutilização...)
     * estes dados devem ser assinados depois pela rotina assinaXML
     * da classe NotaFiscal\DocumentoXML
     */
    private function signature(XMLWriter $xml, $uri)
    {
        $xml->startElement("Signature");
        $xml->writeAttribute("xmlns", "http://www.w3.org/2000/09/xmldsig#");
        // -----------------------------
        $xml->startElement("SignedInfo");
        // -----------------------------
        $xml->startElement("CanonicalizationMethod");
        $xml->writeAttribute("Algorithm", "http://www.w3.org/TR/2001/REC-xml-c14n-20010315");
        $xml->endElement(); // CanonicalizationMethod

        // -----------------------------
        $xml->startElement("SignatureMethod");
        $xml->writeAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#rsa-sha1");
        $xml->endElement(); // SignatureMethod

        // -----------------------------
        $xml->startElement("Reference");
        $xml->writeAttribute("URI", $uri);
        // -----------------------------
        $xml->startElement("Transforms");
        // -----------------------------
        // primeiro algoritmo de transform
        $xml->startElement("Transform");
        $xml->writeAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#enveloped-signature");
        $xml->endElement(); // Transform

        // -----------------------------
        // segundo algoritmo de transform
        $xml->startElement("Transform");
        $xml->writeAttribute("Algorithm", "http://www.w3.org/TR/2001/REC-xml-c14n-20010315");
        $xml->endElement(); // Transform
        $xml->endElement();    // Transforms

        // -----------------------------
        $xml->startElement("DigestMethod");
        $xml->writeAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#sha1");
        $xml->endElement();    // DigestMethod

        // -----------------------------
        $xml->writeElement("DigestValue", null);
        $xml->endElement(); // Reference
        $xml->endElement(); // SignedInfo

        // -----------------------------
        $xml->writeElement("SignatureValue", null);

        // -----------------------------
        $xml->startElement("KeyInfo");
        // -----------------------------
        $xml->startElement("X509Data");
        $xml->writeElement("X509Certificate", null);
        $xml->endElement(); // X509Data
        $xml->endElement(); // KeyInfo
        $xml->endElement(); // Signature

        // retorna o objeto XMLWriter modificado
        return $xml;
    }

    /* extrai uma tag de uma string XML bem formada
     * -- apenas para operações internas
     */
    private function extraiTag($tag, $conteudo)
    {
        // abertura
        $partes = explode("<{$tag}", $conteudo);

        // fechamento
        $partes = explode("</{$tag}>", $partes[1]);

        // envelope
        return "<{$tag}{$partes[0]}</{$tag}>";
    }

    /* gera uma linha centralizada
     * (cupom fiscal)
     */
    private function centro($texto)
    {
        $tam = strlen($texto);

        if ($tam < self::COLUNAS) {
            $t2 = (int)(self::COLUNAS - $tam) / 2;
            $ws = str_repeat(" ", $t2);
            $texto = $ws . $texto . $ws;
        }

        return "{$texto}\n";
    }

    /* gera uma linha com textos alinhados à esquerda e à direita
     * (cupom fiscal)
     */
    private function alinhaEsqDir($texto_esq, $texto_dir)
    {
        $tam_esq = strlen($texto_esq);
        $tam_dir = strlen($texto_dir);
        $dif = self::COLUNAS - $tam_esq - $tam_dir;

        // se há overflow (dois campos > coluna), quebre em duas linhas
        if ($dif < 0) {
            $texto_dir = str_pad($texto_dir, self::COLUNAS, " ", STR_PAD_RIGHT);

            return "{$texto_esq}\n{$texto_dir}\n";
        } // senão, insira o espaço de diferença entre os dois
        else {
            $espacos = str_repeat(" ", $dif);

            return "{$texto_esq}{$espacos}{$texto_dir}\n";
        }
    }

    /* geração provisória da URL do QR Code da nota fiscal consumidor em acordo com:
     * > Nota Técnica 2015.002
     * > Manual de especificações técnicas do DANFE NFC-e e QR Code versão 3.2
     *
     * os valores são completados no ato da assinatura do XML
     * (digest value, hash calculado)
     */
    private function montaQRCode()
    {
        /* cadastro de URLs de consulta de homologação e produção para cada estado
         * http://nfce.encat.org/desenvolvedor/qrcode/
         *
         * GO não tem endereço de consulta! como proceder?
         */
        switch ($this->obj_empresa->endereco->cod_estado_ibge) {
            // DF
            case 53:
                $url_uf = array(
                    1 => "http://dec.fazenda.df.gov.br/ConsultarNFCe.aspx",
                    2 => "http://dec.fazenda.df.gov.br/ConsultarNFCe.aspx"
                );
                break;
            // SP
            case 35:
                $url_uf = array(
                    1 => "https://www.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaQRCode.aspx",
                    2 => "https://www.homologacao.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaQRCode.aspx"
                );
                break;
            default:
                mensagem("Estado não suportado para emissão de QR Code!", MSG_AVISO);
                return "";
        }

        // monta url de consulta
        $url = $url_uf[$this->tipo_ambiente]
            . "?chNFe={$this->nota_fiscal->chave}"
            . "&nVersao=100"
            . "&tpAmb={$this->tipo_ambiente}";

        // se houver documento do destinatário, passar aqui
        $documento = $this->obj_pessoa->getCpfCnpj();
        if ($documento != "00000000000") $url .= "&cDest={$documento}";

        // data e hora de emissão (representação em hex)
        $data_hora = formataUTC(converteDataSqlOrdenada($this->nota_fiscal->data_emissao), $this->nota_fiscal->hora_emissao);
        $url .= "&dhEmi=" . bin2hex($data_hora);

        // valores totais
        $url .= "&vNF=" . formataValor($this->total_produtos->valor_total_nota);
        $url .= "&vICMS=" . formataValor($this->total_produtos->valor_icms);

        /* digest value da nota --
         * isso é feito na classe NotaFiscal, na assinatura do XML.
         * deve ser informado aqui (convertido para base64/hex)
         * e calculado o hash final!
         */
        $url .= "&digVal=";

        // Identificador do CSC - Código de Segurança do Contribuinte no Banco de Dados da SEFAZ
        $url .= "&cIdToken={$this->obj_empresa->id_csc}";

        /* a url está pronta, mas incompleta. entendeu?
         * falta:
         * > pegar a string até aqui e incluir o CSC (secreto)
         * > calcular o hash em cima desse valor
         * > inserir o valor do hash na url como o último parâmetro
         */
        $this->nota_fiscal->qr_code = $url;
    }
}