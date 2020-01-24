START TRANSACTION;

/* ================================================================================================================== 
    TabelasETT globais, usadas em mais de um módulo
*/

CREATE TABLE METADADOS_TABELAS(
	HANDLE int PRIMARY KEY NOT NULL,
    NOME_TABELA varchar(50) NOT NULL,
    USUARIO int NOT NULL,
    COLUNA int NOT NULL,
    POSICAO int
);

CREATE TABLE K_PREFIXO
(
    HANDLE int PRIMARY KEY NOT NULL,
    NUMERO int,
    NOME   varchar(50),
    FILIAL int
);

CREATE TABLE K_FN_PERMISSOES
(
    HANDLE   int PRIMARY KEY NOT NULL,
    Z_GRUPO  int,
    GRUPO    int,
    ALCADA   int,
    NIVEL    int
);

CREATE TABLE K_FN_FILIAL
(
    HANDLE             int PRIMARY KEY NOT NULL,
    Z_GRUPO            int,
    NOME               varchar(60),
    CODIGO             varchar(10),
    PAI                int,
    ESTADO             int,
    EMPRESA            int,
    CNPJ               varchar(250),
    ENDERECO           varchar(250),
    COMPLEMENTO        varchar(250),
    NUMERO             varchar(20),
    BAIRRO             varchar(250),
    CEP                varchar(11),
    CIDADE             varchar(250),
    LOGOTIPO           varchar(250),
    SMTPHOST           varchar(250),
    SMTPUSER           varchar(50),
    SMTPPASS           varchar(250),
    INSCRICAOESTADUAL  varchar(50),
    INSCRICAOMUNICIPAL varchar(50),
    PLANOCOMPRAS       int,
    PLANOVENDAS        int,
    CRT                varchar(2),
    CONTA              int,
    CNAE               varchar(15),
    TEXTOPADRAONFE     varchar(150),
    RAZAOSOCIAL        varchar(60),
    TELEFONE           varchar(20),
    SEQUENCIANOTA      int,
    TIMEZONE           varchar(6),
    ID_CSC             varchar(6),
    CSC                varchar(50),
    REGIAO             int,
    TABELA_ME          float(53),
    JUROS_BOLETO       varchar(100),
    MULTA_BOLETO       varchar(100),
    PROTESTO           varchar(100),
    LOGO_CONTRATO      varchar(100)
);

CREATE TABLE K_FN_GRUPOUSUARIO
(
    HANDLE        int PRIMARY KEY NOT NULL,
    Z_GRUPO       int,
    NOME          varchar(50),
    PAGINAINICIAL varchar(250)
);

CREATE TABLE K_REGIAO
(
    HANDLE int PRIMARY KEY NOT NULL,
    NOME   varchar(250)
);

CREATE TABLE K_GALERIA
(
    HANDLE     int PRIMARY KEY NOT NULL,
    PRODUTO    int,
    URL        varchar(250),
    LEGENDA    varchar(250),
    MODELO     int,
    ATIVO      char(1),
    ORDEM      int,
    TARGET     int,
    REFERENCIA int
);

CREATE TABLE K_DICIONARIO
(
    HANDLE         int PRIMARY KEY NOT NULL,
    TERMO_ORIGINAL varchar(50),
    TERMO_NOVO     varchar(50)
);

CREATE TABLE K_AGENDA
(
    HANDLE      int PRIMARY KEY NOT NULL,
    FILIAL      int,
    DATA        datetime,
    HORA        varchar(20),
    DATAFINAL   datetime,
    HORAFINAL   varchar(20),
    LUGAR       varchar(200),
    TITULO      varchar(200),
    TAGS        varchar(200),
    ARQUIVO     varchar(250),
    NOMEARQUIVO varchar(100),
    CONTEUDO    text,
    PUBLICO     char(1),
    ATIVO       char(1),
    STATUS      int,
    USUARIO     int,
    USUARIO2    int,
    USUARIO3    int,
    GRUPO       int,
    PESSOA      int,
    TIPO        int,
    REGIAO      int,
    TURMA       int,
    ALUNO       int
);

CREATE TABLE K_FN_USUARIOFILIAL
(
    HANDLE     int PRIMARY KEY NOT NULL,
    Z_GRUPO    int,
    USUARIO    int,
    FILIAL     int,
    PRIORIDADE int
);

CREATE TABLE K_LOG
(
    HANDLE      int PRIMARY KEY NOT NULL,
    Z_GRUPO     int,
    USUARIO     int,
    DATA        datetime,
    FILIAL      int,
    CLASSE      varchar(50),
    FUNCAO      varchar(50),
    QUERYSTRING varchar(250),
    REGISTRO    int
);

CREATE TABLE K_PD_ALCADAS
(
    HANDLE           int PRIMARY KEY NOT NULL,
    Z_GRUPO          int,
    CODIGO           int,
    NOME             varchar(30),
    LIBERAPEDIDOS    char(1),
    CONSULTAPEDIDOS  char(1),
    IMPRIMERELATORIO char(1),
    URL              varchar(42),
    TITULO           varchar(40),
    MENU             char(1),
    COMPARTILHADO    char(1)
);

CREATE TABLE K_PD_USUARIOS
(
    HANDLE               int PRIMARY KEY NOT NULL,
    Z_GRUPO              int,
    NOME                 varchar(50),
    APELIDO              varchar(250),
    SENHA                varchar(250),
    CNPJCPF              varchar(19),
    DATAULTIMOLOGIN      datetime,
    CLIENTE              int,
    NIVEL                int,
    PAGAMENTOEMISSAO     float(53),
    PAGAMENTORECEBIMENTO float(53),
    GRUPO                int,
    -- controle dr produção
    TERMINAL             int,
    PHPSESSID            varchar(250),
    META                 float(53),
    EQUIPE               int,
    EMAIL                varchar(250),
    REGIAO               int
);

/* ================================================================================================================== 
    Configuração de produto**
*/

CREATE TABLE PD_PRODUTOS
(
    HANDLE               int PRIMARY KEY NOT NULL,
    Z_GRUPO              int,
    DESCRICAO            text,
    NOME                 varchar(100),
    CODIGO               int,
    CODIGOALTERNATIVO    varchar(50),
    FAMILIA              int,
    DATAINCLUSAO         datetime,
    CODIGOREFERENCIA     varchar(30),
    MATERIAL             varchar(40),
    TAMANHO              varchar(20),
    COR                  varchar(20),
    CUSTOMAOOBRA         float(53),
    HORIZONTEFIRME       int,
    UNIDADEMEDIDAVENDAS  int,
    CODIGOBARRAS         varchar(40),
    COTACAOINTERNET      char(1),
    PESOVALOR            float(53),
    ALIQUOTASUBSTITUICAO int,
    NUMEROSERIE          char(1),
    LOTE                 char(1),
    CODIGOSERVICO        int,
    PRECOVENDA           float(53),
    CUSTOCOMPRAS         float(53),
    ATIVO                char(1),
    MARGEMLUCRO          float(53),
    MODELO               int,
    CUSTOS_FIXOS         float(53),
    VALOR_COMISSAO       float(53),
    ESTOQUE_MAXIMO       float(53),
    MARCA                varchar(40),
    FABRICANTE           int,
    MEDIDA_X             float(53),
    MEDIDA_Z             float(53),
    MEDIDA_Y             float(53),
    LOCALIZACAO          varchar(250),
    HABILITADOFRENTELOJA char(1),
    K_ENDERECO           int,
    K_CONTROLAESTOQUE    char(1),
    K_RESERVAESTOQUE     char(1),
    K_TERCEIRO           char(1),
    K_NCM                varchar(15),
    K_FORNECEDORES       varchar(250),
    K_TIPOMOVENTRADA     int,
    K_TIPOMOVSAIDA       int,
    K_ESTRUTURADO        char(1),
    K_FRETE              float(53),
    K_ALIQUOTAIPI        float(53),
    K_ALIQUOTAICMS       float(53),
    K_RESERVADO          int,
    K_KFILIAL            int,
    K_OTIMIZA            char(1)
);

