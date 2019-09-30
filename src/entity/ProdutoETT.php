<?php

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class ProdutoETT extends ObjectETT
{
    	// materiais
	const PD_MATERIA_PRIMA = 0;
	const PD_MANUFATURADO = 1;
	const PD_SERVICOS = 2;
	const PD_INSUMOS = 3;
	const PD_VIDRO_COMUM = 4;
	const PD_VIDRO_TEMPERADO = 5;
	const PD_VIDRO_LAMINADO = 6;
	const PD_VIDRO_RESINADO = 7;
	const PD_COMPONENTE = 8;
	const PD_ACESSORIO = 9;
	const PD_LIVRO = 10;
	const PD_ALIMENTO = 11;

	// propriedades
	public $pai;
	public $codigo;					// enforçar numérico! por enquanto é alimentado o valor do handle.
	public $codigo_alternativo;		// isso é o código do FORNECEDOR, não pode ser sequestrado!
	public $codigo_referencia;		// campo desativado
	public $codigo_servico;			// esse é o código da lista de serviços da nota fiscal (padrão NN.NN, mas salva inteiro NNNN)
	public $codigo_barras;
	public $numero_serie;
	public $nome;
	public $descricao;
	public $marca;
	public $endereco;
	public $familia;
	public $cat_mercadolivre;
	public $material;
	public $ncm;
	public $reservado;				// em estoque; aplicar se reserva_estoque
	public $imagem;					// imagem padrão (url) - depois terá galeria de imagens alternativas
	public $galeria;				// array com objetos Galeria
	public $data;					// para a data de edição de livros, mas pode ser usado para outros fins
	public $modelo;
	public $fabricante;				// ou montadora

	// booleanos (S/N)
	public $controla_estoque;
	public $reserva_estoque;
	public $terceiro;
	public $ativo;
	public $lote;
	public $loja_virtual;			// exibe em loja virtual / PDV
	public $destaque;				// exibe na front page da loja
	// public $patrimonio;			// inutilizado

	// industrial

	/* Devido a várias confusões no código o padrão XYZ está sendo adotado
	 * medida_x = maior medida do produto
	 * medida_z = segunda maior medida do produto
	 * medida_y = profundidade do produto
	*/

	/* para o rei dos capachos está sendo usado
	 *
	 * novo nome	//	significado		//	antigo nome
	 * medida_x 	=	largura			<= 	altura
	 * medida_z 	=	comprimento		<= 	largura
	 * medida_y 	=	espessura		<= 	espessura // comprimento
	 * medida_t		=	metragem		<=	metragem
	*/
	public $medida_x;
	public $medida_z;
	public $medida_y;

	public $peso;
	public $unidade;
	public $cor;
	public $tamanho;
	public $estruturado; 			// booleano
	public $garantia; 				// text

	// formação de preço
    public $mva;
    public $valor_custo;
	public $cred_ipi;
	public $cred_icms;
	public $valor_frete;
	public $markup;
	public $valor_venda;
	public $margem_valor_agregado;	// porcentagem para cálculo do ICMS
    public $valor_cred_icms;
    public $valor_cred_ipi;
    public $valor_preco_fob;
    public $valor_markup;
    public $valor_icms;
    public $perc_ipi;
    public $valor_descontos;
    public $valor_acrescimos;
    public $perc_margem;


	// foreign keys
	public $tipo_movimento_entrada;
	public $tipo_movimento_saida;

	// strings como foreign keys n:n
	public $fornecedores;
	// public $tarifas;				deprecado: veja tipo de operação

	// apenas para gui
	public $filial;
	public $cod_endereco;
	public $cod_tipo_movimento_entrada;
	public $cod_tipo_movimento_saida;
	public $cod_familia;			// vira subgrupo
	// public $cod_cor;
	public $cod_unidade;
	public $cod_modelo;
	public $cod_fabricante;
	public $grupo;
	public $grupo_pai;				// vira família
	public $cod_grupo;
	public $cod_grupo_pai;
	public $ncm_aliquota_ipi;
	public $ncm_codigo;
	public $ncm_codigo_ex;
	public $ncm_cst;
	public $almoxarifado;
	public $controla_saldo;			// tira em cima do $reservado

	// análise de movimento
	public $movimento;

	// produto estruturado
	public $estrutura;

	// gera otimização (indústria)
	 public $producao;

	// tarifas vinculadas
	public $tarifas_vinculadas;

	// tabelas de preço (array $indice => $porcentagem)
	public $tabela_preco;
	public $nome_tabela_preco;
	public $qtd_tabela_preco;
	public $tabela = array();

	// preço promocional (lista de preços global) -- loja virtual apenas?
	public $valor_promocional;
	public $valor_desconto;
	public $perc_desconto;
    public $perc_comissao;

    public $saldo_estoque;
    public $preco_medio;
    public $valor_estoque;
    public $data_inclusao;
    public $cont;
    public $valor_custo_pe;
    public $valor_markup_pe;
    public $valor_venda_pe;

    /**
     *
     */
    public function analise(){
		// puxa estoque
		$estoque = new MovimentoEstoqueETT();
		$estoque->produto = $this->pai;
		$estoque->lote = $this->lote;
		$valores = $estoque->getSaldoEstoque();
		$this->saldo_estoque = $valores[0];
		$this->preco_medio = $valores[1];
		$this->valor_estoque = $valores[2];
	}

	public static function get_familia_produto($grupo){
        global $conexao;

        $sql = "select * from PD_FAMILIASPRODUTOS where ". filtraFilial("K_FILIAL", "Pessoas", false);
        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach ($f as $r){
            if($grupo == "familia"){
                if($r->NIVELSUPERIOR == null){
                    $arr['handle'][] = $r->HANDLE;
                    $arr['nivelsuperior'][] = $r->NIVELSUPERIOR;
                    $arr['familia'][] = $r->FAMILIA;
                    $arr['k_filial'][] = $r->K_FILIAL;
                    $arr['nome'][] = $r->FAMILIA.")".$r->NOME;
                }
            }elseif($grupo == "grupo" ){
                if(strlen($r->FAMILIA) == 5){
                    $arr['handle'][] = $r->HANDLE;
                    $arr['nivelsuperior'][] = $r->NIVELSUPERIOR;
                    $arr['familia'][] = $r->FAMILIA;
                    $arr['k_filial'][] = $r->K_FILIAL;
                    $arr['nome'][] = $r->FAMILIA.")".$r->NOME;
                }
            }elseif($grupo == "subgrupo" ){
                if(strlen($r->FAMILIA) >= 6){
                    $arr['handle'][] = $r->HANDLE;
                    $arr['nivelsuperior'][] = $r->NIVELSUPERIOR;
                    $arr['familia'][] = $r->FAMILIA;
                    $arr['k_filial'][] = $r->K_FILIAL;
                    $arr['nome'][] = $r->FAMILIA.")".$r->NOME;
                }
            }
        }
        return $arr;
    }


    public static function getMaterial($mateiral = 0, $lista_material = false)
    {
        $array_base = array(
            "Matéria prima", "Prod. manufaturado", "Serviços", "Insumos",
            "Vidro comum", "Vidro temperado", "Vidro laminado", "Vidro resinado"
        );

        if ($lista_material){
            return $array_base;
        }

        return $array_base[$mateiral];
    }

    public static function getTabPrecos($precos = 0, $lista_precos = false)
    {
        $array_base = array(
            "PDV", "E-Commerce", "Venda Externa", "Venda Assistida",
            "Atacado", "Varejo", "Padrão"
        );

        if ($lista_precos){
            return $array_base;
        }

        return $array_base[$precos];
    }

    public static function getTipoOperacao($tipo){
        global $conexao;
        $sql = "select * from K_TIPOOPERACAO WHERE TIPO =:tipo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":tipo", $tipo);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach ($f as $r){
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->CODIGO.") ".$r->NOME;
        }

        return $arr;
    }

    public static function getUnidade(){
        global $conexao;
        $sql = "select * from CM_UNIDADESMEDIDA";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach($f as $r){
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->ABREVIATURA.") ".$r->NOME;
            $arr["abreviatura"][] = $r->ABREVIATURA;
            $arr["codigoin68"][] = $r->CODIGOIN68;
        }

        return $arr;
    }

    public static function getModelo(){
        global $conexao;
        $sql = "select * from K_MODELO";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach($f as $r){
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
            $arr["abreviatura"][] = $r->ABREVIATURA;
            $arr["codigoin68"][] = $r->CODIGOIN68;
        }

        return $arr;
    }

    public static function getFabricante(){
        global $conexao;
        $sql = "select * from K_FABRICANTE;";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach($f as $r){
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
        }
        return $arr;
    }

    public static function getNcm(){
        global $conexao;
        $sql = "select * from TR_TIPIS;";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach($f as $r){
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->CODIGONBM.") ".$r->NOME;
        }
        return $arr;
    }

    public function cadastra() {
        global $conexao;

        // gera handle
        $this->handle = newHandle("PD_PRODUTOS", $conexao);

        // insere produto
        $sql = "INSERT INTO PD_PRODUTOS (HANDLE, K_KFILIAL, CODIGO, NOME)
				VALUES (:handle, :filial, :codigo, :nome)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":filial", __FILIAL__);
        $stmt->bindValue(":codigo", $this->codigo);
        $stmt->bindValue(":nome", $this->nome);
        $stmt->execute();

        retornoPadrao($stmt, "Produto cadastrado com sucesso.",	"Problema no cadastro do produto");

        // fazendo cadastro em duas transações porque esse insert tava muito chato de manter!
        $this->atualiza();
    }


    public function atualiza() {

        global $conexao;

        // confere reserva de estoque/controle de saldo
        if($this->reserva_estoque == "S" && $this->controla_saldo == "N") {
            mensagem("Produto não pode reservar estoque sem controlar saldo", MSG_ERRO);
            finaliza();
        }

        if($this->controla_saldo == "S" && $this->reservado < 0)	$this->reservado = 0;
        if($this->controla_saldo == "N" && $this->reservado >= 0)	$this->reservado = -1;

        // converte MVA de float para int
        $this->margem_valor_agregado = intval($this->margem_valor_agregado * 1000);

        // gera código de barras novo se for vazio ou muito curto
        if(empty($this->codigo_barras) || strlen($this->codigo_barras) < 6) {
            $this->codigo_barras = "A".insereZeros(strtoupper(dechex($this->handle)), 5);

            mensagem("Gerando novo código de barras: {$this->codigo_barras}", MSG_AVISO);
        }

        // atualiza produto
        $sql = "UPDATE PD_PRODUTOS SET
				CODIGO = :codigo,
				CODIGOALTERNATIVO = :codigoalternativo,
				CODIGOREFERENCIA = :codigo,
				CODIGOBARRAS = :codigobarras,
				NUMEROSERIE = :numeroserie,
				NOME = :nome,
				/*DESCRICAO = :descricao,*/
				K_ENDERECO = :endereco,
				FAMILIA = :familia,
				MATERIAL = :material,
				K_CONTROLAESTOQUE = :controlaestoque,
				K_RESERVAESTOQUE = :reservaestoque,
				K_TERCEIRO = :terceiro,
				ATIVO = :ativo,
				LOTE = :lote,
				K_NCM = :ncm,
				/*K_FORNECEDORES = :fornecedores,*/
				MEDIDA_X = :medida_x,
				MEDIDA_Z = :medida_z,
				MEDIDA_Y = :medida_y,
				PESOVALOR = :peso,
				COR = :cor,
				HORIZONTEFIRME = :garantia,
				TAMANHO = :tamanho,
				/*CUSTOMAOOBRA = :perc_comissao,*/
				/*K_ESTRUTURADO = :estruturado,*/
				/*K_TIPOMOVENTRADA = :tipomoventrada,
				K_TIPOMOVSAIDA = :tipomovsaida,*/
				CUSTOCOMPRAS = :valorcusto,
				PRECOVENDA = :valorvenda,
				/*K_ALIQUOTAICMS = :credicms,
				K_ALIQUOTAIPI = :credipi,*/
				/*K_OTIMIZA = :otimiza,*/
				K_FRETE = :valorfrete,
				MARGEMLUCRO = :markup,
				UNIDADEMEDIDAVENDAS = :unidade,
				K_RESERVADO = :reservado,
				MARCA = :marca,
				CODIGOSERVICO = :codigoservico,
				COTACAOINTERNET = :lojavirtual,
				HABILITADOFRENTELOJA = :destaque,
				ALIQUOTASUBSTITUICAO = :margemvaloragregado,
				DATAINCLUSAO = :data,
				MODELO = :modelo,
				FABRICANTE = :fabricante
				WHERE HANDLE = :handle";

        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":codigo", $this->handle); // NULL , FALTA ADICIONAR O CAMPO DESCRICAO
