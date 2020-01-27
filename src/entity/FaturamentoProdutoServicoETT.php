<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 10:20
 */

namespace src\entity;


use src\services\Transact\ExtPDO as PDO;

class FaturamentoProdutoServicoETT extends ObjectETT
{
    // nota à qual pertence (setado no construtor)
    private $nota;

    // propriedades
    public $produto;                // especificações da movimentação
    public $nome_produto;
    public $tabela_preco;
    public $tipo_operacao;
    public $unidade;
    public $quantidade;

    public $medida_x;                    // técnicos
    public $medida_z;                // (tratamos estoque apenas pela quantidade)
    public $medida_t;
    public $medida_y;
    public $peso;
    public $garantia;
    public $data_entrega;
    public $data_expedicao;
    public $emenda;

    /* valores informados pela compra/venda
     * algumas siglas: BC - base de cálculo | ST - substituição tributária
     */
    public $valor_unitario;
    public $perc_desconto;
    public $valor_desconto;
    public $perc_ipi;
    public $valor_ipi;
    public $valor_bc_ipi;
    public $valor_total;
    public $perc_icms;
    public $valor_icms;
    public $valor_bc_icms;
    public $valor_frete;
    public $preco_original;

    // valores calculados pelo tipo de operação
    public $perc_pis;
    public $valor_pis;
    public $valor_bc_pis;
    public $perc_cofins;
    public $valor_cofins;
    public $valor_bc_cofins;
    public $perc_issqn;
    public $valor_issqn;
    public $valor_bc_issqn;

    /* substituições tributárias:
     * ICMS ST é implementado. outras não
     */
    public $perc_icms_st;            // inutilizado; percentual do ST é o mesmo do normal na maioria dos casos?
    public $valor_icms_st;
    public $valor_bc_icms_st;

    public $perc_cofins_st;
    public $valor_cofins_st;

    // "parâmetros" tributários
    public $ncm;
    public $cst_icms;
    public $cst_ipi;
    public $csosn;                    // *situação tributária para simples nacional
    public $cfop;

    // integração com estoque
    public $endereco;
    public $endereco_destino;        // o que é isso?
    public $lote;
    public $quantidade_entregue;
    public $quantidade_baixada;

    // integração com projeto (inutilizada)
    public $projeto;
    public $arquivo;
    public $servico;

    // não cadastra, apenas passa para referência
    public $tipo_nota;                // E/S
    // public $gera_entrada;		// esta regra está errada! venda não gera compra!

    // ** apenas gui
    public $cod_produto;
    public $cod_produto_alternativo;// código do fornecedor para importações
    public $cod_tipo_operacao;
    public $cod_endereco;
    public $valor_bruto;            // $valor_unitario * $quantidade [* $medida_t]
    public $quantidade_saldo;        // $quantidade - $quantidade_entregue - $quantidade_baixada
    public $codigo_ex;                // propriedade do NCM
    public $cest;                    // || (código de especificação de situação tributária)
    public $modalidade_bc_icms;    // propriedade do tipo de operação
    public $fator_bc_icms;            // ||
    public $perc_reducao_bc_icms;    // produto do fator
    public $usa_substituicao_tributaria;// ||
    public $cst_pis_cofins;            // ||
    public $enquadramento_ipi;        // ||
    public $codigo_servico;            // propriedade do produto (lista de serviços NN.NN)
    public $margem_valor_agregado;    // propriedade do produto
    public $valor_total_tributos;    // soma IPI, ICMS, PIS, COFINS, ISSQN...
    public $valor_total_nota;        // cálculo do total da nota fiscal varia do total do sistema!
    public $producao;

    // minimo de segurança na alteração
    public function setNota($nota){
        $this->nota = intval($nota);
    }

