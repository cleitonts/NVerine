<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 31/07/2019
 * Time: 17:16
 */

namespace src\entity;

include_once("class/Notificacoes.php");
include_once("class/Email.php");

use src\services\Transact\ExtPDO as PDO;
use Notificacoes\Chat;
use Notificacoes\ChatFile;

class SuporteChamadoETT extends ObjectETT
{
    // máximos
    const MAX_TIPOS = 6;
    const MAX_PRIORIDADES = 4;
    const MAX_STATUS = 11;

    // tipos de chamado
    const TIPO_SUPORTE = 1;
    const TIPO_DESENVOLVIMENTO = 2;
    const TIPO_CUSTOMIZACAO = 3;
    const TIPO_IMPLANTACAO = 4;
    const TIPO_ARTE = 5;
    const TIPO_DIVERSOS = 6;

    // prioridades
    const PRIORIDADE_ALTA = 1;
    const PRIORIDADE_NORMAL = 2;
    const PRIORIDADE_BAIXA = 3;
    const PRIORIDADE_MINIMA = 4;
    /* quero uma lista pequena de prioridades que tenham um significado claro.
     * + alta: resolução imediata, preferencial
     * + normal: resolução em alguns dias
     * + baixa: resolução em uma semana ou mais
     * + mínima: resolução sem prazo
     * ~ a duração exata dos prazos de acordo com prioridade deve ser definida em uma tabela de SLA
     */

    // status
    const STATUS_TRIAGEM = 1;
    const STATUS_CONFIRMADO = 2;
    const STATUS_EM_ANDAMENTO = 3;
    const STATUS_PAUSADO = 4;
    const STATUS_CTRL_QUALIDADE = 5;
    const STATUS_HOMOLOGADO = 6; // = homologado
    const STATUS_LIBERADO = 7;
    const STATUS_INVALIDO = 8;
    const STATUS_DUPLICADO = 9;
    const STATUS_NAO_RESOLVERA = 10;
    /* a numeração dos status tem um sentido: do "mais tenso" (mais novo)
     * ... ao "menos tenso" (perto de resolver, resolvido ou descartado por motivo tal)
     * o código numérico do status pode ser usado como ordenação
     * (apesar da ordenação principal ser a prioridade)
     */

    // classificação do chamado
    public $tipo;
    public $status;
    public $prioridade;
    public $after;

    // "propriedade" do chamado: sistema, cliente, módulo... (cada um desses tem que ter uma tabelinha)
    public $cliente;            // nome da empresa pra quem o serviço é prestado. (ligar com usuário -- tabela pessoa!)
    public $produto;            // o SISTEMA ou PROJETO
    public $componente;            // o MÓDULO, REPOSITÓRIO ou CONTEXTO

    // responsabilidade do chamado
    public $responsavel;        // quem está tratando (interno)
    public $reporter;            // quem reportou (usuário)
    public $contato_nome;        // com quem tratar (cliente)
    public $contato_email;        // ||
    public $contato_telefone;    // ||
    public $copia_carbono;        // quem deve receber alterações deste chamado (implementar depois***)

    // especificação breve do chamado (descrição segue em histórico)
    public $assunto;

    // dados para controle
    public $prazo;                // data limite
    public $atraso;                // dias de atraso
    public $duplicado;            // referência da duplicidade, se houver

    // referências para gui
    public $cod_tipo;
    public $cod_status;
    public $cod_prioridade;
    public $cod_cliente;
    public $cod_produto;
    public $cod_componente;
    public $cod_responsavel;
    public $cod_reporter;
    public $tipo_abrev;
    public $data_abertura;
    public $data_atualizacao;
    public $contador;
    public $historico;

    /* a mensagem do e-mail pode ser a mesma que o histórico;
     * no entanto, o histórico deve ser cadastrado separadamente
     */
    public $mensagem_email;