//        $stmt->bindValue(":codigo", $this->codigo); // NULL , FALTA ADICIONAR O CAMPO DESCRICAO
        $stmt->bindValue(":codigoalternativo", $this->codigo_alternativo);
        // $stmt->bindValue(":codigoreferencia", $this->codigo_referencia);
        $stmt->bindValue(":codigobarras", $this->codigo_barras);
        $stmt->bindValue(":numeroserie", $this->numero_serie);
        $stmt->bindValue(":nome", $this->nome);
//        $stmt->bindValue(":descricao", $this->descricao); // FALTA ADICIONAR O CAMPO DESCRICAO
        $stmt->bindValue(":endereco", $this->endereco);
        $stmt->bindValue(":familia", validaVazio($this->familia));
        $stmt->bindValue(":material", $this->material);
        $stmt->bindValue(":controlaestoque", $this->controla_estoque);
        $stmt->bindValue(":reservaestoque", $this->reserva_estoque);
        $stmt->bindValue(":terceiro", $this->terceiro);
        $stmt->bindValue(":ativo", $this->ativo);
        $stmt->bindValue(":lote", $this->lote);
        $stmt->bindValue(":ncm", $this->ncm);
//        $stmt->bindValue(":fornecedores", $this->fornecedores); //NULL , Fornecedores ainda não incerido
        $stmt->bindValue(":medida_x", $this->medida_x);
        $stmt->bindValue(":medida_z", $this->medida_z);
        $stmt->bindValue(":medida_y", $this->medida_y);
        $stmt->bindValue(":peso", $this->peso);
        $stmt->bindValue(":cor", left($this->cor, 20));
        $stmt->bindValue(":garantia", $this->garantia);
        $stmt->bindValue(":tamanho", left($this->tamanho, 20));