    // ----------------------------------------------------------------------------
    // métodos públicos
    public function __construct($nota = 0)
    {
        $this->nota = $nota;

        // zera valores (útil para totalizadores)
        $this->quantidade = 0;
        $this->quantidade_entregue = 0;
        $this->quantidade_baixada = 0;
        $this->quantidade_saldo = 0;
        $this->medida_t = 0;
        $this->peso = 0;
        $this->valor_unitario = 0;
        $this->valor_bruto = 0;
        $this->valor_ipi = 0;
        $this->valor_bc_ipi = 0;
        $this->valor_total = 0;
        $this->valor_icms = 0;
        $this->valor_bc_icms = 0;
        $this->valor_frete = 0;
        $this->valor_pis = 0;
        $this->valor_bc_pis = 0;
        $this->valor_cofins = 0;
        $this->valor_bc_cofins = 0;
        $this->valor_issqn = 0;
        $this->valor_bc_issqn = 0;
        $this->valor_icms_st = 0;
        $this->valor_bc_icms_st = 0;
        $this->valor_total_tributos = 0;
        $this->valor_total_nota = 0;
    }

    public function validaForm()
    {
        global $transact;

        // campos obrigatorios
        validaCampo($this->cod_produto, "Produto");
    }


    // métodos públicos
    public function cadastra()
    {
        global $conexao;

        $this->handle = newHandle("K_NOTAITENS", $conexao);

        // insere
        $stmt = $this->insertStatement("K_NOTAITENS",
            array(
                "HANDLE" => $this->handle,
                "NOTA" => $this->nota,
                "QTDENTREGUE" => 0,
                "QTDBAIXADA" => 0,
            ));

        // não precisa do retorno padrão aqui porque a falha nesse insert vai ocasionar uma falha no próximo update.
        if (__DEBUG__) retornoPadrao($stmt, "Alocando tabela de produtos e serviços...", "Não foi possível fazer o cadastro inicial do item");

        $this->atualiza();
    }

