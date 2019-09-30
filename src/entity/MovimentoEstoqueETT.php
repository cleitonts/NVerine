<?php


namespace src\entity;


use src\services\Transact\ExtPDO as PDO;

class MovimentoEstoqueETT extends ObjectETT
{
    // origens
    const ME_COMPRAS = 1;
    const ME_VENDAS = 2;
    const ME_ESTOQUE = 3;		// lançamento manual
    const ME_DEVOLUCOES = 4;
    const ME_CANCELAMENTOS = 5;	// financeiro?
    const ME_REQUISICAO = 6;
    const ME_VIRADA = 7;
    const ME_API = 8;

// almoxarifados padrão
    const ME_SOLICITACAO = 2;
    const ME_RESERVA = 3;

// propriedades
    public $codigo;				// prefixo de controle de movimento - LE, LS, TR
    // na gui, é concatenado com o handle para formar o número do movimento
    public $produto; 			// código
    public $endereco;			// handle
    public $lote;				// numérico (controlar por uma tabela - opcional?)
    public $quantidade;
    public $area; 				// para a requisição
    public $valor_unitario;		// opcional, para movimentos valorados
    public $data;				// se não for passado, assume hoje
    public $notas;
    public $cancelado;			// s/n (opcional, default n)
    public $origem;
    public $requisicao;
    public $numero_orcamento;	// aqui é diferente do título. ele guarda o número do orçamento de compra OU venda
    // depois é tratado pela origem para puxar o numero_doc correspondente

    public $custo_compra;		// ** apenas para a movimentação de produto estruturado vindo da venda! **
    // o único campo de valor no cadastro é $valor_unitario, que representa um
    // custo de compra ou venda dependendo do tipo de movimento.

    public $dry_run;			// para simular a movimentação de estoque sem cadastro


// para gui
    public $cod_tipo;			// infere a partir do prefixo. não precisa seguir qualquer padrão
    public $cod_produto;
    public $cod_origem;
    public $valor_total;
    public $quantidade_sinal;
    public $valor_unitario_sinal;
    public $valor_total_sinal;
    public $unidade;			// puxa do produto
    public $grupo_produto;		// ||
    public $familia_produto;	// ||
    public $tipo;				// entrada ou saída
    public $almoxarifado;
    public $rua;
    public $numero_doc;
    public $numero_nf;
    public $usuario;
    public $pessoa;				// puxa do título de compra/venda
    public $cod_pessoa;			// ||
    public $filial;
    public $cfop;

    // construtor
    public function __construct() {
        $this->dry_run = false;
    }

    // -----------------------------------------------------------------------------------------------------------------------
    // métodos privados
    private function cadastra() {
        global $conexao;

        // dry run?
        if($this->dry_run) {
            mensagem("Dry run do estoque! Movimentação não será cadastrada.", MSG_AVISO);
            return;
        }

        // valida quantidade
        if($this->quantidade == 0) {
            mensagem("Movimentação vazia de estoque!", MSG_AVISO);
            return;
        }

        // valida lote
        if(!empty($this->lote) && !is_numeric($this->lote)) {
            mensagem("Número de lote não é numérico", MSG_ERRO);
            finaliza();
        }

        // possui data? / converte
        if(!empty($this->data))
            $data = converteData0($this->data) . " 12:00:00";
        else
            $data = agora();

        // possui endereço?
        if(empty($this->endereco)) $this->endereco = $this->getEndereco();

        // gera handle
        $this->handle = newHandle("K_FN_PRODUTOENDERECO", $conexao);

        // insere
        $sql = "INSERT INTO K_FN_PRODUTOENDERECO
                (HANDLE, CODIGO, PRODUTO, ENDERECO, QUANTIDADE, QUANTIDADEFLOAT, USUARIO, FILIAL, DATA, NOTAS, CANCELADO,
                ORIGEM, NUMEROORCAMENTO, VALOR, LOTE, REQUISICAO)
                VALUES
                (:handle, :codigo, :produto, :endereco, :quantidade, :quantidadefloat, :usuario, :filial, :data, :notas, :cancelado,
                :origem, :numeroorcamento, :valorunitario, :lote, :requisicao)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":codigo", $this->codigo);
        $stmt->bindValue(":produto", $this->produto);
        $stmt->bindValue(":endereco", $this->endereco);
        $stmt->bindValue(":quantidade", intval($this->quantidade));
        $stmt->bindValue(":quantidadefloat", $this->quantidade);
        $stmt->bindValue(":usuario", $_SESSION["ID"]);
        $stmt->bindValue(":filial", __FILIAL__);
        $stmt->bindValue(":data", $data);
        $stmt->bindValue(":notas", $this->notas);
        $stmt->bindValue(":cancelado", "N");
        $stmt->bindValue(":origem", $this->origem);
        $stmt->bindValue(":requisicao", $this->requisicao);
        $stmt->bindValue(":numeroorcamento", $this->numero_orcamento);
        $stmt->bindValue(":valorunitario", $this->valor_unitario);
        $stmt->bindValue(":lote", $this->lote);
        $stmt->execute();