//        $stmt->bindValue(":perc_comissao", $this->perc_comissao);
//        $stmt->bindValue(":estruturado", $this->estruturado);
//        $stmt->bindValue(":otimiza", $this->producao);  //NULL
//        $stmt->bindValue(":tipomoventrada", $this->tipo_movimento_entrada);
//        $stmt->bindValue(":tipomovsaida", $this->tipo_movimento_saida);
        $stmt->bindValue(":valorcusto", $this->valor_custo);
        $stmt->bindValue(":valorvenda", $this->valor_venda);
//        $stmt->bindValue(":credicms", $this->cred_icms); //NULL
//        $stmt->bindValue(":credipi", $this->cred_ipi); //NULL
        $stmt->bindValue(":valorfrete", $this->valor_frete);
        $stmt->bindValue(":markup", $this->valor_markup);
        $stmt->bindValue(":unidade", $this->unidade);
        $stmt->bindValue(":reservado", $this->reservado);
        $stmt->bindValue(":marca", $this->marca);
        $stmt->bindValue(":codigoservico", apenasNumeros($this->codigo_servico));
        $stmt->bindValue(":destaque", $this->destaque);
        $stmt->bindValue(":lojavirtual", $this->loja_virtual);
        $stmt->bindValue(":margemvaloragregado", $this->margem_valor_agregado);
        $stmt->bindValue(":data", $this->data_inclusao);
        $stmt->bindValue(":modelo", validaVazio($this->modelo));
        $stmt->bindValue(":fabricante", validaVazio($this->fabricante));


        $stmt->execute();
        retornoPadrao($stmt, "Produto atualizado com sucesso.",
            "Problema na atualização do produto. Por favor, confira se há campos em branco ou valores de tipo inconsistente.");

        // atualiza imagem
        if(temArquivo($this->imagem)) {
            $up = new Upload();
            $up->anexo = $this->imagem;
            $url = $up->getUrl();

            /* usando o campo "localização" porque é uma string de 250 caracteres sentada aí sem uso.
             * desculpe pela confusão, mas PD_PRODUTOS é um elefante. não precisamos aumentar mais ainda o tamanho do registro
             * (o resto da galeria precisa ser feita por uma tabela linkando produto->cor->imagem)
             */
            $sql = "UPDATE PD_PRODUTOS SET LOCALIZACAO = :imagem WHERE HANDLE = :handle";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":handle", $this->handle);
            $stmt->bindValue(":imagem", $url);
            $stmt->execute();

            retornoPadrao($stmt, "Imagem de exibição atualizada com sucesso.",
                "O arquivo foi salvo, mas a informação não pôde ser escrita no banco de dados. Certifique-se da integridade das tabelas.");
        }


    }

    public function atualizaCusto() {
        global $conexao;

        // puxa o produto e análise de movimento se precisar
        $gui = new ProdutoGUI();
        $gui->pesquisa["pesq_codigo"] = $this->codigo;
        $gui->fetch();
        $produto = $gui->itens[0];

        // é estruturado?
        if($produto->estruturado == "S") {
            mensagem("Compra de produto estruturado. Apenas compras das matérias primas atualizam preço", MSG_AVISO);
            return 0;
        }

        // puxa a porcentagem do markup atual
        $markup = $produto->markup / ($produto->valor_venda - $produto->markup);

        // calcula o novo preço
        $custo_base = $this->valor_custo;
        $icms = (($custo_base * $this->cred_icms) / 100);
        $ipi = (($custo_base * $this->cred_ipi) / 100);
        $valor_frete = $this->valor_frete;

        if(__USA_CUSTO_MEDIO__) {
            // puxa os valores médios de solicitações fechadas
            $sql = "SELECT AVG(VALOR) AS VALOR, AVG(ICMS) AS ICMS, AVG(IPI) AS IPI, AVG(VALORFRETE) AS VALORFRETE
					FROM K_FN_SOLICITACAOCOMPRA
					WHERE STATUS = 2
					AND PRODUTO = :produto
					AND ".filtraFilial("FILIAL", "Compras", false);
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":produto", $this->codigo);
            $stmt->execute();

            $f = $stmt->fetch(\PDO::FETCH_OBJ);

            if($f->VALOR > 0) {
                $custo_base = $f->VALOR;
                $icms = (($custo_base * $f->ICMS) / 100);
                $ipi = (($custo_base * $f->IPI) / 100);
                $valor_frete = $f->VALORFRETE;
            }
            else {
                mensagem("Produto não possui histórico de compras! Assumindo custo da última compra.", MSG_AVISO);
                // não atualiza a tabela de preços ou assume custo atual?
                // return 0;
            }
        }

        $preco_fob = $custo_base - $icms - $ipi + $valor_frete;
        $valor_markup = $preco_fob * $markup;
        $preco_venda = $preco_fob + $valor_markup;

        mensagem("Markup: ".$markup, MSG_DEBUG);
        mensagem("Custo base: ".$custo_base, MSG_DEBUG);
        mensagem("ICMS: ".$icms, MSG_DEBUG);
        mensagem("IPI: ".$ipi, MSG_DEBUG);
        mensagem("Frete: ".$valor_frete, MSG_DEBUG);
        mensagem("Preço FOB: ".$preco_fob, MSG_DEBUG);
        mensagem("Valor markup: ".$valor_markup, MSG_DEBUG);
        mensagem("Preço venda: ".$preco_venda, MSG_DEBUG);

        // atualiza produto
        $sql = "UPDATE PD_PRODUTOS SET
				CUSTOCOMPRAS = :valorcusto,
				PRECOVENDA = :valorvenda,
				K_ALIQUOTAICMS = :credicms,
				K_ALIQUOTAIPI = :credipi,
				K_FRETE = :valorfrete,
				MARGEMLUCRO = :markup
				WHERE CODIGO = :codigo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $this->codigo);
        $stmt->bindValue(":valorcusto", $custo_base);
        $stmt->bindValue(":valorvenda", $preco_venda);
        $stmt->bindValue(":credicms", $icms);
        $stmt->bindValue(":credipi", $ipi);
        $stmt->bindValue(":valorfrete", $valor_frete);
        $stmt->bindValue(":markup", $valor_markup);
        $stmt->execute();

        retornoPadrao($stmt, "Custo do produto #".$this->codigo." atualizado (novo preço: $".formataValor($preco_venda).")",
            "Erro atualizando custo do produto de acordo com última compra");

        // atualiza produto estruturado
        $sql = "UPDATE K_FN_PRODUTOESTRUTURADO SET UNITARIO = :preco WHERE FILHO = :codigo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $this->codigo);
        $stmt->bindValue(":preco", $preco_venda);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            mensagem("Entradas em tabela de produtos estruturados atualizadas.");

            // atualiza os produtos pais
            $sql = "SELECT PAI FROM K_FN_PRODUTOESTRUTURADO WHERE FILHO = :codigo";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":codigo", $this->codigo);
            $stmt->execute();

            $f = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach($f as $r) {
                $pe = new ProdutoEstruturadoGUI;
                $pe->pai = $r->PAI;
                $pe->fetch();

                // soma valores dos filhos
                $custo_base = 0;

                foreach($pe->itens as $item) {
                    $custo_base += $item->valor_total;

                    mensagem("Custo filho #{$item->cod_filho}: {$item->valor_total}", MSG_DEBUG);
                }

                // multiplica pelo markup do pai
                $valor_markup = $custo_base * $item->markup;
                $preco_venda = $custo_base + $valor_markup;

                if(__GLASS_DEBUG__) {
                    mensagem("Markup #{$item->cod_pai}: {$item->markup}", MSG_DEBUG);
                    mensagem("Preço venda #{$item->cod_pai}: {$preco_venda}", MSG_DEBUG);
                }

                // atualiza produto
                $sql = "UPDATE PD_PRODUTOS SET
						CUSTOCOMPRAS = :valorcusto,
						PRECOVENDA = :valorvenda,
						MARGEMLUCRO = :markup
						WHERE CODIGO = :codigo";
                $stmt = $conexao->prepare($sql);
                $stmt->bindValue(":codigo", $r->PAI);
                $stmt->bindValue(":valorcusto", $custo_base);
                $stmt->bindValue(":valorvenda", $preco_venda);
                $stmt->bindValue(":markup", $valor_markup);
                $stmt->execute();

                retornoPadrao($stmt, "Custo do produto #".$r->PAI." atualizado (novo preço: $".formataValor($preco_venda).")",
                    "Erro atualizando custo do produto de acordo com última compra (PE)");
            }
        }
        else {
            mensagem("Sem entradas na tabela de produtos estruturados.");
        }
    }

}
