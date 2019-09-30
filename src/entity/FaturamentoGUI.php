<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 16:04
 */

namespace src\entity;

include_once("class/Contabil.php");

use Contabil\Conta;
use ExtPDO as PDO;

class FaturamentoGUI extends ObjectGUI implements InterfaceGUI
{
    public $nota;                // busca uma nota específica
    public $usa_exportacao;        /* requisita objeto Exportação (true) ou Nota (false)
								 * --
								 * Exportação agora também extende a classe Importação
								 * com as rotinas úteis para leitura de XML de fornecedor
								 */

    protected $fatura;            // diferencia views de notas e contratos

    public function __construct($nota = null)
    {
        $this->nota = is_null($nota) ? null : intval($nota);
        $this->usa_exportacao = false;
        $this->fatura = "S";

        $this->header = array(
            "Tipo", "Finalidade", "Origem", "Nº Orçamento", "Nº Nota", "Status", "Doc. fornecedor", "Pessoa", "UF",
            "Valor nota", "Valor IPI", "Valor total", "Valor ICMS", "Valor ICMS ST",
            "Valor Frete", "Valor PIS", "Valor COFINS", "Valor ISSQN", "Valor COFINS ST",
            "Data emissão", "Data nota", "Descrição", "Protocolo", "Chave NF-e", "Recibo", "Lote", "Data entrega", "Cód. transação",
            "E-mail", "Vendedor", "Histórico", "Financeiro", "Plano contas", "Filial"
        );
    }

    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // trata exibição do protocolo
        $protocolo = $item->nota_fiscal->protocolo;