CREATE TABLE CM_UNIDADESMEDIDA
(
    HANDLE        int PRIMARY KEY NOT NULL,
    Z_GRUPO       int,
    NOME          varchar(40),
    ABREVIATURA   varchar(5),
    CODIGOIN68    int,
    DATAINCLUSAO  datetime,
    DATAALTERACAO datetime
);

CREATE TABLE PD_FAMILIASPRODUTOS
(
    HANDLE                         int PRIMARY KEY NOT NULL,
    Z_GRUPO                        int,
    NIVELSUPERIOR                  int,
    ULTIMONIVEL                    char(1),
    FAMILIA                        varchar(40),
    NOME                           varchar(100),
    EMPRESA                        int,
    CODIGO                         varchar(5),
    METODOPREVISAO                 int,
    CONTAFINANCEIRAENTRADA         int,
    CONTAFINANCEIRASAIDA           int,
    COMPRADOR                      int,
    CENTROCUSTOSAIDA               int,
    CONTACONTABIL                  int,
    TIPOPRODUTOFISCAL              int,
    RECUPERAIPI                    char(1),
    RECUPERAICMS                   char(1),
    CLASSIFICACAOORIGEM            int,
    CLASSIFICACAOTRIBUTARIA        int,
    ALIQUOTAICMS                   int,
    REDUCAOBASEICMS                int,
    CLASSIFICACAOTIPI              int,
    ISSAPLICAVEL                   char(1),
    ISS                            int,
    CODIGOSERVICO                  int,
    IRRFAPLICAVEL                  char(1),
    IRRF                           int,
    INSSAPLICAVEL                  char(1),
    INSS                           int,
    IPIAPLICAVEL                   char(1),
    CONTACONTABILESTOQUE           int,
    UPAREA                         int,
    PISAPLICAVEL                   char(1),
    ALIQUOTAPISENTRADA             float(53),
    ALIQUOTAPISSAIDA               float(53),
    REDUCAOPISENTRADA              int,
    REDUCAOPISSAIDA                int,
    ITEMCOMPLEMENTAR               char(1),
    QUANTIDADECOMPLEMENTAR         float(53),
    COFINSAPLICAVEL                char(1),
    ALIQUOTACOFINSENTRADA          float(53),
    ALIQUOTACOFINSSAIDA            float(53),
    REDUCAOCOFINSENTRADA           int,
    REDUCAOCOFINSSAIDA             int,
    VALORLIMITELICITACAO           float(53),
    PERIODOLIMITE                  int,
    SALDOEMPEDIDO                  float(53),
    SALDOSEMLICITACAO              float(53),
    DATABASEPERIODO                datetime,
    PISRETIDOAPLICAVEL             char(1),
    PISRETIDO                      int,
    COFINSRETIDOAPLICAVEL          char(1),
    COFINSRETIDO                   int,
    CSLLAPLICAVEL                  char(1),
    CSLL                           int,
    IRPJAPLICAVEL                  char(1),
    IRPJ                           int,
    ALIQUOTAPISRETIDO              float(53),
    ALIQUOTACOFINSRETIDO           float(53),
    ALIQUOTACSLL                   float(53),
    ALIQUOTAIRPJ                   float(53),
    PERCENTUALCOMISSAO             float(53),
    UTILIZAMARGEMDELUCRO           char(1),
    SUBSTITRIB                     char(1),
    TIPOCOFINS                     int,
    TIPOPIS                        int,
    COFINSPAUTA                    char(1),
    PISPAUTA                       char(1),
    IPIPORVALOR                    char(1),
    CONTAFINANCEIRADEVFOR          int,
    CONTAFINANCEIRADEVCLI          int,
    CONTACONTABILDEVCLI            int,
    CONTAFINANCEIRAIMPORTACAO      int,
    CONTAFINANCEIRAEXPORTACAO      int,
    PLANONATUREZASFISCAIS          int,
    ESTADUALENTRADA                int,
    INTERESTADUALENTRADA           int,
    ESTADUALSAIDA                  int,
    INTERESTADUALSAIDA             int,
    INTERNACIONALSAIDA             int,
    INTERNACIONALENTRADA           int,
    IPIAPLICAVELSAIDA              char(1),
    EHKANBAN                       char(1),
    ALIQUOTACOFINSIMPORTACAO       float(53),
    ALIQUOTAPISIMPORTACAO          float(53),
    CONTACONTABILVENDA             int,
    REDUCAOBASENAOCONTRIBUINTE     int,
    CNAE                           varchar(10),
    TIPO                           int,
    ESTORNOPROPORCIONAL            char(1),
    BENEFICIOFISCAL                char(1),
    BASECALCULODOBRADA             char(1),
    CODIGOCNAE                     int,
    CLASSIFICACAOTRIBUTARIAIPI     int,
    CLASSIFICACAOTRIBUTARIAPIS     int,
    CLASSIFICACAOTRIBUTARIACOFINS  int,
    CLASSIFICACAOTRIBUTARIAISS     int,
    REDUCOESPISENTRADA             float(53),
    REDUCOESPISSAIDA               float(53),
    REDUCOESCOFINSENTRADA          float(53),
    REDUCOESCOFINSSAIDA            float(53),
    CONTACONTABILESTOQUESINTETICO  int,
    DATAINCLUSAO                   datetime,
    DATAALTERACAO                  datetime,
    CLASSIFICACAOALIQUOTAECF       int,
    K_SEPARAMATERIAL               char(1),
    K_CALCULAMETRAGEM              char(1),
    GRUPOCOMPRAS                   int,
    CLASSIFICACAOTRIBPISENTRADA    int,
    CLASSIFICACAOTRIBCOFINSENTRADA int,
    CENTROCUSTOENTRADA             int,
    CLASSIFICACAOTRIBIPIENTRADA    int,
    CODIGOBCCREDITOPISCOFINS       int,
    K_ALIQUOTAICMS                 float(53),
    K_ICMS                         char(1),
    K_CONTAFINANCEIRA              int,
    K_FILIAL                       int,
    CATMERCADOLIVRE                varchar(50)
);

