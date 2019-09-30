<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 27/09/2019
 * Time: 12:45
 */

namespace src\entity;

use src\creator\widget\Tools;
use src\services\Transact\ExtPDO as PDO;

class CadastroETT extends ObjectETT
{
    // esta lista define as tabelas permitidas de serem acessadas/alteradas por aqui
    const TABELAS_VALIDAS =
        "'K_FN_AREA', 'K_FN_ALMOXARIFADO', 'K_FN_CENTROCUSTO',
		'K_FN_CST_ORIGEM', 'K_FN_CST_TRIBUTACAO', 'K_FN_TARIFAS', 'K_PD_ALCADAS',
		'K_FN_ENDERECO', 'K_FN_FILIAL', 'K_FN_USUARIOFILIAL',
		'K_PD_USUARIOS', 'K_FN_CFOP', 'TR_TIPIS', 'CM_UNIDADESMEDIDA',
		'K_FN_GRUPOUSUARIO', 'FN_FORMASPAGAMENTO',
		'K_CRM_SEGMENTOS', 'K_CRM_FONTES', 'K_CRM_FONTES', 'K_CRM_CAMPANHAS',
		'K_CRM_MOTIVOSPERDA',  'K_CRM_ETAPAS', 'K_CRM_PROJETOS', 'K_CRM_PROCESSOS',
		'K_CRM_PROCESSOSTAREFAS', 'K_FN_PESSOACLASSE', 'K_FN_TIPOVINCULO',
		'K_HISTORICO', 'K_CORPECAS', 'K_LISTAPRECO', 'K_TIPODOCUMENTO',
		'K_DISCIPLINA', 'K_AREACONHECIMENTO',
		'K_PREFIXO', 'K_REGIAO', 'K_PRODUCAOETAPAS', 'K_PRODUCAOTERMINAL',
		'K_ALUNOSITUACAO', 'K_TIPOOCORRENCIA', 'K_USUARIOSNIVEIS',
		'K_MODELO', 'K_FABRICANTE', 'K_SERIE'
		";

    /* propriedades públicas
     */
    public $nome_tabela;
    public $campos;                // array com os nomes de campo
    public $lista_campos;        // lista para interface (sem a propriedade COLUMN_NAME)
    public $mensagem_retorno;    // passar para mensagem() ou mensagemErro() no contexto de origem
    public $modulo_referencia;    // para alimentar $__MODULO__

