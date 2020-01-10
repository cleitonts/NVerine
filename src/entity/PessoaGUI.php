<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 30/04/2019
 * Time: 08:35
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class PessoaGUI extends ObjectGUI implements InterfaceGUI
{
    /* flag que controla o mapeamento de campos extra do educacional
	 * controlada apenas aqui ou pelas classes-filhas
	 */
    protected $educacional;
    protected $ficha_medica;

    /* use esta flag para não puxar endereços e contatos na listagem de pessoas.
     * deve otimizar as consultas significativamente
     */
    public $bypass_enderecos;

    // passa os dados para a filha mapear
    public $dados_educacional = array();
    // construtor
    public function __construct($handle = null)
    {
        // cabeçalho padrão
        $this->header = array(
            "Código", "Nome", "Nome fantasia (apelido)", "Tipo", "CPF/CNPJ", "Rg", "Area", "Cidade", "Estado", "Logradouro", "Bairro", "Numero", "E-mail", "Telefone",
            "Segmento", "Comercial", "Cliente (CRE)", "Fornecedor (CPA)", "Funcionário", "Empresa", "Transportadora", "Ativo", "Lista de preço");

        // header abreviado (opcional)
        $this->header_abrev = array(
            "Cód.", "Nome", "Fantasia", "Tipo", "CPF/CNPJ", "Rg", "Area", "Cidade", "Estado", "Logradouro", "Bairro", "Numero", "E-mail", "Telefone",
            "Segmento", "Comercial", "Cli.", "Forn.", "Func.", "Emp.", "Transp.", "Ativo", "Lista preço");

        // campos a exibir por default
        // $this->exibe = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 19);

        // flags
        $this->educacional = false;
        $this->ficha_medica = false;
        $this->bypass_enderecos = false;
    }

    // métodos públicos
    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo(formataCase($item->nome, true)),
            campo(formataCase($item->nome_fantasia, true)),
            //campo($item ->data_nascimento),
            campo("P" . $item->tipo),
            campo($item->cpf_cnpj),
            campo($item->rg),
            campo($item->area),
            campo($item->endereco->cidade),
            campo($item->endereco->estado),
            campo($item->endereco->logradouro),
            campo($item->endereco->bairro),
            campo($item->endereco->numero),
            campo($item->email),
            campo($item->contatos[0]->telefone . " ° " . $item->contatos[1]->telefone),
            campo($item->segmento),
            campo($item->vendedor),
            campo(formataLogico($item->cliente)),
            campo(formataLogico($item->fornecedor)),
            campo(formataLogico($item->funcionario)),
            campo(formataLogico($item->empresa)),
            campo(formataLogico($item->transportador)),
            campo(formataLogico($item->ativo)),
            campo($item->lista_preco)
        ));
    }

    public function fetch()
    {


        global $conexao;

        /* não filtrar filial quando estiver buscando uma única pessoa/aluno.
         * quando precisar do dado da filial para outros ambientes, buscar aqui primeiro
         */
        if (!empty($this->pesquisa["pesq_num"])) {
            $where = "WHERE 1 = 1 \n";
        } else {
            $where = "WHERE " . filtraFilial("P.FILIAL", "Pessoas");
        }


        // monta query de pesquisa
        $order = "P.NOME ASC";

        if (!empty($this->pesquisa["pesq_filial"])) $where .= "AND P.FILIAL = :filial\n";
        if (!empty($this->pesquisa["pesq_num"])) $where .= "AND P.HANDLE = :codigo\n";
        if (!empty($this->pesquisa["pesq_nome"])) $where .= "AND (P.NOME LIKE :nome OR P.NOMEFANTASIA LIKE :nome)\n";
        if (!empty($this->pesquisa["pesq_nome_exato"])) $where .= "AND P.NOME = :nomeexato\n";
        if (!empty($this->pesquisa["pesq_cpf_cnpj"])) $where .= "AND P.CPFCNPJ = :cpfcnpj\n";
        if (!empty($this->pesquisa["pesq_bolsa"])) $where .= "AND P.BOLSA = :bolsa\n";
        if (!empty($this->pesquisa["pesq_transtorno"])) $where .= "AND FM.TIPOTRANSTORNO = :transtorno\n";
        if (!empty($this->pesquisa["pesq_transtorno_like"])) $where .= "AND FM.TRANSTORNOSJSON LIKE :transtorno_like \n";

        // relatório de aniversariantes
        if (!empty($this->pesquisa["pesq_mes_nascimento"])) {
            $where .= "AND MONTH(P.NASCIMENTO) = :mesnascimento \n";
            $order = "DAY(P.NASCIMENTO) ASC";
        }

        if (!empty($this->pesquisa["pesq_data_adesao_inicio"])) $where .= "AND DATAADESAO BETWEEN :data_inicio AND :data_fim \n";

        // páginas
        if ($this->pesquisa["pesq_pagina"] == "A") $where .= "AND P.ALUNO = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "P") $where .= "AND P.PROFESSOR = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "C") $where .= "AND P.CLIENTE = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "F") $where .= "AND P.FORNECEDOR = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "FUNC") $where .= "AND P.FUNCIONARIO = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "E") $where .= "AND P.EMPRESA = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "T") $where .= "AND P.TRANSPORTADOR = 'S'\n";
        elseif ($this->pesquisa["pesq_pagina"] == "B") $where .= "AND P.AGRUPABOLETO = 'S'\n";

        if(!empty($_REQUEST["pesq_funcoes"])) {
            $funcoes = array(   "P.ATIVO = 'S'", "P.CLIENTE = 'S'", "P.FORNECEDOR = 'S'",  "P.FUNCIONARIO = 'S'",
                                "P.EMPRESA = 'S'", "P.ALUNO = 'S'", "P.PROFESSOR = 'S'", "P.TRANSPORTADOR = 'S'");

            $where .= "AND (";
            foreach ($_REQUEST["pesq_funcoes"] as $k => $r) {
                if(isset($or)){
                    $where .= " AND ";
                }
                $or = true;
                $where .= $funcoes[$k-1];
            }
            $where .= ") \n";
        }

        // (ativos ou inativos)
        // pesquisa por matricula antes
        if (!empty($this->pesquisa["pesq_matricula"])) {
            $where .= "AND P.HANDLE = " . (int)right($this->pesquisa["pesq_matricula"], 6) . "\n";
        } elseif ($this->pesquisa["pesq_pagina"] == "I" && empty($this->pesquisa["pesq_num"])) {
            $where .= "AND (P.ATIVO = 'N' OR P.ATIVO IS NULL)\n";
        } elseif (empty($this->pesquisa["pesq_num"])) {
            $where .= "AND P.ATIVO = 'S'\n";
        }

        // pesquisa situação do aluno
        if (!empty($this->pesquisa["pesq_situacao_aluno"])) {
            // não é bonito...
            $where .= "AND P.DADOSCENSO LIKE '%\\quotemotivoTransf\\quote:\\quote" . anti_injection($this->pesquisa["pesq_situacao_aluno"]) . "\\quote%' \n";
        }

        // usa ou não ficha médica? (precisa para filtros)
        if ($this->ficha_medica) {
            /* isso é o melhor que consigo fazer por enquanto.
             * se um aluno for alterado de transtorno entre diferentes fichas médicas,
             * irá aparecer duplicado!
             * (depois precisa enforçar os dados da ficha anterior no novo cadastro)
             */
            $select_transtorno = ", FM.*";

            // se não usar o filtro de transtorno, tem que deixar passar alunos sem ficha!
            $tipo_join = "LEFT";

            if (!empty($this->pesquisa["pesq_transtorno"]) || !empty($this->pesquisa["pesq_transtorno_like"])) {
                $tipo_join = "INNER";
            }

            $join_transtorno = "{$tipo_join} JOIN (SELECT DISTINCT ALUNO, TIPOTRANSTORNO, AVALIACAO,
								CONVERT(VARCHAR(1000), TRANSTORNOSJSON) TRANSTORNOSJSON
								FROM K_FICHAMEDICA) FM ON FM.ALUNO = P.HANDLE";
        } else {
            $select_transtorno = "";
            $join_transtorno = "";
        }

        // inicializa array
        $this->itens = array();

        // puxa dados
        $sql = "SELECT {$this->top}
				P.*,
				F.NOME NOMEFILIAL,
				F.HANDLE HANDLEFILIAL,
				SEG.NOME NOMESEGMENTO,
				VEN.NOME NOMEVENDEDOR,
				ARE.NOME NOMEAREA {$select_transtorno}

				FROM K_FN_PESSOA P {$join_transtorno}

				-- P.HANDLE não pode ser a condição de nenhum LEFT join. fazer selects únicos
				LEFT JOIN K_FN_FILIAL F ON P.FILIAL = F.HANDLE
				LEFT JOIN K_CRM_SEGMENTOS SEG ON P.SEGMENTO = SEG.HANDLE
				LEFT JOIN K_PD_USUARIOS VEN ON P.VENDEDOR = VEN.HANDLE
				LEFT JOIN K_FN_AREA ARE ON P.AREA = ARE.HANDLE
				{$where}
				ORDER BY {$order}";

        $stmt = $conexao->prepare($sql);

        if (!empty($this->pesquisa['pesq_handle_filial'])) $stmt->bindValue(":handle_filial", $this->pesquisa['pesq_handle_filial']);
        if (!empty($this->pesquisa['pesq_filial'])) $stmt->bindValue(":filial", $this->pesquisa['pesq_filial']);
        if (!empty($this->pesquisa["pesq_num"])) $stmt->bindValue(":codigo", $this->pesquisa["pesq_num"]);
        if (!empty($this->pesquisa["pesq_nome"])) $stmt->bindValue(":nome", stringPesquisa($this->pesquisa["pesq_nome"]));
        if (!empty($this->pesquisa["pesq_nome_exato"])) $stmt->bindValue(":nomeexato", trim($this->pesquisa["pesq_nome_exato"]));
        if (!empty($this->pesquisa["pesq_cpf_cnpj"])) $stmt->bindValue(":cpfcnpj", PessoaETT::maskify($this->pesquisa["pesq_cpf_cnpj"]));
        if (!empty($this->pesquisa["pesq_bolsa"])) $stmt->bindValue(":bolsa", $this->pesquisa["pesq_bolsa"]);
        if (!empty($this->pesquisa["pesq_transtorno"])) $stmt->bindValue(":transtorno", $this->pesquisa["pesq_transtorno"]);
        if (!empty($this->pesquisa["pesq_transtorno_like"])) $stmt->bindValue(":transtorno_like", "%{$this->pesquisa['pesq_transtorno_like']}%");
        if (!empty($this->pesquisa["pesq_mes_nascimento"])) $stmt->bindValue(":mesnascimento", $this->pesquisa["pesq_mes_nascimento"]);
        if (!empty($this->pesquisa["pesq_data_adesao_inicio"])) $stmt->bindValue(":data_inicio", converteData($this->pesquisa["pesq_data_adesao_inicio"]));
        if (!empty($this->pesquisa["pesq_data_adesao_inicio"])) $stmt->bindValue(":data_fim", converteData($this->pesquisa["pesq_data_adesao_fim"]));

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // insere no array
        $i = 0;

        foreach ($f as $r) {
            $item = new PessoaETT();
            $item->handle = $r->HANDLE;
            $item->ativo = $r->ATIVO;
            $item->tipo = $r->TIPO;
            $item->cont = $i;

            // se estiver vazio é físico
            if (empty($item->tipo))
                $item->tipo = "F";

            // flags, true or false
            $item->cliente = $r->CLIENTE;
            $item->fornecedor = $r->FORNECEDOR;
            $item->funcionario = $r->FUNCIONARIO;
            $item->empresa = $r->EMPRESA;
            $item->transportador = $r->TRANSPORTADOR;
            $item->aluno = $r->ALUNO;
            $item->professor = $r->PROFESSOR;

            // data de nascimento vai voltar a ser global
            $item->data_nascimento = $r->NASCIMENTO;
            $item->agrupa_boleto = $r->AGRUPABOLETO;
            $item->contribuinte_icms = $r->CONTRIBUINTEICMS;
            $item->nome = formataCase($r->NOME, true); // todos os nomes devem receber tratamento de formataCase na classe
            $item->nome_fantasia = formataCase($r->NOMEFANTASIA, true);
            $item->segmento = formataCase($r->NOMESEGMENTO, true);
            $item->vendedor = formataCase($r->NOMEVENDEDOR, true);
            $item->cod_segmento = $r->SEGMENTO;
            $item->cod_vendedor = $r->VENDEDOR;
            $item->cpf_cnpj = trim($r->CPFCNPJ);
            $item->rg = trim($r->RG);
            $item->cnae = trim($r->CNAE);
            $item->conta_pagamento = $r->CONTAPAGAMENTO;
            $item->observacoes = $r->OBSERVACOES;
            $item->tabela_preco = $r->LISTAPRECO;
            $item->lista_preco = $r->LISTA;
            $item->filial = $r->NOMEFILIAL;
            $item->cod_filial = $r->HANDLEFILIAL;
            $item->cod_area = $r->AREA;
            $item->area = $r->NOMEAREA;

            // campos para configurar financeiro na venda
            $item->forma_pagamento = $r->FORMAPAGAMENTO;
            $item->condicao_pagamento = $r->CONDICAOPAGAMENTO;

            // novos endereços/contatos
            if (!$this->bypass_enderecos) {
                // carrega endereços
                $endereco = new PessoaEnderecoGUI();
                $endereco->pesquisa["pesq_pessoa"] = $r->HANDLE;
                $endereco->fetch();
                $item->enderecos = $endereco->itens;

                // carrega contatos
                $contato = new PessoaContatoGUI();
                $contato->pesquisa["pesq_pessoa"] = $r->HANDLE;
                $contato->fetch();
                $item->contatos = $contato->itens;

                // compatibilidade reversa com objetos antigos
                $item->endereco = !empty($item->enderecos) ? $item->enderecos[0] : new PessoaEnderecoETT();
                $item->email = $item->contatos[0]->email;
                $item->telefone = $item->contatos[0]->telefone;
                $item->contato = $item->contatos[0]->nome;
                $item->area_contato = $item->contatos[0]->area;

            } else {
                $string = "?";
                $item->endereco = new PessoaEnderecoETT();
                $item->endereco->cidade = $string;
                $item->endereco->estado = $string;
                $item->email = $string;
                $item->telefone = $string;
                $item->contato = $string;
                $item->area_contato = $string;
                $item->foto_relatorio = $string;
            }

            // mapeia os vinculos
            if($this->handle > 0){
                $vinculos = new PessoaVinculoGUI();
                $vinculos->pessoa = $this->handle;
                $vinculos->fetch();
                $item->vinculos = $vinculos->itens;
            }

            // configura análise de movimento
            $item->credito->pai = $item->handle;
            $item->credito->bloqueio = $r->BLOQUEIO;
            $item->credito->restricao = $r->RESTRICAO;

            /* para ajudar na manutenção do código, o fetch de todos os campos da tabela é feito aqui.
             * a flag educacional controla se as propriedades existem em $item
             * e se os campos extra do educacional vão ser mapeados.
             */
            if ($this->educacional) {
                // passando os dados para a classe educacional mapear
                $this->dados_educacional[] = $r;
            }

            array_push($this->itens, $item);
            $i++;
        }
    }
}