CREATE TABLE K_FN_PRODUTOENDERECO
(
    HANDLE          int PRIMARY KEY NOT NULL,
    Z_GRUPO         int,
    PRODUTO         int,
    ENDERECO        int,
    QUANTIDADE      int,
    USUARIO         int,
    DATA            datetime,
    FILIAL          int,
    NOTAS           varchar(100),
    CODIGO          varchar(20),
    CANCELADO       char(1),
    ORIGEM          int,
    NUMEROORCAMENTO int,
    VALOR           float(53),
    QUANTIDADEFLOAT float(53),
    CUSTOCOMPRA     float(53),
    REQUISICAO      int,
    LOTE            int
);

CREATE TABLE K_FN_PRODUTOESTRUTURADO
(
    HANDLE          int PRIMARY KEY NOT NULL,
    Z_GRUPO         int,
    PAI             int,
    FILHO           int,
    QUANTIDADE      int,
    UNIDADE         varchar(2),
    UNITARIO        float(53),
    QUANTIDADEFLOAT float(53)
);

CREATE TABLE K_FN_TABELAPRECOS
(
    HANDLE            int PRIMARY KEY NOT NULL,
    Z_GRUPO           int,
    PRODUTO           int,
    CONDICAOPAGAMENTO int,
    PRECO             float(53),
    USUARIO           int,
    DATA              datetime,
    PORCENTAGEM       float(53),
    ACRESCIMO         float(53),
    INDICE            int,
    NOME              varchar(100),
    QUANTIDADE        int
);

CREATE TABLE K_FN_LISTAPRECOS
(
    HANDLE            int PRIMARY KEY NOT NULL,
    Z_GRUPO           int,
    NOME              varchar(50),
    CONDICAOPAGAMENTO int,
    FORMAPAGAMENTO    int,
    ATIVA             char(1),
    EXCLUSIVA         char(1)
);

CREATE TABLE K_FABRICANTE
(
    HANDLE int PRIMARY KEY NOT NULL,
    NOME   varchar(250)
);

CREATE TABLE K_MODELO
(
    HANDLE     int PRIMARY KEY NOT NULL,
    NOME       varchar(250),
    FABRICANTE int,
    ANOINICIAL varchar(4),
    ANOFINAL   varchar(4)
);

CREATE TABLE K_LISTAPRECO
(
    HANDLE       int PRIMARY KEY NOT NULL,
    NOME         varchar(250),
    FILIAL       int,
    INDICE       int,
    PRODUTO      int,
    ATIVO        char(1),
    DATAINICIO   date,
    DATAFIM      date,
    VALOR        float(53),
    PERCDESCONTO float(53)
);

/* ================================================================================================================== 
    Estoque**
*/

CREATE TABLE K_INVENTARIO
(
    HANDLE     int PRIMARY KEY NOT NULL,
    NUMERO     int             NOT NULL,
    MOVIMENTO  int             NOT NULL,
    PRODUTO    int             NOT NULL,
    ENDERECO   int,
    QUANTIDADE float(53),
    FILIAL     int,
    DATA       date,
    LOTE       varchar(50)
);

CREATE TABLE K_FN_REQUISICAO
(
    HANDLE             INT PRIMARY KEY NOT NULL,
    AREA               INT,
    DATA               DATETIME,
    NOTAS              VARCHAR(100),
    USUARIO            INT,
    ALMOXARIFADOORIGEM INT,
    BAIXADO            VARCHAR(2)
);

CREATE TABLE K_FN_ALMOXARIFADO
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(50),
    FILIAL  int
);

CREATE TABLE K_FN_AREA
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(50),
    FILIAL  int
);

/* ================================================================================================================== 
    Financeiro**
*/

CREATE TABLE K_TIPODOCUMENTO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    NOME        varchar(50),
    SIGLA       varchar(3),
    CONTAORIGEM int,
    CONTABAIXA  int,
    BAIXAR      char(1)
);

CREATE TABLE K_FN_CENTROCUSTO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    CODIGO      varchar(11),
    NOME        varchar(50),
    PLANOCONTAS int,
    FILIAL      int
);

CREATE TABLE K_PLANEJAMENTO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    PLANOCONTAS int,
    DATAINICIAL datetime,
    DATAFINAL   datetime,
    VALOR       float(53),
    BLOQUEIA    char(1),
    CENTROCUSTO int
);

CREATE TABLE K_FINANCEIRO
(
    -- cabeçalho
    HANDLE                 int PRIMARY KEY NOT NULL,
    FILIAL                 int,          -- por via das dúvidas
    SERIE                  int,          -- identificador: antigo título
    NATUREZA               int,          -- 1 - a pagar   2 - a receber
    ORCAMENTO              int,          -- se veio de compras ou vendas
    NUMERODOC              varchar(20),
    NUMEROPARCELA          int,
    PESSOA                 int,
    CONTAORIGEM            int,
    DOCFORNECEDOR          varchar(20),
    CONTABAIXA             int,
    DESCRICAO              varchar(250),
    HISTORICO              text,
    USUARIO                int,
    PREFIXO                int,
    COMISSAO               int,

    -- forma de pagamento/integrações
    FORMAPAGAMENTO         int,
    NUMEROCHEQUE           int,
    CODIGOPAGAMENTO        varchar(250), -- para tokens pagseguro, paypal, número do cartão de crédito, etc.
    TITULOREFERENCIA       int,          -- outro financeiro - cancelamento, renegociação, agrupamento, etc.

    -- controle
    STATUS                 int,
    ATIVO                  char(1),
    LIBERADO               char(1),      -- para contas a pagar

    -- valores
    VALOR                  float(53),
    VALORJUROS             float(53),
    VALORMULTA             float(53),
    VALORACRESCIMO         float(53),
    VALORDESCONTO          float(53),
    VALORTOTAL             float(53),
    VALORBAIXA             float(53),
    VALORSALDO             float(53),

    -- datas
    DATALANCAMENTO         datetime,
    DATAEMISSAO            datetime,
    DATAVENCIMENTOORIGINAL datetime,
    DATAVENCIMENTOREAL     datetime,
    DATABAIXA              datetime,

    -- campos que poderemos usar ou não.
    ANEXO                  varchar(250),
    NUMERONF               int,
    CENTROCUSTO            int
);

CREATE TABLE K_CONTAS
(
    -- controle
    HANDLE             int PRIMARY KEY NOT NULL,
    FILIAL             int,

    -- todos lançamento atualizará este saldo!
    SALDO              float(53),

    -- propriedades da conta
    PAI                int,
    NIVEL              int,
    CODIGO             varchar(8),
    NOME               varchar(50),

    -- define se o lançamento a débit aumenta ou diminui o saldo, crédito vice-versa
    DEBITOAUMENTA      char(1),

    -- um texto descritivo para as operações de débito e crédito que aparecerá ao usuário
    TEXTOCREDITO       varchar(50),
    TEXTODEBITO        varchar(50),

    -- para permitir conversão de moeda no futuro; setar como BRL no momento
    MOEDA              varchar(3),

    -- propriedade para definir se conta entra no relatório de fluxo de caixa
    FLUXODECAIXA       char(1),

    -- propriedade para definir se conta gera extrato bancário
    EXTRATOBANCARIO    char(1),

    -- propriedade para definir se conta entra no relatório patrimonial
    PATRIMONIAL        char(1),

    -- propriedades para definir se o plano pode ser usado em compras, vendas, contas a pagar ou receber
    USACOMPRAS         char(1),
    USAVENDAS          char(1),
    USACONTASAPAGAR    char(1),
    USACONTASARECEBER  char(1),

    -- para auditar quando o saldo foi atualizado ou quando foi a última movimentação
    DATAATUALIZACAO    datetime,

    -- para quando criarmos o módulo de orçamento no futuro
    ORCAMENTO          float(53),

    -- dados para conta bancária
    PESSOA             int,
    BANCO              char(5),
    AGENCIABANCARIA    varchar(20),
    CODIGOBANCARIO     varchar(20),
    LIMITE             float(53),

    -- dados para contas públicas
    CODIGOORCAMENTO    varchar(20),
    DESCRICAOORCAMENTO varchar(250),
    CODIGOFONTE        varchar(4),
    DESCRICAOFONTE     varchar(20),
    NATUREZADESPESA    char(1)
);