    // --------------------------------------------------------------------------------------
    // métodos públicos
    public function cadastra()
    {
        global $conexao;

        // palavras filtradas (assunto)
        $filtro = array("urgente", "urgência", "urgencia", "importante",
            "prioridade", "prioritário", "prioritario", "favor");
        $substituidas = 0;

        foreach ($filtro as $palavra) {
            if (stripos($this->assunto, $palavra) !== false) {
                $this->assunto = str_ireplace($palavra, "", $this->assunto);
                $substituidas++;
            }
        }

        // all caps (assunto)
        $caps = strlen(preg_replace('![^A-Z]+!', '', $this->assunto));
        $total = strlen($this->assunto);
        $ratio = $caps / $total;
        if ($ratio > 0.5) $this->assunto = formataCase($this->assunto);

        // mensagens de validação
        if (!__FILTRO_SILENCIOSO__) {
            if ($substituidas > 0) {
                mensagem("Assunto possui uma ou mais palavras filtradas.
					Por favor, limite o assunto ao escopo do problema para que possamos atendê-lo melhor.",
                    MSG_AVISO);
            }

            if ($ratio > 0.5) {
                mensagem("Seu assunto será formatado para facilitar a leitura.", MSG_AVISO);
            }
        }

        // trata defaults
        if (empty($this->tipo)) $this->tipo = self::TIPO_SUPORTE;
        if (empty($this->status)) $this->status = self::STATUS_TRIAGEM;
        if (empty($this->prioridade)) $this->prioridade = self::PRIORIDADE_NORMAL;
        if (empty($this->cod_reporter)) $this->cod_reporter = $_SESSION["ID"];
        $this->handle = newHandle("K_CHAMADOS", $conexao);

        // cliente padrão
        if ($this->cliente <= 2) {
            // default
            $this->cliente = 2;

            // tenta encontrar o cnpj no cadastro
            preg_match("(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})", $this->mensagem_email, $match);

            if (!empty($match)) {
                $sql = "SELECT HANDLE, NOME FROM K_FN_PESSOA WHERE CPFCNPJ = :match";
                $stmt = $conexao->prepare($sql);
                $stmt->bindValue(":match", $match[0]);
                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_OBJ);

                if (!empty($resultado->HANDLE)) {
                    $this->cliente = $resultado->HANDLE;

                    mensagem("Cliente identificado como: " . formataCase($resultado->NOME, true));
                }
            }
        }

        $stmt = $this->insertStatement("K_CHAMADOS",
            array(
                "HANDLE" => $this->handle,
                "TIPO" => $this->cod_tipo,
                "STATUS" => $this->cod_status,
                "PRIORIDADE" => $this->cod_prioridade,
                "CLIENTE" => validaVazio($this->cod_cliente),
                "PRODUTO" => validaVazio($this->cod_produto),
                "COMPONENTE" => $this->componente,
                "RESPONSAVEL" => validaVazio($this->cod_responsavel),
                "REPORTER" => validaVazio($this->cod_reporter),
                "CONTATONOME" => $this->contato_nome,
                "CONTATOEMAIL" => $this->contato_email,
                "CONTATOTELEFONE" => $this->contato_telefone,
                "COPIACARBONO" => $this->copia_carbono,
                "ASSUNTO" => trim($this->assunto),
                "PRAZO" => $this->prazo,
                "DUPLICADO" => $this->duplicado,
                "AFTER" => $this->after,
                "FILIAL" => __FILIAL__
            ));