        if ($protocolo == "CANCELADO") {
            $protocolo = "<span class='btn btn-small btn-error btn-disabled'>NOTA CANCELADA</span>";
        } elseif ($protocolo == "PROCESSAM") {
            $protocolo = "<span class='btn btn-small btn-warning'>EM PROCESSAMENTO</span>";
        } elseif ($protocolo == "REJEICAO") {
            $protocolo = "<span class='btn btn-small btn-error'>REJEIÇÃO</span>";
        } elseif ($protocolo == "DENEGADO") {
            $protocolo = "<span class='btn btn-small btn-error'>NOTA DENEGADA</span>";
        } elseif ($protocolo == "INUTILIZA") {
            $protocolo = "<span class='btn btn-small btn-info'>NOTA INUTILIZADA</span>";
        }

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->tipo),
            campo(FaturamentoETT::getFinalidade($item->finalidade)),
            campo($item->origem),
            campo($item->numero, "numerico"),
            campo($item->nota_fiscal->numero, "numerico"),
            campo($item->status->nome, "bg-color bg{$item->status->cor}"),
            campo($item->doc_fornecedor),
            campo($item->pessoa),
            campo($item->uf_pessoa),
            //campo(formataValor($item->valor_total), "numerico"),
            campo(formataValor($item->total_relatorio->valor_bruto), "numerico"),
            campo(formataValor($item->total_relatorio->valor_ipi), "numerico"),
            campo(formataValor($item->total_relatorio->valor_total), "numerico"),
            campo(formataValor($item->total_relatorio->valor_icms), "numerico"),
            campo(formataValor($item->total_relatorio->valor_icms_st), "numerico"),
            campo(formataValor($item->total_relatorio->valor_frete), "numerico"),
            campo(formataValor($item->total_relatorio->valor_pis), "numerico"),
            campo(formataValor($item->total_relatorio->valor_cofins), "numerico"),
            campo(formataValor($item->total_relatorio->valor_issqn), "numerico"),
            campo(formataValor($item->total_relatorio->valor_cofins_st), "numerico"),
            campo(converteDataSqlOrdenada($item->data_emissao)),
            campo(converteDataSqlOrdenada($item->nota_fiscal->data_emissao)),
            campo($item->descricao),
            campo($protocolo),
            campo($item->nota_fiscal->chave),
            campo($item->nota_fiscal->recibo),
            campo($item->nota_fiscal->lote),
            campo(converteDataSqlOrdenada($item->entrega->data_entrega)),
            campo("<pre>{$item->cod_transacao}</pre>"),
            campo($item->email),
            campo($item->vendedor),
            campo($item->descricao),
            campo($item->numero_doc),
            campo($item->plano_contas),
            // campo($item->supervisor),
            campo($item->filial)
        ));
    }

    public function fetch()
    {
        global $conexao;
        global $permissoes;

        /* algumas notas do sistema são escondidas com fatura = null.
         * é necessário revelar por um parâmetro especial
         */
        $where = "WHERE N.FATURA = '{$this->fatura}' AND " . filtraFilial("N.FILIAL", "Faturamento");
        if (isset($this->pesquisa["pesq_null"])) $where = "WHERE 1=1 \n";

        // if(!empty($this->pesquisa["pesq_tipo"])) $where .= "AND N.TIPO = '".left($this->pesquisa["pesq_tipo"], 1)."' \n";
        if (!empty($this->pesquisa["pesq_tipo"])) {
            if ($this->fatura == "S") {
                $where .= "	AND ((N.TIPO = :tipo AND N.FINALIDADE <> 4) OR ";
                if ($this->pesquisa["pesq_tipo"] == "E") $where .= " (N.TIPO = 'S' AND N.FINALIDADE = 4)) \n"; // troca natureza de devolução
                if ($this->pesquisa["pesq_tipo"] == "S") $where .= " (N.TIPO = 'E' AND N.FINALIDADE = 4)) \n"; // ||
            } // regra de devolução não se aplica a contratos
            else {
                $where .= "AND N.TIPO = :tipo\n";
            }
        }

        if (!empty($this->pesquisa["pesq_fonte"])) $where .= "AND N.FONTE = '" . intval($this->pesquisa["pesq_fonte"]) . "'\n";
        if (!empty($this->pesquisa["pesq_codigo"])) $where .= "AND N.HANDLE = '" . intval($this->pesquisa["pesq_codigo"]) . "'\n";
        if (!empty($this->pesquisa["pesq_cod_pessoa"])) $where .= "AND N.PESSOA = '" . intval($this->pesquisa["pesq_cod_pessoa"]) . "'\n";
        if (!empty($this->pesquisa["pesq_vendedor"])) $where .= "AND N.VENDEDOR = '" . intval($this->pesquisa["pesq_vendedor"]) . "'\n";
        if (!empty($this->pesquisa["pesq_supervisor"])) $where .= "AND N.SUPERVISOR = '" . intval($this->pesquisa["pesq_supervisor"]) . "'\n";
        if (!empty($this->pesquisa["pesq_origem"])) $where .= "AND N.ORIGEM = '" . intval($this->pesquisa["pesq_origem"]) . "'\n";
        if (!empty($this->pesquisa["pesq_doc_fornecedor"])) $where .= "AND N.DOCFORNECEDOR = " . intval($this->pesquisa["pesq_doc_fornecedor"]) . "\n";
        if (!empty($this->pesquisa["pesq_plano_contas"])) $where .= "AND PC.HANDLE = '" . intval($this->pesquisa["pesq_plano_contas"]) . "'\n";
        // if(!empty($this->pesquisa["pesq_forma_pagamento"])) $where .= "AND FP.NOME = :formapagamento\n";

        if (!empty($this->pesquisa["pesq_num_nota"])) {
            $num_nota = $this->pesquisa["pesq_num_nota"];

            // busca um intervalo de notas
            if (strpos($num_nota, ":") !== false) {
                $partes = explode(":", $num_nota);
                $partes[0] = intval($partes[0]);
                $partes[1] = intval($partes[1]);

                $where .= "AND N.NUMNOTA BETWEEN {$partes['0']} AND {$partes['1']} \n";
            } // busca um único número (pesquisa normal)
            else {
                $where .= "AND N.NUMNOTA = " . intval($num_nota) . "\n";
            }
        }

        // filtros por finalidade
        $finalidade = "";
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($this->pesquisa["pesq_finalidade_{$i}"])) $finalidade .= "{$i}, ";
        }
        if (!empty($finalidade)) {
            $finalidade = trim($finalidade, ", ");
            $where .= "AND N.FINALIDADE IN ({$finalidade}) \n";
        }
        if (isset($this->pesquisa["pesq_finalidade"])) {
            // se entrar aqui, então é o relatorio
            $relatorio_join = "LEFT JOIN (
                                SELECT 	SUM(I.VALORFRETE) AS VALORFRETE, SUM(I.VALORICMS) AS VALORICMS, 
                                            SUM(I.VALORICMSST) AS VALORICMSST, SUM(I.VALORIPI) AS VALORIPI, 
                                            SUM(I.VALORTOTAL) AS VALORTOTAL, SUM(I.VALORPIS) AS VALORPIS,
                                            SUM(I.VALORCOFINS) AS VALORCOFINS, SUM(I.VALORISSQN) AS VALORISSQN,
                                            SUM(I.VALORCOFINSST) AS VALORCOFINSST, I.NOTA
                                    FROM K_NOTAITENS I
                                    GROUP BY I.NOTA
                                ) AS ITEM ON ITEM.NOTA = N.HANDLE";
            $relatorio_select = " ITEM.*, (SELECT TOP 1 PEE.SIGLA 
                                FROM K_FN_PESSOAENDERECO PE
                                LEFT JOIN ESTADOS PEE ON PE.ESTADO = PEE.HANDLE
                                WHERE PE.PESSOA = P.HANDLE
                                ORDER BY PE.ORDEM DESC) AS ESTADOPESSOA, ";
        }

        // filtros por status
        $status = "";
        for ($i = 1; $i <= 20; $i++) {
            if (!empty($this->pesquisa["pesq_status_{$i}"])) $status .= "{$i}, ";
        }
        if (!empty($status)) {
            $status = trim($status, ", ");
            $where .= "AND N.STATUS IN ({$status}) \n";
        }

        // filtros por nota fiscal
        $protocolos = array();

        if (isset($this->pesquisa["pesq_nota_fiscal_0"])) { // nota não emitida
            $protocolos[] = "N.PROTOCOLO IS NULL";
            $protocolos[] = "N.PROTOCOLO = ''";
        }
        if (isset($this->pesquisa["pesq_nota_fiscal_1"])) { // nota aprovada
            $protocolos[] = "LEN(N.PROTOCOLO) > 10";
        }
        if (isset($this->pesquisa["pesq_nota_fiscal_2"])) { // nota cancelada
            $protocolos[] = "N.PROTOCOLO = 'CANCELADO'";
        }
        if (isset($this->pesquisa["pesq_nota_fiscal_3"])) { // nota denegada
            $protocolos[] = "N.PROTOCOLO = 'DENEGADO'";
        }

        // período
        if (!empty($this->pesquisa["pesq_data_inicial"]) && !empty($this->pesquisa["pesq_data_final"])) {
            /* define qual é a data a buscar
             * (default é emissao; definir pesq_periodo não é obrigatório)
             */
            $campo_data = "(SELECT TOP 1 DUP.DATAEMISSAO FROM K_NOTADUPLICATAS DUP WHERE DUP.NOTA = N.HANDLE) ";

            if (isset($this->pesquisa["pesq_periodo"])) {
                if ($this->pesquisa["pesq_periodo"] == 1) { // data de vencimento (duplicata)
                    $campo_data = "(SELECT TOP 1 DUP.DATAVENCIMENTOREAL FROM K_NOTADUPLICATAS DUP WHERE DUP.NOTA = N.HANDLE) ";
                } elseif ($this->pesquisa["pesq_periodo"] == 2) { // data de baixa (duplicata)
                    $campo_data = " (SELECT TOP 1 DUP.DATABAIXA FROM K_NOTADUPLICATAS DUP WHERE DUP.NOTA = N.HANDLE) ";
                } // adicionando outros filtros sem alterar
                elseif ($this->pesquisa["pesq_periodo"] == 3) { // data de ORÇAMENTO
                    $campo_data = " N.DATA ";
                } elseif ($this->pesquisa["pesq_periodo"] == 4) { // data de NF
                    $campo_data = " N.DATANOTA ";
                } elseif ($this->pesquisa["pesq_periodo"] == 5) { // data de ENTREGA
                    $campo_data = " N.DATAENTREGA ";
                }


            }

            $where .= "AND {$campo_data} >= :datainicial AND {$campo_data} <= :datafinal \n";
        }

        if (!empty($protocolos)) {
            $str_protocolos = "";

            foreach ($protocolos as $protocolo) {
                if (!empty($str_protocolos)) $str_protocolos .= " OR ";
                $str_protocolos .= $protocolo;
            }

            $where .= "AND ({$str_protocolos}) \n";
        }

        // filtro da assefe
        if (!empty($this->pesquisa["pesq_prefixo"])) {
            $where .= "AND (SELECT TOP 1 ND.PREFIXO FROM K_NOTADUPLICATAS ND WHERE ND.NOTA = N.HANDLE) = " . intval($this->pesquisa["pesq_prefixo"]) . " \n";
        }

        // query de e-mail responsável (usa tabela de vínculo)
        if ($permissoes->libera("Educacional") && $this->fatura == "N") {
            $email_responsavel = "(SELECT TOP 1 C.EMAIL FROM K_FN_CONTATO C INNER JOIN K_FN_PESSOAVINCULO VIN
			 ON C.PESSOA = VIN.PAI
			 WHERE VIN.FILHO = N.PESSOA AND VIN.RESPONSAVEL = 'S'
			 ORDER BY C.ORDEM DESC)
			AS EMAILRESPONSAVEL";
        } else {
            $email_responsavel = "'' AS EMAILRESPONSAVEL";
        }

        // monta query de pesquisa
        if (!empty($this->nota)){
            $where = "WHERE N.HANDLE = {$this->nota} AND " . filtraFilial("N.FILIAL", "Faturamento");
            $relatorio_select = "";
            $relatorio_join = "";
        }

        // puxa dados
        $sql = "SELECT {$this->top} N.*,
				F.NOME AS NOMEFILIAL,
				P.NOME AS NOMEPESSOA,
				T.NOME AS NOMETRANSPORTADORA,
				U.NOME AS NOMEUSUARIO,
				V.NOME AS NOMEVENDEDOR,
				M.NOME AS NOMEMUNICIPIO, M.CODIGOIBGE,
				S.NOME AS NOMESTATUS, S.COR AS CORSTATUS, S.GRUPOENTRADA, S.GRUPOSAIDA,
				FN.NUMERODOC,
				FO.HANDLE AS FONTE, FO.NOME AS NOMEFONTE,
				PC.NOME AS NOMEPLANO, PC.CODIGO AS CODPLANO,
				PX.NOME AS NOMEPREFIXO,
				{$relatorio_select}
				
				-- parcela única (loja virtual, contratos)
				(	SELECT TOP 1 FP.NOME
					FROM K_NOTADUPLICATAS D
					LEFT JOIN FN_FORMASPAGAMENTO FP ON D.FORMAPAGAMENTO = FP.HANDLE
					WHERE D.NOTA = N.HANDLE
					ORDER BY D.HANDLE DESC
				) AS NOMEPAGAMENTO,

				-- data da última fatura
				(	SELECT TOP 1 D.DATAEMISSAO
					FROM K_NOTADUPLICATAS D
					WHERE D.NOTA = N.HANDLE
					ORDER BY D.HANDLE DESC
				) AS DATAFATURA,

				-- e-mail principal
				(SELECT TOP 1 C.EMAIL FROM K_FN_CONTATO C WHERE C.PESSOA = N.PESSOA ORDER BY C.ORDEM DESC)
				AS EMAIL,
				{$email_responsavel}				
				
				FROM K_NOTA N
				LEFT JOIN K_STATUS S ON N.STATUS = S.HANDLE
				LEFT JOIN K_FN_FILIAL F ON N.FILIAL = F.HANDLE
				LEFT JOIN K_FN_PESSOA P ON N.PESSOA = P.HANDLE
				LEFT JOIN K_CRM_FONTES FO ON N.FONTE = FO.HANDLE
				LEFT JOIN K_FN_PESSOA T ON N.TRANSPORTADORA = T.HANDLE
				LEFT JOIN K_PD_USUARIOS U ON N.USUARIO = U.HANDLE
				LEFT JOIN K_PD_USUARIOS V ON N.VENDEDOR = V.HANDLE
				LEFT JOIN MUNICIPIOS M ON N.MUNICIPIO = M.HANDLE
				LEFT JOIN K_FINANCEIRO FN ON (FN.ORCAMENTO = N.HANDLE AND FN.NUMEROPARCELA = 1 AND FN.NUMERODOC NOT LIKE ('PCO%'))
				LEFT JOIN K_CONTAS PC ON N.PLANOCONTAS = PC.HANDLE
				LEFT JOIN K_PREFIXO PX ON N.EXTRA3 = PX.HANDLE
				{$relatorio_join}
				{$where}
				ORDER BY N.HANDLE DESC";
        //adiciona paginação
        $conexao->pagina = intval($this->pesquisa["pesq_offset"]);
        $stmt = $conexao->prepare($sql);

        if (!empty($this->pesquisa["pesq_tipo"])) $stmt->bindValue(":tipo", $this->pesquisa["pesq_tipo"]);

        if (!empty($this->pesquisa["pesq_data_inicial"]) && !empty($this->pesquisa["pesq_data_final"])) {
            $stmt->bindValue(":datainicial", converteData($this->pesquisa["pesq_data_inicial"]));
            $stmt->bindValue(":datafinal", converteData($this->pesquisa["pesq_data_final"]));
        }

        // if(!empty($this->pesquisa["pesq_forma_pagamento"])) $stmt->bindValue(":formapagamento", $this->pesquisa["pesq_forma_pagamento"]);

        $stmt->execute();

        // limpa para nao dar erro
        $conexao->pagina = null;

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // insere no array
        $i = 0;

        if (!empty($f)) {
            foreach ($f as $r) {
                $item = $this->usa_exportacao ? new FaturamentoExportacaoETT($this->nota) : new FaturamentoETT($this->nota);
                $item->cont = $i;
                $item->handle = $r->HANDLE;
                $item->nota = $r->HANDLE;
                $item->fatura = $r->FATURA;
                $item->filial = formataCase($r->NOMEFILIAL, true);
                $item->cod_filial = empty($r->FILIAL) ? 1 : $r->FILIAL; // compatibilidade com venda feita em loja (sem filial!)
                $item->usuario = formataCase($r->NOMEUSUARIO, true);
                $item->cod_usuario = $r->USUARIO;
                $item->vendedor = formataCase($r->NOMEVENDEDOR, true);
                $item->cod_vendedor = $r->VENDEDOR;
                $item->supervisor = $r->SUPERVISOR;
                $item->tipo = $item->getNomeTipo($r->TIPO);
                $item->cod_tipo = $r->TIPO;
                $item->origem = $item->getNomeOrigem($r->ORIGEM);
                $item->cod_origem = $r->ORIGEM;
                $item->destino = $r->DESTINO;                            // não há necessidade de um literal e um código?
                $item->finalidade = $r->FINALIDADE;                    // ||
                $item->numero = $r->NUMORCAMENTO;
                $item->folha_pagamento = $r->FOLHAPAGAMENTO;
                $item->data_emissao = $r->DATA;
                $item->data_fatura = $r->DATAFATURA;
                $item->pessoa = formataCase($r->NOMEPESSOA, true);
                $item->cod_pessoa = $r->PESSOA;
                $item->fonte = $r->NOMEFONTE;
                $item->cod_fonte = $r->FONTE;
                $item->plano_contas = $r->CODPLANO . " - " . formataCase($r->NOMEPLANO);
                $item->cod_plano_contas = $r->PLANOCONTAS;
                $item->descricao = $r->DESCRICAO;
                $item->historico = $r->HISTORICO;
                $item->contrato = $r->CONTRATO;
                $item->data_inicio = $r->DATAINICIO;
                $item->data_termino = $r->DATATERMINO;
                $item->dia_vencimento = $r->DIAVENCIMENTO;
                $item->valor_total = $r->VALORTOTAL;
                $item->valor_creditado = $r->VALORCREDITADO;
                $item->credito_utilizado = $r->CREDITOUTILIZADO;
                $item->cod_transacao = $r->EXTRA1;
                $item->cod_correios = $r->EXTRA2;
                $item->cod_prefixo_contrato = $r->EXTRA3;
                $item->prefixo_contrato = $r->NOMEPREFIXO;
                $item->doc_fornecedor = $r->DOCFORNECEDOR;
                $item->numero_doc = $r->NUMERODOC;
                // $item->email = $r->EMAIL;
                $item->email_responsavel = $r->EMAILRESPONSAVEL;

                $item->entrega->transportadora = formataCase($r->NOMETRANSPORTADORA, true);
                $item->entrega->cod_transportadora = $r->TRANSPORTADORA;
                $item->entrega->data_entrega = $r->DATAENTREGA;
                $item->entrega->tipo_frete = $r->FRETE; // literal
                $item->entrega->cod_tipo_frete = $r->FRETE;
                $item->entrega->placa = $r->PLACA;
                $item->entrega->uf_placa = $r->UFPLACA;
                $item->entrega->rntc = $r->RNTC;
                $item->entrega->motorista = $r->MOTORISTA;
                $item->entrega->volume_quantidade = $r->VOLQUANTIDADE;
                $item->entrega->volume_especie = $r->VOLESPECIE;
                $item->entrega->volume_marca = $r->VOLMARCA;
                $item->entrega->volume_numeracao = $r->VOLNUMERACAO;
                $item->entrega->volume_peso_bruto = $r->VOLPESOBRUTO;
                $item->entrega->volume_peso_liquido = $r->VOLPESOLIQUIDO;

                $item->entrega->bairro = trim($r->BAIRRO);
                $item->entrega->logradouro = trim($r->LOGRADOURO);
                $item->entrega->complemento = trim($r->COMPLEMENTO);
                $item->entrega->numero = trim($r->NUMERO);
                $item->entrega->estado = $r->ESTADO;
                $item->entrega->sigla_estado = $r->ESTADO;    // é o mesmo, separar?
                $item->entrega->cidade = $r->NOMEMUNICIPIO;
                $item->entrega->cod_cidade = $r->MUNICIPIO;
                $item->entrega->cod_cidade_ibge = $r->CODIGOIBGE;

                $item->nota_fiscal->modelo = $r->MODELO;
                $item->nota_fiscal->numero = $r->NUMNOTA;
                $item->nota_fiscal->chave = $r->CHAVE;
                $item->nota_fiscal->chave_referencia = $r->CHAVEREFERENCIA;
                $item->nota_fiscal->protocolo = trim($r->PROTOCOLO);
                $item->nota_fiscal->data_emissao = $r->DATANOTA;
                $item->nota_fiscal->hora_emissao = trim($r->HORANOTA);
                $item->nota_fiscal->natureza_operacao = $r->NATUREZAOPERACAO;
                $item->nota_fiscal->informacoes_fisco = $r->INFORMACOESFISCO;
                $item->nota_fiscal->xml_retorno = $r->XMLRETORNO;
                // $item->nota_fiscal->serie = $r->SERIE;				// série é sempre 1, inicialmente
                $item->nota_fiscal->lote = $r->LOTE;
                $item->nota_fiscal->recibo = $r->RECIBO;

                $item->status->handle = $r->STATUS;
                $item->status->nome = $r->NOMESTATUS;
                $item->status->cor = $r->CORSTATUS;
                $item->status->cod_grupo_entrada = $r->GRUPOENTRADA;
                $item->status->cod_grupo_saida = $r->GRUPOSAIDA;

                // sigla para o relatorio simplificado
                empty($item->entrega->sigla_estado) ? $item->uf_pessoa = $r->ESTADOPESSOA : $item->entrega->sigla_estado;

                // quick fix para status de financeiro cancelado
                if ($item->status->nome == "Faturado" && empty($item->numero_doc)) {
                    $item->status->handle = 1;
                    $item->status->nome = "(Indefinido)";
                    $item->status->cor = 13;
                }

                // popula valores de impostos, somente no relatorio
                if (!empty($relatorio_select)) {
                    $item->total_relatorio = new FaturamentoProdutoServicoETT($this->nota);
                    $item->total_relatorio->valor_ipi = $r->VALORIPI;
                    $item->total_relatorio->valor_total = $r->VALORTOTAL;
                    $item->total_relatorio->valor_icms = $r->VALORICMS;
                    $item->total_relatorio->valor_frete = $r->VALORFRETE;
                    $item->total_relatorio->valor_pis = $r->VALORPIS;
                    $item->total_relatorio->valor_cofins = $r->VALORCOFINS;
                    $item->total_relatorio->valor_issqn = $r->VALORISSQN;
                    $item->total_relatorio->valor_icms_st = $r->VALORICMSST;
                    $item->total_relatorio->valor_cofins_st = $r->VALORCOFINSST;
                    $item->total_relatorio->valor_bruto = $item->valor_total - $item->total_relatorio->valor_ipi;
                } else {
                    // manter os outros dados para compatibilidade
                    $item->total_relatorio->valor_bruto = $item->valor_total;
                }

                // alternativas de status para loja virtual
                $item->status_loja_virtual = $item->status->nome;
                if ($item->status_loja_virtual == "Entregue") $item->status_loja_virtual = "Enviado";

                // forma de pagamento da loja virtual
                $item->forma_pagamento_loja_virtual = $r->NOMEPAGAMENTO;
                /*
                switch($r->FORMAPAGAMENTO) {
                    case Duplicata::FORMA_PAGSEGURO:	$item->forma_pagamento_loja_virtual = "Pagseguro"; break;
                    case Duplicata::FORMA_BOLETO:		$item->forma_pagamento_loja_virtual = "Boleto"; break;
                    default:							$item->forma_pagamento_loja_virtual = "Outros";
                }
                */

                /* gera link do boleto para loja virtual (parcela única)
                 * para múltiplas parcelas, a url é definida no fetchSingle()
                 */
//                if($item->cod_forma_pagamento == FaturamentoDuplicataETT::FORMA_BOLETO) {
//                    $salt = "1337";
//                    $hash = "boleto{$salt}venda{$item->handle}cliente{$item->cod_pessoa}";
//                    $hash = sha1($hash);
//                    $boleto_url = _pasta."boleto.php?nota={$item->handle}&parcela=1&hash={$hash}";
//
//                    // qual é a melhor forma de passar o link?
//                    $item->url_boleto_loja_virtual = $boleto_url;
//                }

                // trata defaults de plano de contas
                if (empty($item->cod_plano_contas)) {
                    if ($item->cod_tipo == "E") {
                        $item->cod_plano_contas = Conta::CONTA_COMPRAS;
                        $item->plano_contas = "Compras (Padrão)";
                    } else {
                        $item->cod_plano_contas = Conta::CONTA_VENDAS;
                        $item->plano_contas = "Vendas (Padrão)";
                    }
                }

                // e-mails de contato múltiplos
                if (!empty($this->top)) {
                    $sql = "SELECT EMAIL FROM K_FN_CONTATO WHERE PESSOA = '{$item->cod_pessoa}' AND EMAIL <> '' AND EMAIL IS NOT NULL";
                    $stmt = $conexao->prepare($sql);
                    $stmt->execute();
                    $f = $stmt->fetchAll(PDO::FETCH_OBJ);

                    $item->email = "";
                    if (!empty($f)) {
                        foreach ($f as $r) {
                            if (!empty($item->email)) $item->email .= "; ";
                            $item->email .= trim($r->EMAIL);
                        }
                    }
                } else {
                    $item->email = $r->EMAIL;
                }

                // fallback de e-mail (contratos do educacional)
                if (empty($item->email)) $item->email = $item->email_responsavel;

                // completa dados de nota única (edição, não consulta!)
                if (!empty($this->nota)) $this->fetchSingle($item);

                array_push($this->itens, $item);
                $i++;
            }
        }
    }

    /* =========================================
	 * puxa produtos e duplicatas de nota single
	 * + comissões também!
	 * alimenta totalizadores da nota
	 * gera numeração provisória de nota fiscal
	 *
	 * TO-DO: converter em ObjetoGUI para
	 * relatórios e tela individual de
	 * duplicatas
	 *
	 * puxar também dados do cliente e da filial
	 * =========================================
	 */
    private function fetchSingle(&$item)
    { // passando por referência!
        global $conexao;

        // -----------------------------------------------------------
        // produtos e serviços
        $sql = "SELECT I.*,
				P.NOME AS PRODUTONOME, P.CODIGOSERVICO, P.K_ENDERECO AS ENDERECODEFAULT, P.ALIQUOTASUBSTITUICAO, P.K_OTIMIZA,
				T.CODIGO AS CODIGOOPERACAO, T.NOME AS NOMEOPERACAO, P.PRECOVENDA AS PRODUTOVALOR, P.HORIZONTEFIRME AS GARANTIA,
				T.MODALIDADE, T.CSTPISCOFINS, T.CALCULOBASE, T.CENQIPI,
				NCM.CODIGOEX, NCM.CODPRODUTO AS CEST
				FROM K_NOTAITENS I
				LEFT JOIN PD_PRODUTOS P ON I.PRODUTO = P.HANDLE
				LEFT JOIN K_TIPOOPERACAO T ON I.TIPOOPERACAO = T.HANDLE
				LEFT JOIN TR_TIPIS NCM ON I.NCM = NCM.CODIGONBM
				WHERE I.NOTA = :nota";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":nota", $this->nota);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($f)) {
            foreach ($f as $r) {
                $produto = new FaturamentoProdutoServicoETT($this->nota);

                $produto->handle = $r->HANDLE;

                $produto->produto = formataCase(limpaString($r->NOMEPRODUTO)); // não pode deixar passar caracteres inválidos para NF-e
                if (empty($r->NOMEPRODUTO))
                    $produto->produto = formataCase(limpaString($r->PRODUTONOME));

                $produto->data_expedicao = $r->DATAEXPEDICAO;
                $produto->cod_produto = $r->PRODUTO;
                $produto->tabela_preco = $r->TABELAPRECO;
                $produto->tipo_operacao = $r->CODIGOOPERACAO;
                $produto->cod_tipo_operacao = $r->TIPOOPERACAO;
                $produto->unidade = $r->UNIDADE;
                $produto->quantidade = $r->QUANTIDADE;
                $produto->medida_x = $r->MEDIDA_X;
                $produto->medida_z = $r->MEDIDA_Z;
                $produto->medida_t = $r->MEDIDA_T;
                $produto->medida_y = $r->MEDIDA_Y;
                $produto->peso = $r->PESO;
                $produto->emenda = $r->EMENDA;
                $produto->producao = $r->K_OTIMIZA;
                $produto->garantia = formataCase($r->GARANTIA);
                $produto->data_entrega = $r->DATAENTREGA;    //esta é uma previsão

                $produto->valor_unitario = $r->VALORUNITARIO;

                if ($produto->unidade == "M2" || $produto->unidade == "M" || $produto->unidade == "MT") {
                    $produto->valor_bruto = $produto->valor_unitario * $produto->medida_t;
                } else {
                    $produto->valor_bruto = $produto->valor_unitario * $produto->quantidade;
                }

                $produto->perc_desconto = $r->PERCDESCONTO;
                $produto->valor_desconto = $r->VALORDESCONTO; // recalcular de acordo com o percentual aqui?
                $produto->perc_ipi = formataValor($r->PERCIPI);
                $produto->valor_ipi = $r->VALORIPI;
                $produto->valor_bc_ipi = $r->VALORBCIPI;
                $produto->valor_total = $r->VALORTOTAL;    // recalcular também?
                $produto->perc_icms = formataValor($r->PERCICMS);
                $produto->valor_icms = $r->VALORICMS;
                $produto->valor_bc_icms = $r->VALORBCICMS;
                $produto->valor_frete = $r->VALORFRETE;
                $produto->preco_original = $r->PRECOTABELA;
                $produto->perc_pis = formataValor($r->PERCPIS);
                $produto->valor_pis = $produto->valor_bruto * ($produto->perc_pis / 100); // formataValor($r->VALORPIS);
                $produto->valor_bc_pis = $produto->valor_pis > 0 ? $produto->valor_bruto : 0; // formataValor($r->VALORBCPIS);
                $produto->perc_cofins = formataValor($r->PERCCOFINS);
                $produto->valor_cofins = $produto->valor_bruto * ($produto->perc_cofins / 100); // formataValor($r->VALORCOFINS);
                $produto->valor_bc_cofins = $produto->valor_cofins > 0 ? $produto->valor_bruto : 0; // formataValor($r->VALORBCCOFINS);
                $produto->perc_issqn = formataValor($r->PERCISSQN);
                $produto->valor_issqn = $produto->valor_bruto * ($produto->perc_issqn / 100); // formataValor($r->VALORISSQN);
                $produto->valor_bc_issqn = $produto->valor_issqn > 0 ? $produto->valor_bruto : 0; // formataValor($r->VALORBCISSQN);

                $produto->perc_icms_st = $r->PERCICMSST; // substituições tributárias (to-do: outras além do ICMS ST)
                $produto->valor_icms_st = $r->VALORICMSST;
                $produto->valor_bc_icms_st = $r->VALORBCICMSST;
                $produto->perc_cofins_st = $r->PERCCOFINSST;
                $produto->valor_cofins_st = $r->VALORCOFINSST;

                $produto->valor_total_tributos =
                    $produto->valor_icms +
                    $produto->valor_ipi +
                    formataValor($produto->valor_pis) +
                    formataValor($produto->valor_cofins) +
                    formataValor($produto->valor_issqn);

                // separa os CSTs de ICMS e IPI (compatibilidade)
                $partes = explode("|", $r->CST);

                if (count($partes) == 2) {
                    $produto->cst_icms = apenasNumeros($partes[0]);
                    $produto->cst_ipi = apenasNumeros($partes[1]);
                } else {
                    $produto->cst_icms = apenasNumeros($partes[0]);
                    $produto->cst_ipi = apenasNumeros($partes[0]);
                }

                $produto->cst_pis_cofins = !empty($r->CSTPISCOFINS) ? $r->CSTPISCOFINS : "01"; // assume 01 como default para cadastros vazios/desatualizados
                $produto->ncm = apenasNumeros($r->NCM);
                $produto->csosn = apenasNumeros($r->CSOSN);
                $produto->cfop = apenasNumeros($r->CFOP);
                $produto->codigo_ex = $r->CODIGOEX;
                $produto->cest = $r->CEST;
                $produto->modalidade_bc_icms = $r->MODALIDADE;
                $produto->fator_bc_icms = $r->CALCULOBASE / 100; // transforma base de cálculo de porcentagem para 0-1
                $produto->perc_reducao_bc_icms = 100 - $r->CALCULOBASE;
                $produto->usa_substituicao_tributaria = $r->SUBSTITUICAOTRIB; // S/N
                $produto->codigo_servico = left($r->CODIGOSERVICO, 2) . "." . right($r->CODIGOSERVICO, 2);
                $produto->margem_valor_agregado = empty($r->ALIQUOTASUBSTITUICAO) ? 0 : $r->ALIQUOTASUBSTITUICAO / 100;
                $produto->enquadramento_ipi = empty($r->CENQIPI) ? "999" : $r->CENQIPI; // 999 = compatibilidade reversa

                // nota: se não tiver endereço salvo, tem que puxar o default do produto.
                $produto->cod_endereco = empty($r->ENDERECO) ? $r->ENDERECODEFAULT : $r->ENDERECO;
                $produto->endereco = $r->endereco; // literal
                $produto->endereco_destino = $r->ENDERECODESTINO;
                $produto->lote = $r->LOTE;
                $produto->quantidade_entregue = $r->QTDENTREGUE;
                $produto->quantidade_baixada = $r->QTDBAIXADA;
                $produto->quantidade_saldo = $produto->quantidade - $produto->quantidade_entregue - $produto->quantidade_baixada;

                $produto->projeto = $r->NUMPROJETO;
                $produto->arquivo = $r->ARQUIVO;
                $produto->servico = $r->SERVICO;

                // insere no array
                array_push($item->produtos, $produto);

                // soma totalizadores
                $item->atualizaTotaisProduto($produto);
            }
        }

        // -----------------------------------------------------------
        // duplicatas
        $sql = "SELECT D.*,
				F.NOME NOMEPAGAMENTO, F.CODIGO AS TIPOPAGAMENTO,
				'' NOMEPREFIXO
				FROM K_NOTADUPLICATAS D
				LEFT JOIN FN_FORMASPAGAMENTO F ON D.FORMAPAGAMENTO = F.HANDLE
				-- LEFT JOIN K_FN_PREFIXO P ON D.PREFIXO = P.HANDLE
				WHERE D.NOTA = :nota";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":nota", $this->nota);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($f)) {
            foreach ($f as $r) {
                if (!isset($vencimento_anterior)) $vencimento_anterior = $r->DATAEMISSAO; // intervalo inicial

                $duplicata = new FaturamentoDuplicataETT($this->nota);

                $duplicata->handle = $r->HANDLE;
                $duplicata->numero = $r->NUMERO;
                $duplicata->forma_pagamento = formataCase($r->NOMEPAGAMENTO);
                $duplicata->cod_forma_pagamento = $r->FORMAPAGAMENTO;
                $duplicata->tipo_pagamento_nfe = empty($r->TIPOPAGAMENTO) ? "99" : insereZeros($r->TIPOPAGAMENTO, 2);
                $duplicata->prefixo = formataCase($r->NOMEPREFIXO);
                $duplicata->cod_prefixo = $r->PREFIXO;
                $duplicata->data_emissao = $r->DATAEMISSAO;
                $duplicata->data_vencimento_original = $r->DATAVENCIMENTOORIGINAL;
                $duplicata->data_vencimento_real = $r->DATAVENCIMENTOREAL;
                $duplicata->data_baixa = $r->DATABAIXA;
                $duplicata->dias = round(diasEntre($duplicata->data_emissao, $duplicata->data_vencimento_real));
                $duplicata->intervalo = round(diasEntre($vencimento_anterior, $duplicata->data_vencimento_real));
                $duplicata->valor = $r->VALOR;
                $duplicata->baixado = $r->BAIXADO;
                $duplicata->cheque = $r->CHEQUE;

                $duplicata->valor_aditivo = $r->VALORADITIVO;
                $duplicata->valor_juros = $r->VALORJUROS;
                $duplicata->valor_multa = $r->VALORMULTA;
                $duplicata->valor_desconto = $r->VALORDESCONTO;
                $duplicata->valor_total = $r->VALORTOTAL;

                // para o indicador de forma de pagamento à vista ou à prazo
                if ($duplicata->dias <= 0)
                    $item->forma_pagamento = 0;
                else
                    $item->forma_pagamento = 1;

                // para o cálculo do próximo intervalo
                $vencimento_anterior = $duplicata->data_vencimento_real;

                // para forma de pagamento boleto
                $duplicata->nosso_numero = $r->NOSSONUMERO;
                $duplicata->banco = $r->BANCO;
                $duplicata->boleto_hash = "";
                $duplicata->boleto_url = "";

                if ($duplicata->cod_forma_pagamento == FaturamentoDuplicataETT::FORMA_BOLETO) {
                    $salt = "1337";
                    $hash = "boleto{$salt}venda{$item->handle}cliente{$item->cod_pessoa}";
                    $hash = sha1($hash);

                    $duplicata->boleto_hash = $hash;
                    $duplicata->boleto_url = _pasta . "boleto.php?nota={$item->handle}&parcela={$duplicata->numero}&hash={$duplicata->boleto_hash}";
                }

                // altera valores de desconto para contrato
                if ($item->fatura == "N") {
                    $duplicata->valor_total = $duplicata->valor;
                    $duplicata->valor += $item->total_produtos->valor_desconto;
                    $duplicata->valor_desconto = $item->total_produtos->valor_desconto;
                }

                // insere no array
                array_push($item->duplicatas, $duplicata);

                // soma totalizadores
                $item->total_duplicatas->valor += $duplicata->valor;
                $item->total_duplicatas->valor_aditivo += $duplicata->valor_aditivo;
                $item->total_duplicatas->valor_juros += $duplicata->valor_juros;
                $item->total_duplicatas->valor_multa += $duplicata->valor_multa;
                $item->total_duplicatas->valor_desconto += $duplicata->valor_desconto;
                $item->total_duplicatas->valor_total += $duplicata->valor_total;
            }
        }

        // -----------------------------------------------------------
        // comissões

        $sql = "SELECT C.*, P.NOME
				FROM K_NOTACOMISSAO C
				LEFT JOIN K_FN_PESSOA P ON C.PESSOA = P.HANDLE
				WHERE C.NOTA = :nota
				ORDER BY C.TIPO";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":nota", $this->nota);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($f)) {
            foreach ($f as $r) {
                $comissao = new FaturamentoComissaoETT($this->nota);

                $comissao->handle = $r->HANDLE;
                $comissao->pessoa = formataCase($r->NOME, true);
                $comissao->cod_pessoa = $r->PESSOA;
                $comissao->valor_base = $r->VALORBASE;
                $comissao->percentual = $r->PERCENTUAL;
                $comissao->valor = $r->VALOR;
                $comissao->tipo = $r->TIPO;
                $comissao->historico = $r->HISTORICO;

                // insere no array
                array_push($item->comissao, $comissao);
            }
        }


        // -----------------------------------------------------------
        // numeração base da nota fiscal
        if (empty($item->nota_fiscal->numero) && empty($item->nota_fiscal->chave)) {
            // puxa dados da empresa
            $empresa = new FilialGUI();
            $empresa->filial = $item->cod_filial;
            $empresa->fetch();
            $empresa = $empresa->itens[0];

            // busca numeração base
            $sql = "SELECT COUNT(HANDLE) AS BASE FROM K_NOTA
					WHERE NUMNOTA <> '' AND NUMNOTA IS NOT NULL
					AND FATURA = 'S' AND " . filtraFilial("FILIAL", "Faturamento");
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $numeracao = $stmt->fetch(PDO::FETCH_OBJ);

            // atualiza número provisório e monta chave
            $item->nota_fiscal->numero = $numeracao->BASE + $empresa->sequencia_nota;
            $item->nota_fiscal->montaChave($empresa);

            /* numeração base de lote (processamento assíncrono)
             * >> ESQUISITO? deve gerar numeração de lote novo para o reenvio da mesma nota?
             */
            if (empty($item->nota_fiscal->lote)) {
                $sql = "SELECT MAX(LOTE) AS LOTE FROM K_NOTA
						WHERE FATURA = 'S' AND " . filtraFilial("FILIAL", "Faturamento");
                $stmt = $conexao->prepare($sql);
                $stmt->execute();
                $numeracao = $stmt->fetch(PDO::FETCH_OBJ);

                $item->nota_fiscal->lote = $numeracao->LOTE + 1;
                if (empty($item->nota_fiscal->lote)) $item->nota_fiscal->lote = 1;
            }
        }
    }
}