-- esta tabela guarda os saldos anteriores para cada período. deve ser executada 1x por período (mensal?)
CREATE TABLE K_CONTASSALDOS
(
    HANDLE int PRIMARY KEY NOT NULL,
    CONTA  int,
    FILIAL int,
    VALOR  float(53),
    DATA   datetime,
    ATIVO  char(1)
);

-- lançamentos devem ser feitos dois a dois, um a crédito e um a débito, um valor positivo e outro negativo!
CREATE TABLE K_LANCAMENTOS
(
    -- controle
    HANDLE        int PRIMARY KEY NOT NULL,
    FILIAL        int,

    -- dados do lançamento
    DATA          datetime,
    CONTA         int,
    VALOR         float(53),
    HISTORICO     varchar(250),

    -- para auditoria; C para crédito, D para débito
    TIPO          char(1),

    -- lançamento de contrapartida (handle)
    CONTRAPARTIDA int,

    -- para linhas de crédito
    PESSOA        int,

    -- vinculação com módulos contas a pagar/receber/compras/vendas (opcional)
    PARCELA       int,
    DUPLICATA     int
);

CREATE TABLE K_TALAO
(
    HANDLE       int PRIMARY KEY NOT NULL,
    NUMERO       int,
    FOLHAINICIAL int,
    FOLHAFINAL   int,
    BANCO        varchar(50),
    AGENCIA      varchar(50),
    CONTA        varchar(15),
    FILIAL       int
);

CREATE TABLE K_CHEQUE
(
    HANDLE             int PRIMARY KEY NOT NULL,
    DATAEMISSAO        datetime,
    CPF                varchar(20),
    DATAVENCIMENTOREAL datetime,
    VALOR              float(53),
    ALINEA             int,
    SITUACAO           int,
    EMITENTE           varchar(50),
    BANCO              varchar(50),
    AGENCIA            varchar(50),
    CONTA              varchar(15),
    TALAO              int,
    FOLHA              int
);

CREATE TABLE CP_CONDICOESPAGAMENTO
(
    HANDLE                    int PRIMARY KEY NOT NULL,
    Z_GRUPO                   int,
    DESCRICAO                 varchar(80),
    OBSERVACAO                text,
    PARCELADIGITADA           int,
    DIAVENCIMENTO             int,
    DIAUTIL                   char(1),
    DIAFIXO                   int,
    EMPRESA                   int,
    UTILIZAMAPA               int,
    NRPARCELAS                int,
    INTERVALO                 int,
    SOMARIPI                  char(1),
    SOMARICMS                 char(1),
    SOMARFRETE                char(1),
    SOMARISS                  char(1),
    SOMARIRF                  char(1),
    SOMARSEGURO               char(1),
    SOMARACRESCIMOS           char(1),
    TEMENTRADA                char(1),
    DIASPRIMEIRA              int,
    FECHARANTES               int,
    TABELAPRECOS              int,
    LISTAPRECOS               int,
    CONSIDERARAPENASDIASUTEIS char(1),
    LISTAPRECOSCOMPRAS        int,
    TABELAPRECOSCOMPRAS       int,
    DATAINCLUSAO              datetime,
    DATAALTERACAO             datetime,
    FORMAPAGAMENTO            int,
    CODIGO                    int,
    GPPAGAMENTOECF            int,
    CODIGOFINALIZADORA        int,
    DESCRICAOFINALIZADORA     varchar(30),
    ADMINISTRADORACARTAO      int,
    TIPORECEBIMENTO           int,
    BAIXAR                    char(1),
    MODDIAUTIL                varchar(2),
    SOMARICMSST               char(1)
);

CREATE TABLE CP_MAPACONDICAOPAGAMENTO
(
    HANDLE             int PRIMARY KEY NOT NULL,
    Z_GRUPO            int,
    DIAS               int,
    PERCENTUAL         float(53),
    CONDICOESPAGAMENTO int,
    DIAVENCIMENTO      int,
    TIPOCOBRANCA       int,
    FORMAPAGAMENTO     int,
    K_PREFIXO          int
);

CREATE TABLE FN_FORMASPAGAMENTO
(
    HANDLE           int PRIMARY KEY NOT NULL,
    Z_GRUPO          int,
    NOME             varchar(40),
    CODIGO           varchar(5),
    NUMERODARF       varchar(250),
    TIPOCONTA        varchar(5),
    TIPOOPERACAO     int,
    COMERCIOEXTERIOR int,
    FORMALANCAMENTO  int,
    TIPOTITULO       int,
    CODIGOTRIBUTO    varchar(4),
    TITULORASTREADO  char(1),
    K_BAIXAREM       int,
    K_USARFRENTELOJA char(1),
    IDSAUDE          int,
    K_DOCLIQUIDEZ    char(1),
    DESCONTO         float(53)
);

/* ================================================================================================================== 
    Pessoa**
*/

CREATE TABLE ESTADOS
(
    HANDLE                         int PRIMARY KEY NOT NULL,
    Z_GRUPO                        int,
    PAIS                           int,
    NOME                           varchar(20),
    Z_NOME                         varchar(20),
    GENTILICO                      varchar(20),
    SIGLA                          varchar(3),
    Z_SIGLA                        varchar(3),
    CODIGO                         int,
    MASCARAINSCRICAOESTADUAL       varchar(25),
    MASCARAINSCRICAOESTADUALFISICA varchar(25),
    MASCARAINSCRICAOESTADUALRURAL  varchar(25),
    CODIGOIBGE                     int,
    IDINTEGRACAO                   int,
    DATAINCLUSAO                   datetime,
    DATAALTERACAO                  datetime,
    IDSAUDE                        int
);

CREATE TABLE MUNICIPIOS
(
    HANDLE                    int PRIMARY KEY NOT NULL,
    Z_GRUPO                   int,
    PAIS                      int,
    ESTADO                    int,
    NOME                      varchar(90),
    CODIGOIBGE                int,
    CEP                       varchar(15),
    DDD                       int,
    SINIEF                    int,
    GENTILICO                 varchar(40),
    CODIGOESTADUAL            int,
    MASCARAINSCRICAOMUNICIPAL varchar(19),
    CODIGOSIAFI               int,
    IDINTEGRACAO              int,
    Z_NOME                    varchar(90),
    DATAINCLUSAO              datetime,
    DATAALTERACAO             datetime,
    IDSAUDE                   int
);