        retornoPadrao($stmt, "Seu chamado foi aberto com o número de protocolo <b>#{$this->handle}</b>", "Não foi possível abrir seu chamado");
    }

    public function atualiza($lazy = false)
    {
        if ($this->cod_status > self::STATUS_TRIAGEM) {
            // valida data de prazo
            if ($this->cod_status < self::STATUS_CTRL_QUALIDADE && empty($this->prazo)) {
                mensagem("Por favor, informe uma data de previsão para resolução ou visita.", MSG_ERRO);
                finaliza();
            }

            // valida responsável (just in case)
            if (empty($this->cod_responsavel)) {
                mensagem("Por favor, informe o usuário responsável", MSG_ERRO);
                finaliza();
            }
        }

        // valida cliente padrão
        if ($this->cod_cliente <= 2) {
            mensagem("Por favor, indique o cliente final (chamado está com Cliente Padrão ou Empresa Padrão)", MSG_ERRO);
            finaliza();
        }

        $stmt = $this->updateStatement("K_CHAMADOS",
            array(
                "HANDLE" => $this->handle,
                "TIPO" => $this->cod_tipo,
                "STATUS" => $this->cod_status,
                "PRIORIDADE" => $this->cod_prioridade,
                "CLIENTE" => validaVazio($this->cod_cliente),
                "PRODUTO" => validaVazio($this->cod_produto),
                "COMPONENTE" => $this->componente,
                "RESPONSAVEL" => validaVazio($this->cod_responsavel),
                "CONTATONOME" => $this->contato_nome,
                "CONTATOEMAIL" => $this->contato_email,
                "CONTATOTELEFONE" => $this->contato_telefone,
                "COPIACARBONO" => $this->copia_carbono,
                "ASSUNTO" => trim($this->assunto),
                "PRAZO" => $this->prazo,
                "AFTER" => $this->after,
                "DUPLICADO" => $this->duplicado
            ));

        if (!$lazy) {
            retornoPadrao($stmt, "Chamado atualizado com sucesso", "Não foi atualizar o chamado");

            if (!empty($this->cod_responsavel)) {
                // notifica o responsável
                $chat = new Chat();
                $chat->texto = "Atualização no chamado #{$this->handle} - \"{$this->assunto}\", no qual você foi marcado como responsável.";

                $chatfile = new ChatFile($this->cod_responsavel);
                $chatfile->escreve($chat);
            }

            // notifica o reporter
            if ($this->cod_reporter != $this->cod_responsavel) {
                $chat = new Chat();
                $chat->texto = "Atualização no chamado #{$this->handle} - \"{$this->assunto}\", aberto por você.";

                $chatfile = new ChatFile($this->cod_reporter);
                $chatfile->escreve($chat);
            }
        }
    }

    public function valida()
    {
        $stmt = $this->updateStatement("K_CHAMADOS",
            array(
                "HANDLE" => $this->handle,
                "STATUS" => self::STATUS_HOMOLOGADO
            ));

        retornoPadrao($stmt, "Resolução aceita. O chamado será removido da sua lista de pendências.", "Não foi possível validar o chamado");
    }

    public function getNomeTipo($tipo)
    {
        switch ($tipo) {
            case self::TIPO_SUPORTE:
                return "Suporte";
            case self::TIPO_DESENVOLVIMENTO:
                return "Bugfix";
            case self::TIPO_CUSTOMIZACAO:
                return "Customização";
            case self::TIPO_IMPLANTACAO:
                return "Implantação";
            case self::TIPO_ARTE:
                return "Arte/Web";
            case self::TIPO_DIVERSOS:
                return "Diversos";
            default:
                return "Indefinido";
        }
    }

    public function getNomeTipoAbreviado($tipo)
    {
        switch ($tipo) {
            case self::TIPO_SUPORTE:
                return "Sup";
            case self::TIPO_DESENVOLVIMENTO:
                return "Bug";
            case self::TIPO_CUSTOMIZACAO:
                return "Cst";
            case self::TIPO_IMPLANTACAO:
                return "Imp";
            case self::TIPO_ARTE:
                return "Art";
            case self::TIPO_DIVERSOS:
                return "Div"; // não confundir "dev" com "div"!
            default:
                return "Ind";
        }
    }

    public static function getNomePrioridade($prioridade)
    {
        switch ($prioridade) {
            case self::PRIORIDADE_ALTA:
                return "Alta";
            case self::PRIORIDADE_NORMAL:
                return "Normal";
            case self::PRIORIDADE_BAIXA:
                return "Baixa";
            case self::PRIORIDADE_MINIMA:
                return "Quando puder";
            default:
                return "Indefinida";
        }
    }

    public static function getNomeStatus($status, $lista = false)
    {
        $temp = array(
            self::STATUS_TRIAGEM => "Triagem",
            self::STATUS_CONFIRMADO => "Confirmado",
            self::STATUS_EM_ANDAMENTO => "Em andamento",
            self::STATUS_PAUSADO => "Pausado",
            self::STATUS_CTRL_QUALIDADE => "Ctrl. qualidade",
            self::STATUS_HOMOLOGADO => "Homologado",
            self::STATUS_LIBERADO => "Liberado",
            self::STATUS_INVALIDO => "Inválido",
            self::STATUS_DUPLICADO => "Duplicado",
            self::STATUS_NAO_RESOLVERA => "Não resolverá"
        );

        if ($lista) {
            return $temp;
        }

        return $temp[$status];
    }
}