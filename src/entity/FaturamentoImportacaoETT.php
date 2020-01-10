<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 16:18
 */

namespace src\entity;


use XMLReader;

class FaturamentoImportacaoETT extends FaturamentoETT
{
// interface externa para mensagens de erro
    private $erros_importacao;

    // ----------------------------------------------------------------------------
    // métodos públicos
    public function __construct($nota = null)
    {
        // roda construtor de Nota
        parent::__construct($nota);

        // instancia array de erros
        $this->erros_importacao = array();
    }

    /* retorna todo o array de erros de importação, ou false se não houve
     */
    public function getErrosImportacao()
    {
        return !empty($this->erros_importacao) ? $this->erros_importacao : false;
    }

    /* importa dados de um XML de compra emitido pelo fornecedor.
     * este arquivo deve ter sido salvo primeiramente,
     * após pedido gerado por exportaXMLDownload()
     *
     * retorno: FALSE para xml inexistente; TRUE para operação efetuada
     */
    public function importaXMLNotaFiscal()
    {
        $xml = new XMLReader();

        // se o arquivo não existir, provavelmente não foi importado ainda!
        $arq = _base_path."xml/nfe{$this->nota_fiscal->chave_referencia}.compra.xml";
        if (!file_exists($arq)) return false;

        // tenta abrir arquivo
        if (!$xml->open($arq)) {
            $this->erros_importacao[] = "Não foi possível ler o arquivo {$arq}";
            return true;
        }

        // declaração de variáveis temporárias da leitura
        $produto = null;
        $pessoa = null;
        $cst_origem = 0;

        // loop principal do parser
        while ($xml->read()) {
            // ------------------------------------------------------------------------
            // tags de abertura
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    /* tags do cabeçalho
                     * =================
                     */
                    case "nNF":
                        $xml->read();
                        $this->doc_fornecedor = $xml->value;
                        break;

                    case "natOp":
                        $xml->read();
                        $this->nota_fiscal->natureza_operacao = $xml->value;
                        break;

                    case "dhEmi":
                        $xml->read();
                        $this->data_emissao = left($xml->value, 10);
                        break;

                    case "idDest":
                        $xml->read();
                        $this->destino = $xml->value;
                        break;

                    case "infAdFisco":
                        $xml->read();
                        $this->nota_fiscal->informacoes_fisco = $xml->value;
                        break;

                    case "infCpl":
                        $xml->read();
                        $this->historico = $xml->value;
                        break;

                    /* tags de pessoa (emitente/destinatário)
                     * ======================================
                     */
                    case "emit":
                        $pessoa = new PessoaETT();
                        break;

                    case "CNPJ":
                    case "CPF":
                        $xml->read();
                        $pessoa->cpf_cnpj = PessoaETT::maskify($xml->value);

                        // tenta encontrar o handle da pessoa no nosso cadastro
                        $pesquisa = new PessoaGUI();
                        $pesquisa->pesquisa["pesq_cpf_cnpj"] = $pessoa->cpf_cnpj;
                        $pesquisa->fetch();
                        $pesquisa = $pesquisa->itens[0];

                        if (!empty($pesquisa->handle)) {
                            $pessoa->handle = $pesquisa->handle;
                        } else {
                            $this->erros_importacao[] = "Cadastro inexistente de pessoa com documento {$pessoa->cpf_cnpj}";
                        }
                        break;

                    case "xNome":
                        $xml->read();
                        $pessoa->nome = formataCase($xml->value, true);
                        break;

                    /* tags do produto
                     * ===============
                     */
                    case "det":
                        $produto = new FaturamentoProdutoServicoETT($this->handle);
                        break;

                    case "cProd":
                        $xml->read();
                        $produto->cod_produto_alternativo = $xml->value;

                        // tenta encontrar o produto do fornecedor no nosso cadastro
                        $pesquisa = new ProdutoGUI();
                        $pesquisa->pesquisa["pesq_cod_alternativo"] = $produto->cod_produto_alternativo;
                        $pesquisa->fetch();

                        /* como código alternativo não é uma constraint única, pode ser que haja duplicidade.
                         * isso realmente é um problema de colisão com os cadastros dos fornecedores;
                         * o mais correto seria usar um código exclusivo como o cEAN
                         */
                        if (count($pesquisa->itens) > 1) {
                            $string = "Há mais de um produto com o código alternativo {$produto->cod_produto_alternativo}: ";

                            foreach ($pesquisa->itens as $item) {
                                $string .= "{$item->handle}) {$item->nome} ";
                            }

                            $this->erros_importacao[] = $string;
                        }

                        // mapeia o primeiro produto encontrado.
                        $pesquisa = $pesquisa->itens[0];

                        if (!empty($pesquisa->handle)) {
                            $produto->cod_produto = $pesquisa->handle;
                        } else {
                            $this->erros_importacao[] = "Cadastro inexistente de produto com código alternativo {$produto->cod_produto_alternativo}";
                        }
                        break;

                    case "xProd":
                        $xml->read();
                        $produto->produto = trim($produto->cod_produto_alternativo . " " . $xml->value);
                        break;

                    case "NCM":
                        $xml->read();
                        $produto->ncm = $xml->value;
                        break;

                    case "CEST":
                        $xml->read();
                        $produto->cest = $xml->value;
                        break;

                    case "CFOP":
                        $xml->read();
                        $produto->cfop = $xml->value;
                        break;

                    case "orig":
                        $xml->read();
                        $cst_origem = $xml->value;
                        break;

                    case "CST":
                        $xml->read();
                        $produto->cst = $cst_origem . $xml->value;
                        break;

                    case "CSOSN":
                        $xml->read();
                        $produto->csosn = $xml->value;
                        break;

                    case "uCom":
                        $xml->read();
                        $produto->unidade = $xml->value;
                        break;

                    case "qCom":
                        $xml->read();
                        $produto->quantidade = $xml->value;
                        break;

                    case "vUnCom":
                        $xml->read();
                        $produto->valor_unitario = $xml->value;
                        break;

                    case "vProd":
                        $xml->read();
                        $produto->valor_bruto = $xml->value;
                        break;

                    case "vFrete":
                        $xml->read();
                        $produto->valor_frete = $xml->value;
                        break;

                    case "vDesc":
                        $xml->read();
                        $produto->valor_desconto = $xml->value;
                        break;

                    case "pIPI":
                        $xml->read();
                        $produto->perc_ipi = $xml->value;
                        break;

                    case "vIPI":
                        $xml->read();
                        $produto->valor_ipi = $xml->value;
                        break;

                    case "pICMS":
                        $xml->read();
                        $produto->perc_icms = $xml->value;
                        break;

                    case "vICMS":
                        $xml->read();
                        $produto->valor_icms = $xml->value;
                        break;

                    case "pICMSST":
                        $xml->read();
                        $produto->perc_icms_st = $xml->value;
                        break;

                    case "vICMSST":
                        $xml->read();
                        $produto->valor_icms_st = $xml->value;
                        break;

                    case "vBC":
                        /* base de cálculo: precisa ter uma flag pra saber se se trata do IPI, ICMS, etc.?
                         * ou podemos assumir que a regra é a mesma? (acho que sim pra 99% dos casos)
                         */
                        $xml->read();
                        $produto->valor_bc_ipi = $xml->value;
                        $produto->valor_bc_icms = $xml->value;
                        break;

                    case "vISSQN":
                        $xml->read();
                        $produto->valor_issqn = $xml->value;
                        break;

                    case "pPIS":
                        $xml->read();
                        $produto->perc_pis = $xml->value;
                        break;

                    case "vPIS":
                        $xml->read();
                        $produto->valor_pis = $xml->value;
                        break;

                    case "pCOFINS":
                        $xml->read();
                        $produto->perc_cofins = $xml->value;
                        break;

                    case "vCOFINS":
                        $xml->read();
                        $produto->valor_cofins = $xml->value;
                        break;

                    case "pCOFINSST":
                        $xml->read();
                        $produto->perc_cofins_st = $xml->value;
                        break;

                    case "vCOFINSST":
                        $xml->read();
                        $produto->valor_cofins_st = $xml->value;
                        break;
                }
            }

            // ------------------------------------------------------------------------
            // tags de fechamento
            if ($xml->nodeType == XMLReader::END_ELEMENT) {
                switch ($xml->name) {
                    case "det":
                        $produto->valor_total = $produto->valor_bruto + $produto->valor_ipi - $produto->valor_desconto;

                        $this->produtos[] = $produto;
                        $this->atualizaTotaisProduto($produto);
                        break;

                    case "emit":
                        $this->pessoa = trim($pessoa->cpf_cnpj . " " . $pessoa->nome);
                        $this->cod_pessoa = $pessoa->handle;
                        break;
                }
            }
        }

        // encerra o XMLReader
        $xml->close();

        // retorna sucesso
        return true;
    }
}