    /* instância deve passar o nome da tabela para extrair os campos
     */
    public function __construct($nome_tabela)
    {
        global $conexao;

        $this->nome_tabela = $nome_tabela;

        // retorno vazio = ok
        $this->mensagem_retorno = "";

        // realiza validações
        if (empty($nome_tabela)) {
            $this->mensagem_retorno = "Tabela não foi informada!";
            return;
        }

        /* removendo o bloqueio (com return) para uso geral.
         * não deve ser um problema, fazer o tratamento de acesso pelo mensagem_retorno
         */
        if (strpos(self::TABELAS_VALIDAS, "'{$nome_tabela}'") === false) { // veja no manual do php por que '===' é necessário
            $this->mensagem_retorno = "Acesso negado à tabela {$nome_tabela}";
            // return;
        }

        // definição do módulo de referência
        $this->modulo_referencia = "Cadastros";

        if (in_array($nome_tabela, array("K_FN_USUARIOCAIXA", "K_FN_PERMISSOES", "K_PD_ALCADAS", "K_FN_USUARIOFILIAL", "K_PD_USUARIOS", "K_FN_GRUPOUSUARIO")))
            $this->modulo_referencia = "Administração";

        elseif (in_array($nome_tabela, array("K_FN_CST_ORIGEM", "K_FN_CST_TRIBUTACAO", "K_FN_CFOP", "TR_TIPIS", "K_FN_TARIFAS")))
            $this->modulo_referencia = "Fiscal";

        elseif ($nome_tabela == "K_FN_ALMOXARIFADO" || $nome_tabela == "K_FN_ENDERECO")
            $this->modulo_referencia = "Estoque";

        elseif ($nome_tabela == "K_HISTORICO" || $nome_tabela == "K_LISTAPRECO")
            $this->modulo_referencia = "Faturamento";

        elseif ($nome_tabela == "K_FN_TIPOVINCULO" || $nome_tabela == "K_DISCIPLINAS")
            $this->modulo_referencia = "Educacional";

        elseif ($nome_tabela == "K_FN_CENTROCUSTO" || $nome_tabela == "K_TIPODOCUMENTO")
            $this->modulo_referencia = "Contábil";

        elseif ($nome_tabela == "K_DISCIPLINA" || $nome_tabela == "K_AREACONHECIMENTO" || $nome_tabela == "K_TIPOOCORRENCIA")
            $this->modulo_referencia = "Educacional";

        elseif (strpos($nome_tabela, "K_CRM_") !== false && $nome_tabela != "K_CRM_SEGMENTOS")
            $this->modulo_referencia = "CRM";

        // campos pré-definidos
        $this->campos = array();
        $this->lista_campos = array("");

        /* nem todas essas tabelas precisam ter campos pré-definidos aqui.
         * não use isso como uma forma de ordenar os campos --
         * ordenação não importa! (na maioria dos casos)
         */
        if ($nome_tabela == "TR_TIPIS") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "CODIGONBM";
            $this->campos[] = "CODIGOEX";
            $this->campos[] = "CODPRODUTO";
            $this->campos[] = "NOME";
            $this->campos[] = "ALIQUOTA";
        } elseif ($nome_tabela == "CM_UNIDADESMEDIDA") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "ABREVIATURA";
        } elseif ($nome_tabela == "K_FN_CFOP") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "CODIGO";
            $this->campos[] = "NOME";
            $this->campos[] = "DESCRICAO";
        } elseif ($nome_tabela == "K_PD_ALCADAS") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "COMPARTILHADO";
        } elseif ($nome_tabela == "K_FN_FILIAL") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "RAZAOSOCIAL";
            $this->campos[] = "EMPRESA";
            $this->campos[] = "REGIAO";
            $this->campos[] = "CNPJ";
            $this->campos[] = "CRT";
            $this->campos[] = "CNAE";
            $this->campos[] = "INSCRICAOESTADUAL";
            $this->campos[] = "INSCRICAOMUNICIPAL";
            $this->campos[] = "ENDERECO";
            $this->campos[] = "COMPLEMENTO";
            $this->campos[] = "BAIRRO";
            $this->campos[] = "NUMERO";
            $this->campos[] = "CEP";
            $this->campos[] = "ESTADO";
            $this->campos[] = "CIDADE";
            $this->campos[] = "TELEFONE";
            $this->campos[] = "SEQUENCIANOTA";
            $this->campos[] = "TIMEZONE";
            $this->campos[] = "ID_CSC";
            $this->campos[] = "CSC";
            $this->campos[] = "CODIGO";
            $this->campos[] = "TEXTOPADRAONFE";
            $this->campos[] = "TABELA_ME";
            $this->campos[] = "JUROS_BOLETO";
            $this->campos[] = "MULTA_BOLETO";
            $this->campos[] = "PROTESTO";
            $this->campos[] = "LOGO_CONTRATO";
        } elseif ($nome_tabela == "K_PD_USUARIOS") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "APELIDO";
            $this->campos[] = "SENHA";
            $this->campos[] = "CLIENTE";
            $this->campos[] = "EMAIL";
            $this->campos[] = "GRUPO";
            $this->campos[] = "REGIAO";
            $this->campos[] = "NIVEL";
            $this->campos[] = "VENCIMENTO";
            $this->campos[] = "COMISSAOVENDA";
        } elseif ($nome_tabela == "FN_FORMASPAGAMENTO") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "CODIGO";
            $this->campos[] = "K_DOCLIQUIDEZ";
        } elseif ($nome_tabela == "K_FN_TARIFAS") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "ESTADO";
            $this->campos[] = "ALIQUOTA";
        } elseif ($nome_tabela == "K_LISTAPRECO") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "INDICE";
            $this->campos[] = "PRODUTO";
            $this->campos[] = "VALOR";
            $this->campos[] = "PERCDESCONTO";
            $this->campos[] = "ATIVO";
        } elseif ($nome_tabela == "K_TIPODOCUMENTO") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "NOME";
            $this->campos[] = "SIGLA";
            $this->campos[] = "CONTAORIGEM";
        } elseif ($nome_tabela == "K_FN_CENTROCUSTO") {
            $this->campos[] = "HANDLE";
            $this->campos[] = "CODIGO";
            $this->campos[] = "NOME";
            $this->campos[] = "FILIAL";
        }


        // recupera a listagem de campos se não foi pré-definida
        if (empty($this->campos)) {
            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_NAME = :tabela
					AND COLUMN_NAME <> 'Z_GRUPO'"; // legado; não queremos este campo

            // no mysql, precisamos filtrar a base atual porque todas vão misturadas
            if (__DB_DRIVER__ == "mysql") $sql .= " AND TABLE_SCHEMA = '" . __DB_NAME__ . "'";

            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":tabela", $nome_tabela);
            $stmt->execute();

            $this->campos = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        if (count($this->campos) <= 0) {
            $this->mensagem_retorno = "Não foi possível recuperar as informações de campos da tabela";
            return;
        }

        // lista para interface
        foreach ($this->campos as $campo) {
            $this->lista_campos[] = $campo;
        }

    }

    public static function cadastroLoader()
    {
        $retorno = array();
        global $__PAGINA__;
        global $__MODULO__;

        // alguns aliases
        $retorno["table_enc"] = $_REQUEST["tabela"];
        $retorno["table"] = decrypt($retorno["table_enc"]);
        $retorno["nome"] = $_REQUEST["tn"];
        $handle = $_REQUEST["i"];
        $retorno["retorno"] = "cadastro&tn=" . $retorno['nome'] . "&tabela=" . $_REQUEST["tabela"];

        // fallback
        $__MODULO__ = "Cadastros";
        $__PAGINA__ = !empty($nome) ? $nome : "Cadastro";

        // recupera dados da classe genérica
        $cadastro = new CadastroGUI($retorno["table"]);

        // tratamento de erros da instância de tabela
        if (!empty($cadastro->tabela->mensagem_retorno)) {
            return Tools::returnError($cadastro->tabela->mensagem_retorno);
        }

        if (!empty($handle)) $cadastro->pesquisa["pesq_num"] = $handle;
        $cadastro->fetch();

        // ajusta sitemap
        $__MODULO__ = $cadastro->tabela->modulo_referencia;

        // gera uma senha aleatória para o cadastro de usuários
        $retorno["senha"] = left(hash('sha512', rand()), 8);
        $retorno["senha_hash"] = safercrypt($retorno["senha"]);

        return $retorno;
    }
}