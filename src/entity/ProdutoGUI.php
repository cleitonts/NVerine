<?php

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class ProdutoGUI extends ObjectGUI implements InterfaceGUI{

    public function __construct($handle = null)
    {
        $this->header = array("C�d", "Alt", "Nome", "Refer�ncia", "unid.", "Marca", "Pre�o", "Ativo", "Loja Virtual", "Destaque", "Descri��o", "Fam�lia", "Grupo", "SubGrupo", "Saldo", "M�dio", "Total", "Reservado", "NCM", "Endere�o", "Material", "Peso/cal.", "C�d.barras", "Data", "Estoque", "Reserva", "Estruturado", "Filial", "Cor", "Tamanho", "Modelo", "Fabricante/Montadora");

        $this->handle = $handle;
    }

    public function getCampo($linha, $coluna)
    {

        $item = $this->itens[$linha];

        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo($item->codigo_alternativo),
            campo($item->nome),
            campo($item->unidade),
            campo($item->valor_venda),
            campo($item->ativo),
            campo($item->loja_virtual),
            campo($item->destaque),
            campo($item->descricao),
            campo($item->familia),
            campo($item->grupo),
            campo($item->grupo_pai),
            campo($item->saldo_estoque),
            campo($item->handle, "numerico"),
            campo($item->handle, "numerico"),
            campo($item->handle, "numerico"),
            campo($item->handle, "numerico"),
            campo($item->reservado),
            campo($item->ncm),
            campo($item->endereco),
            campo($item->material),
            campo($item->peso),
            campo($item->codigo_barras),
            campo($item->data),
            campo($item->saldo_estoque),
            campo($item->reserva_estoque),
            campo($item->estruturado),
            campo($item->filial),
            campo($item->cor),
            campo($item->tamanho),
            campo($item->modelo),
            campo($item->fabricante),


        ));
    }

    public function fetch()
    {
        global $conexao;
        dumper($this->pesquisa);

		// monta query de pesquisa
		$where = "WHERE ".filtraFilial("P.K_KFILIAL", "Produto");
		if(!empty($this->pesquisa["pesq_filial"]))			$where .= "AND P.K_KFILIAL = :pesq_filial\n";
		if(!empty($this->pesquisa["pesq_codigo"]))			$where .= "AND P.CODIGO = :codigo\n";
		if(!empty($this->pesquisa["pesq_cod_alternativo"])) $where .= "AND P.CODIGOALTERNATIVO = :codalternativo \n";
		if(!empty($this->pesquisa["pesq_familia"]))			$where .= "AND (FFF.HANDLE = :familia OR FF.HANDLE = :familia OR F.HANDLE = :familia) \n";
		if(!empty($this->pesquisa["pesq_grupo"]))			$where .= "AND F.HANDLE = :grupo \n";
		if(!empty($this->pesquisa["pesq_ativo"]))			$where .= "AND P.ATIVO = '".left($this->pesquisa["pesq_ativo"], 1)."'\n";
		if(!empty($this->pesquisa["pesq_loja_virtual"]))	$where .= "AND P.COTACAOINTERNET <> 'N'\n";
		if(!empty($this->pesquisa["pesq_destaque"]))		$where .= "AND P.HABILITADOFRENTELOJA = 'S'\n";
		if(!empty($this->pesquisa["pesq_marca"]))			$where .= "AND P.MARCA = :marca\n";
		if($this->pesquisa["pagina"] == "estoque_produto")	$where .= "AND P.K_CONTROLAESTOQUE = 'S' \n";

		// ordena��o
		$order_by = " ORDER BY P.NOME ASC";
		if(!empty($this->pesquisa["pesq_order_by"])){
			$order_by_caps = strtoupper(anti_injection($this->pesquisa["pesq_order_by"]));
			$order_by = "ORDER BY P.{$order_by_caps} ASC";

			// casos especiais
			if($this->pesquisa["pesq_order_by"] == "caro") {
				$order_by = "ORDER BY P.PRECOVENDA DESC";
			}
			elseif($this->pesquisa["pesq_order_by"] == "novidades") {
				$order_by = "ORDER BY P.HANDLE DESC";
			}
		}

		// limite da query/pagina��o
		$top = $this->top;
		if(!empty($this->pesquisa["pesq_top"])) 		$top = "TOP {$this->pesquisa['pesq_top']}";
		if(!empty($this->pesquisa["pesq_pagina"]))		$conexao->pagina = $this->pesquisa["pesq_pagina"];

		// busca por pre�o
		if(!empty($this->pesquisa["pesq_preco"])) {
			$partes = explode(":", $this->pesquisa["pesq_preco"]);
			$min = floatval($partes[0]);
			$max = floatval($partes[1]);

			$where .= "AND P.PRECOVENDA >= $min AND P.PRECOVENDA < $max";
		}

		// inicializa array
		$this->itens = array();

        $distinct = "";
		// puxa dados
		$sql = "SELECT {$distinct} {$top}
					-- j� que a tabela � enorme, precisamos listar cada campo utilizado para otimizar
					P.HANDLE, P.K_KFILIAL AS FILIAL, P.PRODUTOPAI PAI, P.CODIGO, P.CUSTOMAOOBRA AS PERCCOMISSAO,
					P.CODIGOALTERNATIVO, P.CODIGOREFERENCIA, P.CODIGOBARRAS, P.NUMEROSERIE,
					P.NOME, P.DESCRICAO, P.K_OTIMIZA,
					P.K_ENDERECO, E.NOME AS NOMEENDERECO, A.NOME AS ALMOXARIFADO,
					P.FAMILIA, P.MATERIAL,
					P.K_CONTROLAESTOQUE, P.K_RESERVAESTOQUE, P.K_TERCEIRO,
					P.ATIVO, P.LOTE, P.K_NCM, P.K_FORNECEDORES, P.HORIZONTEFIRME AS GARANTIA,
					P.MEDIDA_X, P.MEDIDA_Z, P.MEDIDA_Y, P.PESOVALOR,
					P.COR, P.TAMANHO, P.K_ESTRUTURADO, P.K_TIPOMOVENTRADA, P.K_TIPOMOVSAIDA,
					P.CUSTOCOMPRAS, P.PRECOVENDA, 
					P.K_ALIQUOTAICMS AS CREDICMS, P.K_ALIQUOTAIPI AS CREDIPI,
					P.MARGEMLUCRO AS MARKUP, P.K_FRETE, P.UNIDADEMEDIDAVENDAS AS UNIDADE,
					P.K_RESERVADO, P.LOCALIZACAO AS IMAGEM, P.MARCA,
					P.CODIGOSERVICO, P.HABILITADOFRENTELOJA, P.ALIQUOTASUBSTITUICAO,
					P.COTACAOINTERNET AS LOJAVIRTUAL, P.DATAINCLUSAO,
					F.NOME AS NOMEFAMILIA, F.CATMERCADOLIVRE,
					FF.NOME AS GRUPO,
					FF.HANDLE AS COD_GRUPO,
					FFF.NOME AS GRUPOPAI,
					FFF.HANDLE AS COD_GRUPOPAI,
					NCM.ALIQUOTA AS ALIQUOTAIPI,
					NCM.CODIGONBM AS COD_NCM,
					NCM.CODIGOEX,
					NCM.SITUACAOTRIB,
					FIL.NOME AS NOMEFILIAL,
					UM.ABREVIATURA AS NOMEUNIDADE,
					FAB.NOME AS NOMEFABRICANTE,
					MD.NOME AS NOMEMODELO,
					
					-- promo��o unique
					(
						SELECT TOP 1 PERCDESCONTO FROM K_LISTAPRECO L
						WHERE L.PRODUTO = P.HANDLE AND L.ATIVO = 'S' AND L.PERCDESCONTO > 0
						ORDER BY L.PERCDESCONTO DESC
					)
					AS PERCDESCONTO
				
				FROM PD_PRODUTOS P
					LEFT JOIN PD_FAMILIASPRODUTOS F ON P.FAMILIA = F.HANDLE
					LEFT JOIN PD_FAMILIASPRODUTOS FF ON F.NIVELSUPERIOR = FF.HANDLE
					LEFT JOIN PD_FAMILIASPRODUTOS FFF ON FF.NIVELSUPERIOR = FFF.HANDLE
					LEFT JOIN K_FN_ENDERECO E ON P.K_ENDERECO = E.HANDLE
					LEFT JOIN K_FN_ALMOXARIFADO A ON E.ALMOXARIFADO = A.HANDLE
					LEFT JOIN TR_TIPIS NCM ON P.K_NCM = NCM.CODIGONBM
					LEFT JOIN K_FN_FILIAL FIL ON P.K_KFILIAL = FIL.HANDLE
					LEFT JOIN CM_UNIDADESMEDIDA UM ON P.UNIDADEMEDIDAVENDAS = UM.HANDLE
					LEFT JOIN K_LISTAPRECO L ON (L.PRODUTO = P.HANDLE AND L.ATIVO = 'S' AND L.PERCDESCONTO > 0)
					LEFT JOIN K_FABRICANTE FAB ON P.FABRICANTE = FAB.HANDLE
					LEFT JOIN K_MODELO MD ON P.MODELO = MD.HANDLE 
				{$where}
				{$order_by}";
		$stmt = $conexao->prepare($sql);

		if(!empty($this->pesquisa["pesq_filial"])) 			$stmt->bindValue(":pesq_filial", $this->pesquisa["pesq_filial"]);
		if(!empty($this->pesquisa["pesq_codigo"])) 			$stmt->bindValue(":codigo", $this->pesquisa["pesq_codigo"]);
		if(!empty($this->pesquisa["pesq_cod_alternativo"])) $stmt->bindValue(":codalternativo", $this->pesquisa["pesq_cod_alternativo"]);
		if(!empty($this->pesquisa["pesq_familia"])) 		$stmt->bindValue(":familia", $this->pesquisa["pesq_familia"]);
		if(!empty($this->pesquisa["pesq_grupo"])) 			$stmt->bindValue(":grupo", $this->pesquisa["pesq_grupo"]);
		if(!empty($this->pesquisa["pesq_marca"]))			$stmt->bindValue(":marca", $this->pesquisa["pesq_marca"]);

		$stmt->execute();

		$f = $stmt->fetchAll(PDO::FETCH_OBJ);
		// insere no array
		$i = 0;

		foreach($f as $r) {
            $item = new ProdutoETT();
			$item->cont = $i;
			$item->handle = $r->HANDLE;
			$item->filial = $r->NOMEFILIAL;
			$item->pai = $r->PAI;
			$item->codigo = $r->CODIGO;
			$item->codigo_alternativo = $r->CODIGOALTERNATIVO;
			$item->codigo_barras = $r->CODIGOBARRAS;
			$item->codigo_referencia = $r->CODIGOREFERENCIA;
			$item->codigo_servico = $r->CODIGOSERVICO > 0 ? $r->CODIGOSERVICO / 100 : ""; // left($r->CODIGOSERVICO, 2).".".right($r->CODIGOSERVICO, 2);
			$item->numero_serie = $r->NUMEROSERIE;
			$item->nome = formataCase($r->NOME, true);
			$item->descricao = $r->DESCRICAO;
			$item->marca = $r->MARCA;
			$item->endereco = $r->NOMEENDERECO;
			$item->almoxarifado = $r->ALMOXARIFADO;
			$item->cod_endereco = $r->K_ENDERECO;
			$item->cod_familia = $r->FAMILIA;
			$item->familia = formataCase($r->NOMEFAMILIA);
			$item->grupo = formataCase($r->GRUPO);
			$item->grupo_pai = formataCase($r->GRUPOPAI);
			$item->cat_mercadolivre = $r->CATMERCADOLIVRE;
			$item->cod_grupo = $r->COD_GRUPO;
			$item->cod_grupo_pai = $r->COD_GRUPOPAI;
			$item->material = $r->MATERIAL;
			$item->controla_estoque = strtoupper($r->K_CONTROLAESTOQUE);
			$item->reserva_estoque = strtoupper($r->K_RESERVAESTOQUE);
			$item->terceiro = strtoupper($r->K_TERCEIRO);
			$item->ativo = strtoupper($r->ATIVO);
			$item->lote = strtoupper($r->LOTE);
			$item->ncm = $r->K_NCM;
			$item->reservado = $r->K_RESERVADO;
			$item->fornecedores = trim($r->K_FORNECEDORES, " ,");
			$item->medida_x = noZeroes($r->MEDIDA_X);
			$item->medida_z = noZeroes($r->MEDIDA_Z);
			$item->medida_y = noZeroes($r->MEDIDA_Y);
			$item->peso = $r->PESOVALOR;
			$item->garantia = formataCase($r->GARANTIA);
			$item->cor = $r->COR;
			$item->tamanho = $r->TAMANHO;
			$item->estruturado = $r->K_ESTRUTURADO;
			$item->tipo_movimento_entrada = $r->TIPOMOVENTRADA; // c�digo tribut�rio/do sistema
			$item->tipo_movimento_saida = $r->TIPOMOVSAIDA;
			$item->cod_tipo_movimento_entrada = $r->K_TIPOMOVENTRADA; // handle
			$item->cod_tipo_movimento_saida = $r->K_TIPOMOVSAIDA;
			$item->modelo = formataCase($r->NOMEMODELO, true);
			$item->cod_modelo = $r->MODELO;
			$item->fabricante = formataCase($r->NOMEFABRICANTE, true);
			$item->cod_fabricante = $r->FABRICANTE;

			// valores que vem da analise de movimento
			$item->cred_icms = $r->CREDICMS;
			$item->cred_ipi = $r->CREDIPI;
			$item->valor_custo = formataValor($r->CUSTOCOMPRAS);
			$item->valor_frete = formataValor($r->K_FRETE);
			$item->valor_venda = formataValor($r->PRECOVENDA);
			$item->markup = formataValor($r->MARKUP);

			$item->pai = $item->handle;
			$item->lote = intval($this->pesquisa["pesq_lote"]);
			$item->analise();

			// instancia movimento apenas se for pesquisa unica?
			/*if(!empty($this->pesquisa["pesq_codigo"])){
				$item->movimento->pai = $item->handle;
				$item->movimento->fetch();

				if(!empty($item->movimento)){
					// sobrescreve os valores
					$item->cred_icms = $item->movimento->icms_compra;
					$item->cred_ipi = formataValor($item->movimento->ipi_compra);
					$item->valor_custo = formataValor($item->movimento->ultimo_valor_compra);
					$item->valor_frete = formataValor($item->movimento->venda_frete);
					$item->valor_venda = formataValor($item->cred_ipi + $item->cred_icms + $item->valor_custo + $item->valor_frete + $item->markup);
				}
			}*/

			$item->ncm_aliquota_ipi = $r->ALIQUOTAIPI;
			$item->ncm_codigo = $r->COD_NCM;
			$item->ncm_codigo_ex = $r->CODIGOEX;
			$item->ncm_cst = $r->SITUACAOTRIB;
			$item->unidade = $r->NOMEUNIDADE;
			$item->cod_unidade = $r->UNIDADE;
			$item->loja_virtual = empty($r->LOJAVIRTUAL) ? 'S' : $r->LOJAVIRTUAL; // campos sem valor s�o tratados como true
			$item->destaque = $r->HABILITADOFRENTELOJA;
			$item->margem_valor_agregado = empty($r->ALIQUOTASUBSTITUICAO) ? 0 : ((float) $r->ALIQUOTASUBSTITUICAO / 1000); // salva float como int no banco
			$item->data = $r->DATAINCLUSAO;
			$item->producao = $r->K_OTIMIZA; // usado apenas em produ��o (desativado)
			// $item->patrimonio = $r->K_PATRIMONIO; // o m�dulo patrimonial nunca foi conclu�do. nem sei se isso estava certo

			$item->perc_comissao = $r->PERCCOMISSAO;
			// configura an�lise de movimento
			$item->movimento->pai = $item->codigo;

			// configura produto estruturado
			$item->estrutura->pai = $item->codigo;
			// pseudo-flag controla saldo
			if($item->reservado < 0)
				$item->controla_saldo = "N";
			else
				$item->controla_saldo = "S";

			// corrige fam�lia em dois n�veis (n�o devia...)
			if(empty($item->grupo_pai)) {
				$item->grupo_pai = $item->grupo;
				$item->grupo = $item->familia;
				// $item->grupo = "--";
			}

			if($this->handle > 0){
			    $produtoEstruturado = new ProdutoEstruturadoGUI();
			    $produtoEstruturado->pesquisa['pesq_pai'] = $this->handle;
			    $produtoEstruturado->fetch();
			    $item->estrutura = $produtoEstruturado->itens;

            }

			// listas de pre�o/promo��es
			$item->perc_desconto = empty($r->PERCDESCONTO) ? 0 : $r->PERCDESCONTO;
			$item->valor_desconto = $item->valor_venda * ($item->perc_desconto / 100);
			$item->valor_promocional = $item->valor_venda - $item->valor_desconto;

			/* busca dados espec�ficos do produto �nico
			 * (loja virtual)
			 */
			if(!empty($this->handle)) {
			    $tabela = new TabelaPrecosGUI();
			    $tabela->pesquisa["pesq_prouto"] = $this->handle;
			    $tabela->fetch();
			    $item->tabela = $tabela->itens;

                $tabela_produto_estruturado = new ProdutoEstruturadoGUI();
                $tabela_produto_estruturado->pesquisa["pesq_prouto"] = $this->handle;
                $tabela_produto_estruturado->fetch();
                $item->tabela_produto_estruturado = $tabela_produto_estruturado->itens;

				// monta galeria
				$item->galeria = array();
				$sql = "SELECT * FROM K_GALERIA WHERE PRODUTO = '{$item->codigo}' AND ATIVO = 'S'";
				$stmt = $conexao->prepare($sql);
				$stmt->execute();
				$galeria = $stmt->fetchAll(PDO::FETCH_OBJ);

				if(!empty($galeria)) {
					foreach($galeria as $foto) {
						$obj = new GaleriaGUI();
						$obj->handle = $foto->HANDLE;
						$obj->legenda = strip_tags($foto->LEGENDA);
						$obj->url = $foto->URL;
						$item->galeria[] = $obj;
					}
				}
			}

			if(!($this->pesquisa["pesq_filtra_saldo"] == 'S' && $item->movimento->saldo_estoque == "Indispon�vel")){
				array_push($this->itens, $item);
				$i++;

			}

        }

    }
}