CREATE TABLE PAISES
(
    HANDLE             int PRIMARY KEY NOT NULL,
    Z_GRUPO            int,
    NOME               varchar(40),
    Z_NOME             varchar(40),
    GENTILICO          varchar(20),
    SIGLA              varchar(3),
    Z_SIGLA            varchar(3),
    DDI                int,
    CODIGOGIARS        int,
    CODIGORAIS         int,
    CODIGOBANCOCENTRAL int,
    CODIGOANS          int,
    IDINTEGRACAO       int,
    DATAALTERACAO      datetime,
    DATAINCLUSAO       datetime,
    IDSAUDE            int
);

CREATE TABLE K_FN_PESSOAENDERECO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    Z_GRUPO     int,
    PESSOA      int,
    ORDEM       int,
    CEP         varchar(12),
    LOGRADOURO  varchar(50),
    BAIRRO      varchar(30),
    COMPLEMENTO varchar(50),
    NUMERO      varchar(10),
    CIDADE      varchar(30),
    ESTADO      int,
    TIPO        varchar(20)
);

CREATE TABLE K_FN_CONTATO
(
    HANDLE   int PRIMARY KEY NOT NULL,
    Z_GRUPO  int,
    PESSOA   int,
    ORDEM    int,
    NOME     varchar(30),
    AREA     varchar(30),
    TELEFONE varchar(20),
    EMAIL    varchar(50)
);

CREATE TABLE K_FN_ENDERECO
(
    HANDLE       int PRIMARY KEY NOT NULL,
    Z_GRUPO      int,
    NOME         varchar(50),
    ALMOXARIFADO int,
    RUA          varchar(10),
    ENDERECO     varchar(100)
);

CREATE TABLE K_FN_PESSOA
(
    HANDLE            int PRIMARY KEY NOT NULL,
    Z_GRUPO           int,
    NOME              varchar(250),
    FILIAL            int,
    FORNECEDOR        char(1),
    TIPO              char(1),
    NOMEFANTASIA      varchar(250),
    RG                varchar(20),
    BLOQUEIO          char(1),
    RESTRICAO         char(1),
    AREA              int,
    LISTAPRECO        int,
    CPFCNPJ           varchar(20),
    CLIENTE           char(1),
    FUNCIONARIO       char(1),
    EMPRESA           char(1),
    CNAE              varchar(20),
    CONTAPAGAMENTO    int,
    TRANSPORTADOR     char(1),
    OBSERVACOES       text,
    ATIVO             char(1),
    SEGMENTO          int,
    CLASSES           int,
    IMAGEM            varchar(250),
    AGRUPABOLETO      char(1),
    VENDEDOR          int,
    FORMAPAGAMENTO    int,
    CONDICAOPAGAMENTO int,
    PLACA             varchar(20),
    CODIGOANTT        varchar(30),
    CONTRIBUINTEICMS  char(1),

    -- índice da lista de preços (não é tabela! eventualmente remover LISTAPRECO)
    LISTA             int,

    -- campos extra educacional
    NASCIMENTO        datetime,
    MATRICULA         varchar(15),
    SEXO              varchar(1),
    NATURALIDADE      varchar(30),
    -- TURMA varchar(15),
    -- TURNO varchar(1),
    PROFISSAO         varchar(30),
    DATAADESAO        datetime,
    ALUNO             char(1),
    PROFESSOR         char(1),
    ETNIA             varchar(20),
    TIPOSANGUINEO     varchar(3),
    -- ESTRANGEIRO char(1),
    BOLSA             char(1),
    DADOSCENSO        text,
    ESCOLARIDADE      varchar(30),
    RELIGIAO          varchar(30),
    ESPECIALIZACAO    varchar(100)
);

CREATE TABLE K_FN_PESSOAVINCULO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    TIPO        int,
    PAI         int,
    FILHO       int,
    RESPONSAVEL char(1),
    TIPOVINCULO int
);

CREATE TABLE K_FN_TIPOVINCULO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    NOME        varchar(30),
    RELACIONADO varchar(30)
);

/* ================================================================================================================== 
    Nota**
*/

CREATE TABLE K_NOTA
(
    -- controle
    HANDLE           int PRIMARY KEY NOT NULL,
    FILIAL           int,
    FATURA           char(1), -- true = nota de faturamento, false = folha de pagamento

    -- cabeçalho
    TIPO             char(1),
    ORIGEM           int,
    NUMORCAMENTO     int,
    FOLHAPAGAMENTO   int,
    DATA             datetime,
    DESTINO          int,
    FINALIDADE       int,
    STATUS           int,
    PESSOA           int,
    USUARIO          int,
    VENDEDOR         int,
    SUPERVISOR       int,
    PLANOCONTAS      int,
    DESCRICAO        varchar(250),
    HISTORICO        text,

    -- nota fiscal
    NUMNOTA          int,
    SERIE            int,
    LOTE             int,
    CHAVE            varchar(44),
    PROTOCOLO        varchar(15),
    RECIBO           varchar(15),
    DATANOTA         datetime,
    HORANOTA         varchar(8),
    NATUREZAOPERACAO varchar(60),
    INFORMACOESFISCO varchar(250),
    XMLRETORNO       text,
    CHAVEREFERENCIA  varchar(44),
    MODELO           char(2),

    -- folha de pagamento/contrato
    DATAINICIO       datetime,
    DATATERMINO      datetime,
    DIAVENCIMENTO    int,
    CONTRATO         text,

    -- frete/transporte
    TRANSPORTADORA   int,
    FRETE            int,
    DATAENTREGA      datetime,
    BAIRRO           varchar(60),
    LOGRADOURO       varchar(60),
    COMPLEMENTO      varchar(60),
    NUMERO           varchar(60),
    ESTADO           char(2),
    MUNICIPIO        int,
    PLACA            varchar(15),
    UFPLACA          char(2),
    RNTC             varchar(20),
    MOTORISTA        varchar(50),
    VALORICMSRETIDO  float(53),

    -- volumes
    VOLQUANTIDADE    int,
    VOLESPECIE       varchar(60),
    VOLMARCA         varchar(60),
    VOLNUMERACAO     varchar(60),
    VOLPESOLIQUIDO   float(53),
    VOLPESOBRUTO     float(53),

    -- valores para referência/extras
    VALORTOTAL       float(53),
    EXTRA1           varchar(60),
    EXTRA2           varchar(60),
    EXTRA3           varchar(60),
    EXTRA4           varchar(60),
    FONTE            int,

    -- campos da compra
    DOCFORNECEDOR    varchar(50),

    -- devolução/crédito
    VALORCREDITADO   float(53),
    CREDITOUTILIZADO int
);