    public function atualiza()
    {
        /* os valores dos impostos PIS, COFINS e ISSQN não são informados na venda.
         * aqui eles são calculados de acordo com a porcentagem informada (vinda do tipo de operação)
         */
        $valor_bruto = $this->valor_unitario * $this->quantidade;

        $this->valor_pis = $valor_bruto * ($this->perc_pis / 100);
        $this->valor_cofins = $valor_bruto * ($this->perc_cofins / 100);
        $this->valor_issqn = $valor_bruto * ($this->perc_issqn / 100);

        /* BASES DE CÁLCULO: necessário integrar com tipo de operação!
         * por enquanto, <strike>assumir integral</strike> liberar campo editável
         *
         * o cálculo da base do pis/cofins é esse mesmo? por enquanto não informamos...
         */
        $this->valor_bc_pis = $this->valor_pis > 0 ? $valor_bruto : 0;
        $this->valor_bc_cofins = $this->valor_cofins > 0 ? $valor_bruto : 0;
        $this->valor_bc_issqn = $this->valor_issqn > 0 ? $valor_bruto : 0;

        /* SUBSTITUIÇÃO TRIBUTÁRIA: se houver ICMS ST,
         * o percentual é compartilhado entre o ST e normal
         */
        $this->perc_icms_st = $this->valor_icms_st > 0 ? $this->perc_icms : 0;

        $this->validaForm();

        dumper($this);
        $stmt = $this->updateStatement("K_NOTAITENS",
            array(
                "HANDLE" => $this->handle,
                "PRODUTO" => validaVazio($this->cod_produto),
                "NOMEPRODUTO" => validaVazio($this->nome_produto),
                "TABELAPRECO" => $this->tabela_preco,
                "TIPOOPERACAO" => validaVazio($this->tipo_operacao),
                "UNIDADE" => left($this->unidade, 3),
                "QUANTIDADE" => $this->quantidade,
                "MEDIDA_X" => $this->medida_x,
                "MEDIDA_Z" => $this->medida_z,
                "MEDIDA_T" => $this->medida_t,
                "MEDIDA_Y" => $this->medida_y,
                "PESO" => $this->peso,
                "DATAENTREGA" => $this->data_entrega,
                "EMENDA" => $this->emenda,

                // valores gerais
                "VALORUNITARIO" => $this->valor_unitario,
                "VALORDESCONTO" => $this->valor_desconto,
                "VALORTOTAL" => $this->valor_total,
                "VALORFRETE" => $this->valor_frete,
                "PRECOTABELA" => $this->preco_original,
                "PERCDESCONTO" => $this->perc_desconto,

                // ipi
                "VALORIPI" => $this->valor_ipi,
                "VALORBCIPI" => $this->valor_bc_ipi,
                "PERCIPI" => $this->perc_ipi,

                //icms
                "VALORICMS" => $this->valor_icms,
                "VALORBCICMS" => $this->valor_bc_icms,
                "PERCICMS" => $this->perc_icms,

                //pis
                "VALORPIS" => $this->valor_pis,
                "VALORBCPIS" => $this->valor_bc_pis,
                "PERCPIS" => $this->perc_pis,

                //cofins
                "VALORCOFINS" => $this->valor_cofins,
                "VALORBCCOFINS" => $this->valor_bc_cofins,
                "PERCCOFINS" => $this->perc_cofins,

                //issqn
                "VALORISSQN" => $this->valor_issqn,
                "VALORBCISSQN" => $this->valor_bc_issqn,
                "PERCISSQN" => $this->perc_issqn,

                //substituição
                "PERCICMSST" => $this->perc_icms_st,
                "VALORICMSST" => $this->valor_icms_st,
                "VALORBCICMSST" => $this->valor_bc_icms_st,
                "PERCCOFINSST" => $this->perc_cofins_st,
                "VALORCOFINSST" => $this->valor_cofins_st,

                "NCM" => left($this->ncm, 25),
                "CST" => left($this->cst_icms, 3) . "|" . left($this->cst_ipi, 3),
                "CFOP" => left($this->cfop, 10),
                "CSOSN" => left($this->csosn, 3),
                "NUMPROJETO" => $this->projeto,
                "ARQUIVO" => $this->arquivo,
                "SERVICO" => validaVazio($this->servico),
                "ENDERECO" => validaVazio($this->endereco),
                "ENDERECODESTINO" => $this->endereco_destino,
                "LOTE" => noZeroes(intval($this->lote)),
            ));

        retornoPadrao($stmt, "Dados do produto/serviço cód. #{$this->produto} salvos", "Não foi possível atualizar a linha de produto/serviço");
    }

    /* atualiza campos ENDERECO, LOTE, QTDENTREGUE e QTDBAIXADA
	 * realiza integração com movimento de estoque
	 */
    public function estoque()
    {
        global $conexao;

        // sanitiza quantidades para query - injetadas diretamente por compatibilidade apenas
        $this->quantidade_entregue = floatval($this->quantidade_entregue);
        $this->quantidade_baixada = floatval($this->quantidade_baixada);
        $total = $this->quantidade_entregue + $this->quantidade_baixada;

        // puxa os valores anteriores de entrega
        $sql = "SELECT QTDENTREGUE, QTDBAIXADA FROM K_NOTAITENS WHERE HANDLE = :handle AND NOTA = :nota";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":nota", $this->nota);
        $stmt->execute();

        $anterior = $stmt->fetch(PDO::FETCH_OBJ);

        // atualiza linha do produto
        $sql = "UPDATE K_NOTAITENS SET
				ENDERECO = :endereco,
				LOTE = :lote,
				QTDENTREGUE = {$this->quantidade_entregue},
				QTDBAIXADA = {$this->quantidade_baixada}
				
				-- não permite que a edição seja negativa ou mais do que a quantidade orçada
				WHERE {$this->quantidade_entregue} >= QTDENTREGUE
				AND {$this->quantidade_baixada} >= QTDBAIXADA
				AND {$total} <= QUANTIDADE
				