        retornoPadrao($stmt, "Movimentação de estoque inserida com sucesso.", "Não foi possível inserir a movimentação de estoque");
    }

    private function getEndereco() {
        global $conexao;

        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // aqui talvez precise de uma tabela para mapear um só produto para várias filiais
        // ainda assim, um produto não poderá ter dois endereços dentro de uma mesma filial
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $sql = "SELECT K_ENDERECO AS ENDERECO FROM PD_PRODUTOS WHERE CODIGO = :produto";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":produto", $this->produto);
        $stmt->execute();

        $f = $stmt->fetch(PDO::FETCH_OBJ);

        if(empty($f->ENDERECO)) {
            mensagem("Produto #".$this->produto." não possui endereço definido!", MSG_ERRO);
            finaliza();
        }

        return $f->ENDERECO;
    }

    // -----------------------------------------------------------------------------------------------------------------------
    // métodos públicos
    public function movimenta() {
        include_once("class/Produto.php");
        global $conexao;

        // puxa produto
        $gui = new ProdutoGUI();
        $gui->pesquisa["pesq_codigo"] = $this->produto;
        $gui->fetch();
        $produto = $gui->itens[0];

        // descobre se produto controla estoque
        if($produto->controla_estoque == "N") {
            mensagem("Produto #{$this->produto} não movimenta estoque. Carry on.");
            return 0;
        }

        // descobre se produto controla lote
        if($produto->lote == "S" && empty($this->lote)) {
            mensagem("Produto #{$this->produto}: obrigatório informar número de lote!", MSG_ERRO);
            finaliza();
        }

        // descobre se produto é estruturado
        if($produto->estruturado == "S") {
            mensagem("Produto #{$this->produto} é estruturado. Movimentando filhos...");

            // puxa estrutura
            $produto->estrutura->fetch();

            if(!empty($produto->estrutura->itens)) {
                foreach($produto->estrutura->itens as $r) {
                    $movimento = new MovimentoEstoqueGUI();
                    $movimento->codigo = $this->codigo;
                    $movimento->produto = $r->cod_filho;
                    $movimento->valor_unitario = $r->valor_unitario;
                    // $movimento->custo_compra = $r->custo_compra;
                    $movimento->endereco  = $this->endereco;
                    $movimento->lote = $this->lote; // isso é verdade pro produto estruturado?
                    $movimento->quantidade = $this->quantidade * $r->quantidade; // !!
                    $movimento->origem = $this->origem;
                    $movimento->numero_orcamento = $this->numero_orcamento;
                    $movimento->notas = $this->notas." (mov. PE #".$this->produto.")";
                    $movimento->dry_run = $this->dry_run;
                    $movimento->movimenta();
                }
            }
            else {
                mensagem("Produto foi marcado como estruturado, mas não possui filhos cadastrados.", MSG_AVISO);
            }

            // movimento vindo de vendas?
            if($this->origem == $this::ME_VENDAS && !$this->dry_run) {
                mensagem("Gerando movimentação de estoque artificial do PE vendido!");

                $entrada = new MovimentoEstoqueGUI();
                $entrada->produto = $this->produto;
                $entrada->valor_unitario = $this->custo_compra; // diferente!
                $entrada->endereco = $this->endereco;
                $entrada->lote = $this->lote; // isso é necessário?
                $entrada->quantidade = $this->quantidade * -1; // negativo do negativo?
                $entrada->notas = $this->notas." (mov. PE original -- venda #".$this->numero_orcamento.")";
                $entrada->origem = $this::ME_COMPRAS;
                // não tem orçamento de compra!
                $entrada->cadastra();

                $saida = new MovimentoEstoqueGUI();
                $saida->produto = $this->produto;
                $saida->valor_unitario = $this->valor_unitario;
                $saida->endereco = $this->endereco;
                $saida->lote = $this->lote; // idem
                $saida->quantidade = $this->quantidade;
                $saida->notas = $this->notas." (mov. PE original -- venda #{$this->numero_orcamento})";
                $saida->origem = $this::ME_VENDAS;
                $saida->numero_orcamento = $this->numero_orcamento;
                $saida->cadastra();
            }
        }
        else {
            // possui endereço?
            if(empty($this->endereco)) $this->endereco = $this->getEndereco();

            // recupera o estoque disponível do produto no endereço
            $sql = "SELECT SUM(QUANTIDADEFLOAT) AS TOTAL
                    FROM 	K_FN_PRODUTOENDERECO
                    WHERE 	PRODUTO = :produto 
                    AND 	ENDERECO = :endereco";
            // filtrar pela filial
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":produto", $this->produto);
            $stmt->bindValue(":endereco", $this->endereco);
            $stmt->execute();

            $f = $stmt->fetchObject();

            // calcula disponibilidade
            if($produto->reservado < 0) {
                mensagem("Valor de reserva negativo. Produto não calcula saldo de estoque");
            }
            else {
                $disponivel = $f->TOTAL;
                if($produto->reserva_estoque == "S" && $this->quantidade < 0)
                    $disponivel -= $produto->reservado;

                // detalhamento
                mensagem("----");
                mensagem("Produto #{$this->produto} | Endereço #{$this->endereco}");
                mensagem("Estoque disponível: {$disponivel} ({$produto->reservado} mínimo)");
                mensagem("Movimentação: {$this->quantidade}");

                // bloqueia?
                $disponivel += $this->quantidade;
                if($disponivel < 0) {
                    mensagem("Indisponibilidade de estoque do produto #{$this->produto}", MSG_ERRO);
                    finaliza();
                }
            }

            // cadastra o movimento
            $this->cadastra();
        }
    }

    public function cancela() {
        global $conexao;

        // possui origem?
        if(empty($this->origem)) $this->origem = $this::ME_ESTOQUE;

        // puxa movimento
        $gui = new MovimentoEstoqueGUI();
        $gui->pesquisa["pesq_codigo"] = $this->handle;
        $gui->fetch();
        $movimento = $gui->itens[0];

        // cria estorno
        mensagem("Lançando estorno de estoque...");

        $estorno = new MovimentoEstoqueGUI();
        $estorno->codigo = "CA";
        $estorno->produto = $movimento->cod_produto;
        $estorno->valor_unitario = $movimento->valor_unitario;
        $estorno->endereco = $movimento->cod_endereco;
        $estorno->lote = $movimento->lote;
        $estorno->requisicao = $movimento->requisicao;
        $estorno->quantidade = $movimento->quantidade_sinal * -1;
        $estorno->notas = "Cancelamento/estorno do movimento ".$movimento->codigo;
        $estorno->origem = $this->origem;
        $estorno->cadastra();

        // marca movimento como cancelado
        $sql = "UPDATE K_FN_PRODUTOENDERECO SET CANCELADO = 'S' WHERE HANDLE = :handle AND CANCELADO = 'N'";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->execute();

        retornoPadrao($stmt, "Movimento foi marcado como cancelado.", "Erro cancelando movimento de estoque -- este movimento já foi cancelado?");
    }

    public function getSaldoEstoque($endereco = null, $data_final = null) {
        global $conexao;

        $where = "WHERE A.PRODUTO = :produto \n";

        if(!empty($this->lote)){
            $where .= "AND A.LOTE = {$this->lote} \n";
        }

        // monta query de pesquisa
        $sql = "SELECT SUM(A.QUANTIDADEFLOAT) AS TOTAL, AVG(A.VALOR) AS PRECOMEDIO
                FROM
                (				
                    (
                        K_FN_PRODUTOENDERECO A LEFT JOIN K_FN_ENDERECO B ON A.ENDERECO = B.HANDLE)
                    LEFT JOIN K_FN_ALMOXARIFADO C ON B.ALMOXARIFADO = C.HANDLE)
                LEFT JOIN K_FN_FILIAL D ON C.FILIAL = D.HANDLE
                {$where}
                AND ".filtraFilial("D.HANDLE", "Estoque");

        if(isset($data_final)) 	$sql .= "AND A.DATA < :datafinal\n";
        if(isset($endereco))	$sql .= "AND A.ENDERECO = :endereco\n";

        // busca dados
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":produto", $this->produto);
        if(isset($data_final)) 	$stmt->bindValue(":datafinal", $data_final);
        if(isset($endereco))	$stmt->bindValue(":endereco", $endereco);
        $stmt->execute();

        $f = $stmt->fetch(PDO::FETCH_OBJ);

        // trata valores
        $total = $f->TOTAL;
        if(empty($total)) $total = "Indisponível";

        $preco_medio = formataValor($f->PRECOMEDIO);
        $valor_estoque = formataValor($f->PRECOMEDIO * $f->TOTAL);

        return array($total, $preco_medio, $valor_estoque);
    }

    public function getMargemContribuicao() {
        global $conexao;

        // esse relatório não define um produto, ele agrupa todos os produtos no período especificado
        $where = "AND ".filtraFilial("ME.FILIAL", "Estoque");
        if(!empty($_REQUEST["pesq_data_inicial"]) && !empty($_REQUEST["pesq_data_final"])) {
            $where .= "AND ME.DATA >= :datainicial AND ME.DATA <= :datafinal\n";
        }

        // ...mas pode filtrar por família
        $familia = "";
        if(!empty($_REQUEST["pesq_familia"])) {
            $familia .= "AND (FAM.HANDLE = :familia OR GRU.HANDLE = :familia)\n";
        }

        // monta query de pesquisa
        $sql = "SELECT P.CODIGO, P.NOME, UM.ABREVIATURA AS UNIDADE,
                (
                    SELECT AVG(ME.VALOR)
                    FROM K_FN_PRODUTOENDERECO ME
                    WHERE ME.PRODUTO = P.CODIGO
                    AND ORIGEM = :compras
                    {$where}
                ) CUSTOCOMPRA,
                (
                    SELECT AVG(ME.VALOR)
                    FROM K_FN_PRODUTOENDERECO ME
                    WHERE ME.PRODUTO = P.CODIGO
                    AND ORIGEM = :vendas
                    {$where}
                ) CUSTOVENDA,
                (
                    SELECT SUM(ME.QUANTIDADEFLOAT)
                    FROM K_FN_PRODUTOENDERECO ME
                    WHERE ME.PRODUTO = P.CODIGO
                    AND ORIGEM = :compras
                    {$where}
                ) TOTALCOMPRAS,
                (
                    SELECT SUM(ME.QUANTIDADEFLOAT)
                    FROM K_FN_PRODUTOENDERECO ME
                    WHERE ME.PRODUTO = P.CODIGO
                    AND ORIGEM = :vendas
                    {$where}
                ) TOTALVENDAS
                FROM PD_PRODUTOS P LEFT JOIN (
                    PD_FAMILIASPRODUTOS GRU LEFT JOIN PD_FAMILIASPRODUTOS FAM
                    ON GRU.NIVELSUPERIOR = FAM.HANDLE
                ) ON P.FAMILIA = GRU.HANDLE
                LEFT JOIN CM_UNIDADESMEDIDA UM ON P.UNIDADEMEDIDAVENDAS = UM.HANDLE
                WHERE ".filtraFilial("K_KFILIAL", "Produto")."
                {$familia}";

        // busca dados
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":compras", self::ME_COMPRAS);
        $stmt->bindValue(":vendas", self::ME_VENDAS);

        if(!empty($_REQUEST["pesq_data_inicial"]) && !empty($_REQUEST["pesq_data_final"])) {
            $stmt->bindValue(":datainicial", converteData0($_REQUEST["pesq_data_inicial"])." 00:00:00");
            $stmt->bindValue(":datafinal", converteData0($_REQUEST["pesq_data_final"])." 23:59:59");
        }
        if(!empty($_REQUEST["pesq_familia"])) {
            $stmt->bindValue(":familia", $_REQUEST["pesq_familia"]);
        }

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $f;
    }

    public function getNomeOrigem($i) {
        switch($i) {
            case self::ME_COMPRAS: 			return "Compras";
            case self::ME_VENDAS:			return "Vendas";
            case self::ME_ESTOQUE:			return "Estoque (self)";
            case self::ME_DEVOLUCOES:		return "Devoluções";
            case self::ME_CANCELAMENTOS:	return "Cancelamentos";
            case self::ME_REQUISICAO:		return "Requisições";
            case self::ME_VIRADA:			return "Virada de Saldo";
            case self::ME_API:				return "Loja virtual";
            default:						return "Não especificada";
        }
    }

    //insere o cabeçalho da requisição
    public function geraRequisicao(){
        global $conexao;

        $sql = "INSERT INTO K_FN_REQUISICAO (HANDLE, AREA, DATA, NOTAS, USUARIO, ALMOXARIFADOORIGEM, BAIXADO) VALUES 
                (:handle, :area, :data, :notas, :usuario, :endereco, :baixado)";

        // busca dados
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":area", $this->area);
        $stmt->bindValue(":data", $this->data);
        $stmt->bindValue(":notas", $this->notas);
        $stmt->bindValue(":usuario", $_SESSION["ID"]);
        $stmt->bindValue(":endereco", $this->endereco);
        $stmt->bindValue(":baixado", "N");
        $stmt->execute();

        retornoPadrao($stmt, "Cabeçalho da requisição inserida com sucesso.", "Problema inserindo cabeçalho da requisição");

    }
}