CREATE TABLE K_NOTAITENS
(
    -- controle
    HANDLE          int PRIMARY KEY NOT NULL,
    NOTA            int,

    -- produtos
    TABELAPRECO     int,
    PRODUTO         int,
    NOMEPRODUTO     varchar(50),
    UNIDADE         varchar(3),
    QUANTIDADE      float(53),

    -- medidas
    MEDIDA_X        float(53),
    MEDIDA_Z        float(53),
    MEDIDA_T        float(53),
    MEDIDA_Y        float(53),
    PESO            float(53),
    EMENDA          char(2),
    DATAENTREGA     datetime,
    DATAEXPEDICAO   datetime, -- why Rei dos capachos???

    -- valores
    VALORUNITARIO   float(53),
    VALORDESCONTO   float(53),
    VALORIPI        float(53),
    VALORBCIPI      float(53),
    VALORTOTAL      float(53),
    VALORFRETE      float(53),
    VALORICMS       float(53),
    VALORBCICMS     float(53),
    VALORICMSST     float(53),
    VALORBCICMSST   float(53),
    VALORPIS        float(53),
    VALORBCPIS      float(53),
    VALORCOFINS     float(53),
    VALORBCCOFINS   float(53),
    VALORCOFINSST   float(53),
    VALORISSQN      float(53),
    VALORBCISSQN    float(53),

    -- percentuais
    PERCDESCONTO    float(53),
    PERCIPI         float(53),
    PERCICMS        float(53),
    PERCICMSST      float(53),
    PERCPIS         float(53),
    PERCCOFINS      float(53),
    PERCCOFINSST    float(53),
    PERCISSQN       float(53),
    PRECOTABELA     float(53),

    -- tributários
    TIPOOPERACAO    int,
    NCM             varchar(25),
    CST             varchar(10),
    CFOP            varchar(10),
    CSOSN           varchar(3),
    CBENEF          VARCHAR(45),
    PERCICMSDIF     VARCHAR(45),
    VALORICMSDIF    VARCHAR(45),
    VALORBCFCP      VARCHAR(45),
    PERCFCP         VARCHAR(45),
    VALORFCP        VARCHAR(45),
    PDIF            VARCHAR(45),
    VICMSDIF        VARCHAR(45),
    VICMS           VARCHAR(45),
    
    -- estoque
    QTDENTREGUE     int,
    QTDBAIXADA      int,
    ENDERECO        int,
    ENDERECODESTINO int,
    LOTE            int,

    -- integração com projetos
    NUMPROJETO      char(2),
    ARQUIVO         varchar(250),
    SERVICO         int
);

CREATE TABLE K_NOTADUPLICATAS
(
    -- controle
    HANDLE                 int PRIMARY KEY NOT NULL,
    NOTA                   int,

    -- cabeçalho
    NUMERO                 int,
    PREFIXO                int,
    FORMAPAGAMENTO         int,

    -- datas e valores
    DATAEMISSAO            datetime,
    DATAVENCIMENTOORIGINAL datetime,
    DATAVENCIMENTOREAL     datetime,
    DATABAIXA              datetime,
    VALOR                  float(53),
    BAIXADO                char(1),

    -- valores extra
    VALORADITIVO           float(53),
    VALORJUROS             float(53),
    VALORMULTA             float(53),
    VALORDESCONTO          float(53),
    VALORTOTAL             float(53),
    VALORBAIXA             float(53),

    -- integração com financeiro
    PARCELA                int,
    CHEQUE                 int,

    -- integração com boletos
    BANCO                  char(3),
    NOSSONUMERO            varchar(60),
    IDINTEGRACAO           varchar(100),


    -- movimento de caixa
    CAIXA                  int,
    USUARIOCAIXA           int,
    TIPOMOVIMENTO          char(1),
    HORA                   varchar(10),
    HISTORICO              varchar(250)
);

CREATE TABLE K_TIPOOPERACAO
(
    -- identificação
    HANDLE           int PRIMARY KEY NOT NULL,
    FILIAL           int,
    CODIGO           char(3),
    NOME             varchar(250),

    -- histórico legislação
    DESCRICAO        varchar(250),

    -- classificação
    TIPO             char(1),
    MODALIDADE       int,

    -- localização
    -- DESTINO int,
    -- ESTADO char(2),

    -- CFOP e CST
    CFOP             char(5),
    CSTORIGEM        char(1),
    CSTTRIBUTACAO    char(2),
    CSOSN            char(3),
    CSTPISCOFINS     char(2),
    CSTIPI           char(2),
    CENQIPI          char(3),

    -- booleanos
    GERAFINANCEIRO   char(1),
    TRIBUTADO        char(1),
    CREDITAICMS      char(1),
    CREDITAIPI       char(1),
    SUBSTITUICAOTRIB char(1),

    -- ICMS
    TIPOICMS         int,
    CALCULOBASE      float(53),

    -- alíquotas
    ALIQUOTAICMS     float(53),
    ALIQUOTAPIS      float(53),
    ALIQUOTACOFINS   float(53),
    ALIQUOTAISSQN    float(53)
);

CREATE TABLE K_STATUS
(
    HANDLE       int PRIMARY KEY NOT NULL,
    NOME         varchar(25),
    COR          varchar(2),
    GRUPOENTRADA int,
    GRUPOSAIDA   int
);

CREATE TABLE K_STATUSTRANSICOES
(
    HANDLE  int PRIMARY KEY NOT NULL,
    DE      int,
    PARA    int,
    ENTRADA char(1),
    SAIDA   char(1)
);

CREATE TABLE K_HISTORICO
(
    HANDLE int PRIMARY KEY NOT NULL,
    TEXTO  varchar(250),
    ATIVO  char(1)
);

CREATE TABLE K_NOTACOMISSAO
(
    HANDLE     int PRIMARY KEY NOT NULL,
    NOTA       int,
    PESSOA     int,
    VALORBASE  float(53),
    PERCENTUAL float(53),
    VALOR      float(53),
    HISTORICO  varchar(250),
    TIPO       int
);

CREATE TABLE K_NOTAANEXOS
(
    HANDLE    int PRIMARY KEY NOT NULL,
    NOTA      int,
    ENDERECO  varchar(250),
    NOME      varchar(250),
    DESCRICAO varchar(250),
    TAMANHO   varchar(10),
    ATIVO     char(1),
    DATA      datetime
);

CREATE TABLE K_FN_TARIFAS
(
    HANDLE    int PRIMARY KEY NOT NULL,
    Z_GRUPO   int,
    NOME      varchar(50),
    DESCRICAO varchar(250),
    ALIQUOTA  float(53),
    ESTADO    int,
    CODIGO    varchar(250)
);

CREATE TABLE TR_TIPIS
(
    HANDLE                    int PRIMARY KEY NOT NULL,
    Z_GRUPO                   int,
    CODIGONBM                 varchar(20),
    ABREVIATURANOTAFISCAL     varchar(3),
    NOME                      varchar(80),
    ALIQUOTA                  float(53),
    UNIDADEPADRAO             int,
    UNIDADEUTILIZADA          int,
    TRIBUTACAOIPI             varchar(2),
    SITUACAOTRIB              float(53),
    CODIGOINSUMO              varchar(3),
    ALIQUOTAIMPORTACAO        float(53),
    PERCREDUCAOBASECALCULOIPI float(53),
    CODIGOEX                  float(53),
    CODIGOPRODUTO             float(53),
    CLASSIFICACAOORIGEM       int,
    CLASSIFICACAOTRIBUTARIA   int,
    CODPRODUTO                varchar(14),
    EHCOMBUSTIVEL             char(1),
    CODIGOSEFAZ               varchar(5),
    IEAPLICAVEL               char(1),
    ALIQUOTAEXPORTACAO        float(53),
    EHENERGIAPETROLEO         char(1),
    CODIGOSH                  varchar(15),
    CODIGONALADI              varchar(15),
    CODIGOCOMBUSTIVELANP      int
);