				AND HANDLE = :handle
				AND NOTA = :nota";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":nota", $this->nota);
        $stmt->bindValue(":endereco", validaVazio($this->endereco));
        $stmt->bindValue(":lote", noZeroes(intval($this->lote))); // só permite lote numérico

        $stmt->execute();
        retornoPadrao($stmt, "Dados de estoque atualizados.", "Não foi possível realizar a baixa do estoque do produto cód. #{$this->produto}. Confira saldo válido!");

        // exporta movimento de estoque
        $estoque = new MovimentoEstoqueETT();
        $estoque->produto = $this->produto;
        $estoque->endereco = $this->endereco;
        $estoque->lote = $this->lote;
        $estoque->quantidade = $this->quantidade_entregue - $anterior->QTDENTREGUE; // só movimenta a diferença da entrega anterior
        $estoque->valor_unitario = $this->valor_unitario; // tem que incluir descontos?
        $estoque->numero_orcamento = $this->nota;

        if ($this->tipo_nota == "E") {
            $estoque->origem = MovimentoEstoqueETT::ME_COMPRAS;
        } elseif ($this->tipo_nota == "S") {
            $estoque->origem = MovimentoEstoqueETT::ME_VENDAS;
            $estoque->quantidade = $estoque->quantidade * -1; // importante! classe de estoque não trata se é entrada ou saída
        }

        $estoque->movimenta();
    }

    /* faz o cancelamento do estoque movimentado
	 * (para cancelamento de nota de venda direto no caixa)
	 */
    public function cancelaEstoque()
    {
        global $conexao;

        // puxa os valores anteriores de entrega
        $sql = "SELECT QTDENTREGUE, QTDBAIXADA FROM K_NOTAITENS WHERE HANDLE = :handle AND NOTA = :nota";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":nota", $this->nota);
        $stmt->execute();

        $anterior = $stmt->fetch(PDO::FETCH_OBJ);

        // atualiza linha do produto
        $sql = "UPDATE K_NOTAITENS SET QTDENTREGUE = 0, QTDBAIXADA = QUANTIDADE
				WHERE HANDLE = :handle AND NOTA = :nota";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":nota", $this->nota);

        $stmt->execute();
        retornoPadrao($stmt, "Estoque foi marcado como devolvido.", "Não foi possível realizar a devolução do estoque do produto cód. #{$this->produto}. Confira saldo válido!");

        // exporta movimento reverso de estoque
        $estoque = new MovimentoEstoqueETT();
        $estoque->produto = $this->produto;
        $estoque->endereco = $this->endereco;
        $estoque->lote = $this->lote;
        $estoque->quantidade = $anterior->QTDENTREGUE * -1;
        $estoque->valor_unitario = $this->valor_unitario; // tem que incluir descontos?
        $estoque->numero_orcamento = $this->nota;
        $estoque->origem = MovimentoEstoqueETT::ME_DEVOLUCOES;

        if ($this->tipo_nota == "S") {
            $estoque->quantidade = $estoque->quantidade * -1; // importante! classe de estoque não trata se é entrada ou saída
        }

        $estoque->movimenta();
    }

    public function cancela()
    {
        /* não "cancela" os itens, só deleta para recriar.
         * não pode ser em status orçamento !
         */
        $this->deleteStatement("K_NOTAITENS", array("HANDLE" => $this->handle));

        mensagem("Apagando item #{$this->handle}...");
    }

    public function atualizaDataExpedicao()
    {
        $stmt = $this->updateStatement("K_NOTAITENS",
            array(
                "HANDLE" => $this->handle,
                "DATAEXPEDICAO" => $this->data_expedicao
            ));

        retornoPadrao($stmt, "Data da expedição foi salva", "Erro ao salvar data da expedição");
    }

    public static function getTipoOperacao()
    {
        global $conexao;

        // puxa dados de usuário
        $sql = "SELECT CODIGO, NOME, HANDLE FROM K_TIPOOPERACAO";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        foreach ($f as $r) {
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->CODIGO . ") " .$r->NOME;
        }

        return $arr;
    }
}