CREATE TABLE K_FN_CFOP
(
    HANDLE    int PRIMARY KEY NOT NULL,
    Z_GRUPO   int,
    CODIGO    varchar(6),
    DESCRICAO text,
    NOME      varchar(100)
);

CREATE TABLE K_FN_CST_ORIGEM
(
    HANDLE    int PRIMARY KEY NOT NULL,
    Z_GRUPO   int,
    CODIGO    char(3),
    DESCRICAO text,
    NOME      varchar(100)
);

CREATE TABLE K_FN_CST_TRIBUTACAO
(
    HANDLE    int PRIMARY KEY NOT NULL,
    Z_GRUPO   int,
    CODIGO    char(3),
    DESCRICAO text,
    NOME      varchar(100)
);

/* ================================================================================================================== 
    CRM**
*/

CREATE TABLE K_CRM_FONTES
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(250)
);

CREATE TABLE K_CRM_SEGMENTOS
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(250)
);

CREATE TABLE K_CRM_ARQUIVOS
(
    HANDLE     int PRIMARY KEY NOT NULL,
    Z_GRUPO    int,
    NEGOCIACAO int,
    NOME       varchar(250),
    ENDERECO   varchar(250),
    TAMANHO    float(53)
);

CREATE TABLE K_CRM_ATUALIZACOES
(
    HANDLE      int PRIMARY KEY NOT NULL,
    Z_GRUPO     int,
    NEGOCIACAO  int,
    ATUALIZACAO varchar(250),
    DATA        datetime,
    USUARIO     int
);

CREATE TABLE K_CRM_CAMPANHAS
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(250)
);

CREATE TABLE K_CRM_ETAPAS
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(250)
);

CREATE TABLE K_CRM_NEGOCIACAOITENS
(
    HANDLE             int PRIMARY KEY NOT NULL,
    Z_GRUPO            int,
    CODIGO             varchar(250),
    ENVIAEMAIL         char(1),
    PRODUTO            int,
    QUANTIDADE         float(53),
    VALORUNITARIO      float(53),
    VALORORIGINAL      float(53),
    VALORLIQUIDO       float(53),
    VALORTOTAL         float(53),
    VALORCOMISSAO      float(53),
    INTERVALOCOBRANCA  int,
    DATAINICIOCOBRANCA datetime,
    MENSAL             char(1),
    DURACAO            int,
    NEGOCIACAO         int,
    ESTRUTURADOS       varchar(250)
);

CREATE TABLE K_CRM_NEGOCIACOES
(
    HANDLE              int PRIMARY KEY NOT NULL,
    Z_GRUPO             int,
    NOME                varchar(250),
    FONTE               int,
    CAMPANHA            int,
    PESSOA              int,
    RESUMO              varchar(250),
    URL                 varchar(250),
    AVALIACAO           int,
    PROJETO             int,
    ETAPA               int,
    FILIAL              int,
    DATAINCLUSAO        datetime,
    USUARIOINCLUIU      int,
    RESPONSAVEL         int,
    PROCESSO            int,
    FATURAMENTO         float(53),
    QTDUSUARIOS         int,
    POSSUIPRODUTO       char(1),
    ORCAMENTODISPONIVEL float(53),
    VALORTOTAL          float(53),
    EMAIL               varchar(50),
    TELEFONE            varchar(30)
);

CREATE TABLE K_CRM_NEGOCIACOESUSUARIOS
(
    HANDLE     int PRIMARY KEY NOT NULL,
    Z_GRUPO    int,
    USUARIO    int,
    NEGOCIACAO int
);

CREATE TABLE K_CRM_PROCESSOS
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(250)
);

CREATE TABLE K_CRM_PROCESSOSTAREFAS
(
    HANDLE   int PRIMARY KEY NOT NULL,
    Z_GRUPO  int,
    PROCESSO int,
    ASSUNTO  varchar(250),
    TIPO     int,
    NOTAS    varchar(250),
    PRAZO    int
);

CREATE TABLE K_CRM_PROJETOS
(
    HANDLE  int PRIMARY KEY NOT NULL,
    Z_GRUPO int,
    NOME    varchar(250),
    FILIAL  int
);

CREATE TABLE K_CRM_TAREFAS
(
    HANDLE       int PRIMARY KEY NOT NULL,
    Z_GRUPO      int,
    ASSUNTO      varchar(250),
    DATA         datetime,
    HORA         varchar(5),
    CONTATOS     varchar(250),
    RESPONSAVEIS varchar(250),
    NOTAS        varchar(250),
    TIPO         int,
    NEGOCIACAO   int,
    CONTATO      int,
    CONCLUIDO    char(1)
);

/* ================================================================================================================== 
    Educacional**
*/

CREATE TABLE K_PROVAQUESTAO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    DATA        datetime,
    CATEGORIA   int,
    SEGMENTO    int,
    DIFICULDADE int,
    ORIGEM      varchar(50),
    AUTOR       int,
    ENUNCIADO   text
);

CREATE TABLE K_PROVAITEM
(
    HANDLE  int PRIMARY KEY NOT NULL,
    QUESTAO int,
    ORDEM   int,
    TEXTO   text,
    CORRETO char(1)
);

CREATE TABLE K_AREACONHECIMENTO
(
    HANDLE     int PRIMARY KEY NOT NULL,
    DISCIPLINA int,
    PAI        int,
    NOME       varchar(250)
);

CREATE TABLE K_ALUNOQUESTAO
(
    HANDLE   int PRIMARY KEY NOT NULL,
    QUESTAO  int,
    ALUNO    int,
    RESPOSTA int,
    CORRETO  char(1)
);

CREATE TABLE K_PROVAGABARITO
(
    HANDLE  int PRIMARY KEY NOT NULL,
    SERIE   int,
    QUESTAO int,
    VALOR   int,
    ORDEM   int
);

CREATE TABLE K_FICHAMEDICA
(
    HANDLE                int PRIMARY KEY NOT NULL,
    ALUNO                 int,
    DATA                  datetime,
    DOENCAGRAVE           varchar(250),
    ALERGIA               varchar(250),
    RESTRICAOALIMENTAR    varchar(250),
    MEDICAMENTOCONTROLADO varchar(250),
    TRAUMA                varchar(250),
    PLANOSAUDE            varchar(250),
    OBSERVACAO            text,
    DOENCASJSON           text,
    TRANSTORNOSJSON       text,
    TIPOTRANSTORNO        varchar(50),
    NUMEROSUS             varchar(50),
    AVALIACAO             varchar(250),
    LAUDOMEDICO           varchar(250),
    ARQUIVO               varchar(250)
);

CREATE TABLE K_DISCIPLINA
(
    HANDLE        int PRIMARY KEY NOT NULL,
    NOME          varchar(100),
    SEGMENTO      char(2),
    OBRIGATORIO   char(1),
    CARGAHORARIA  varchar(20),
    AULASSEMANAIS int
);

CREATE TABLE K_SERIE
(
    HANDLE         int PRIMARY KEY NOT NULL,
    NOME           varchar(100),
    FAIXA_ETARIA   int,
    CICLO_ETAPA    int,
    NOTA_APROVACAO float(50)
);

CREATE TABLE K_SERIE_MATERIA
(
    HANDLE                int PRIMARY KEY NOT NULL,
    SERIE                 int,
    COMPONENTE_CURRICULAR int,
    BASE_CURRICULAR       int,
    MASCARA_NOTA          varchar(2),
    AREA_CONHECIMENTO     int,
    CODIGO                varchar(53),
    CARGA                 float(53)
);

CREATE TABLE K_TURMA
(
    HANDLE       int PRIMARY KEY NOT NULL,
    FILIAL       int,
    NOME         varchar(100),
    TURNO        varchar(2),
    ANO          int,
    SERIE        int,
    PERIODO      int,
    DIVISAO      int,
    SEGMENTO     int,
    ATUAL        char(1),
    CARGAHORARIA varchar(20),
    MINALUNOS    int,
    MAXALUNOS    int,
    MAXPERIODOS  int,
    EJA          char(1),
    NUMERO       int,
    EAD          char(1),
    DATAINICIO   datetime,
    DATAFIM      datetime
);

CREATE TABLE K_TURMAALUNO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    ALUNO       int,
    TURMA       int,
    ATIVO       char(1),
    DATAENTRADA datetime,
    DATASAIDA   datetime,
    HISTORICO   varchar(250),
    MEDIAFINAL  float(53),
    CONCEITO    varchar(10),
    TURNO       varchar(2)
);

CREATE TABLE K_TURMAHORARIO
(
    HANDLE         int PRIMARY KEY NOT NULL,
    TURMA          int,
    PROFESSOR      int,
    DISCIPLINA     int,
    DIASEMANA      int,
    HORARIOINICIO  varchar(10),
    HORARIOTERMINO varchar(10),
    TIMESLOT       int
);

CREATE TABLE K_AULA
(
    HANDLE     int PRIMARY KEY NOT NULL,
    HORARIO    int,
    DATA       datetime,
    PROFESSOR  int,
    SUBSTITUTO char(1),
    CONTEUDO   text
);

CREATE TABLE K_DIARIOCLASSE
(
    HANDLE    int PRIMARY KEY NOT NULL,
    AULA      int,
    HORARIO   int,
    DATA      datetime,
    ALUNO     int,
    PRESENCA  char(1),
    HISTORICO varchar(250)
);

CREATE TABLE K_TURMAAVALIACAO
(
    HANDLE     int PRIMARY KEY NOT NULL,
    TURMA      int,
    DISCIPLINA int,
    NOME       varchar(250),
    DESCRICAO  text,
    PESO       float(53),
    DATA       datetime,
    CONTEUDO   text
);

CREATE TABLE K_ALUNOSITUACAO
(
    HANDLE int PRIMARY KEY NOT NULL,
    NOME   varchar(50)
);

CREATE TABLE K_BOLETIM
(
    HANDLE      int PRIMARY KEY NOT NULL,
    AVALIACAO   int,
    ALUNO       int,
    NOTA        float(53),
    NOTAREVISAO float(53),
    HISTORICO   text
);

CREATE TABLE K_OCORRENCIA
(
    HANDLE  int PRIMARY KEY NOT NULL,
    TIPO    int,
    DATA    datetime,
    USUARIO int,
    STATUS  int,
    NOTAS   text
);

CREATE TABLE K_TIPOOCORRENCIA
(
    HANDLE     int PRIMARY KEY NOT NULL,
    NOME       varchar(100),
    SEVERIDADE varchar(20),
    VALOR      int
);

CREATE TABLE K_OCORRENCIAPESSOA
(
    HANDLE      int PRIMARY KEY NOT NULL,
    OCORRENCIA  int,
    PESSOA      int,
    OBSERVACOES varchar(200)
);

/* ================================================================================================================== 
    Producao**
*/

CREATE TABLE K_MEDIDAS_PADRAO
(
    HANDLE     INT PRIMARY KEY NOT NULL,
    MEDIDABASE int             NOT NULL,
    MEDIDA     int
);

CREATE TABLE K_PRODUCAOITENS
(
    HANDLE            int PRIMARY KEY NOT NULL,
    NOTAITENS         int,
    -- PRODUTO int,
    DATAPRAZOORIGINAL date, -- somente o cadastro?
    DATAPRAZOREAL     date,
    DATACADASTRO      date,
    STATUS            int,
    FILIAL            int,
    -- ORDEM int,
    NOTAS             varchar(255),
    CODIGOBARRAS      varchar(10)
);

CREATE TABLE K_PRODUCAOETAPAS
(
    HANDLE        int PRIMARY KEY NOT NULL,
    NOME          varchar(50),
    ORDEMPRODUCAO int UNIQUE
);

CREATE TABLE K_PRODUCAODOCS
(
    HANDLE     int PRIMARY KEY NOT NULL,
    URL        varchar(50),
    NOME       varchar(10),
    ITEM       int,
    OBSERVACAO varchar(250)
);

CREATE TABLE K_PRODUCAOTERMINAL
(
    HANDLE         int PRIMARY KEY NOT NULL,
    NOME           varchar(50),
    PRODUCAOETAPAS int,
    ATIVO          char(1)
);

CREATE TABLE K_USUARIOSNIVEIS
(
    HANDLE      int PRIMARY KEY NOT NULL,
    NOME        varchar(15),
    DESCRICAO   varchar(50),
    PORCENTAGEM float(53)
);

/* ================================================================================================================== 
    Suporte**
*/

CREATE TABLE K_CHAMADOHISTORICO
(
    HANDLE      int PRIMARY KEY NOT NULL,
    CHAMADO     int,
    USUARIO     int,
    COMENTARIOS text,
    ANEXO       varchar(250),
    OBS_SISTEMA text,
    DATA        datetime,
    HORA        varchar(20),
    STATUS      int,
    REVISAO     varchar(20)
);

CREATE TABLE K_CHAMADOS
(
    HANDLE int PRIMARY KEY NOT NULL,
    TIPO int,
    STATUS int,
    PRIORIDADE int,
    COMPONENTE varchar(30),
    CLIENTE int,
    PRODUTO int,
    RESPONSAVEL int,
    CONTATONOME varchar(30),
    CONTATOEMAIL varchar(50),
    CONTATOTELEFONE varchar(20),
    COPIACARBONO varchar(250),
    ASSUNTO varchar(100),
    PRAZO datetime,
    DUPLICADO int,
    AFTER int,
    REPORTER int,
    FILIAL int
);

CREATE TABLE K_PONTO
(
    HANDLE int PRIMARY KEY NOT NULL,
    USUARIO int,
    FILIAL int,
    LOCAL int,
    DATA datetime,
    HORAENTRADA int,
    MINUTOENTRADA int,
    HORASAIDA int,
    MINUTOSAIDA int,
    HORAREAL varchar(5),
    HORAEDICAO varchar(5),
    -- ipv6 pode ter até 40, com separadores
    IP varchar(32),
    IPEDICAO varchar(32),
    DOENTE char(1),
    NOTAS text,
    INTERVALO int
);

/* ================================================================================================================== 
    Final**
*/

COMMIT
