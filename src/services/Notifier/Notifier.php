<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:13
 */

namespace src\services\Notifier;


use src\services\Transact\ExtPDO as PDO;

class Notifier {
    // propriedades
    public $alertas;
    public $mensagens;
    public $usuario;

    /* m�todos privados
     * ================
     *
     * puxa alertas de vencimentos -- registros para [amanh�,] hoje e vencidos
     * par�metros de configura��o por array! no futuro, esses par�metros podem ser informados pelas pr�prias classes
     *
     * @tabela: nome da tabela a pesquisar
     * @data: nome do campo do per�odo a filtrar
     * @usuario: nome do campo do usu�rio a filtrar
     * @titulo: nome do campo do t�tulo (nome) a informar (poss�vel passar string)
     * @handle: nome do campo do handle para gera��o do link
     * @link: prefixo para forma��o do link
     * @icone: classe opcional font-awesome para o �cone do elemento
     * @sql_adicional: inje��o de cl�usulas condicionais extras -- geralmente se passa um STATUS n�o resolvido
     */
    private function vencimentos($params = array()) {
        global $conexao;

        // alertas s�o para amanh� ou para hoje apenas?
        $limite = amanha();

        // puxa vencimentos
        $sql = "SELECT TOP 3 -- parametrize limite aqui
				{$params['data']} AS DATA, {$params['titulo']} AS TITULO, {$params['handle']} AS HANDLE
				FROM {$params['tabela']} \n";

        if(isset($params["usuario"])) {
            $sql .= "WHERE {$params['usuario']} = {$_SESSION['ID']}
					AND {$params['data']} <= '".converteData($limite)."' \n";
        }
        else {
            $sql .= "WHERE {$params['data']} <= '".converteData($limite)."' \n";
        }

        $sql .= "{$params['sql_adicional']}
				ORDER BY DATA DESC";

        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        if(!empty($f)) {
            foreach($f as $r) {
                // monta chap�u: se � para amanh�, hoje ou atraso
                $data = converteDataSql($r->DATA);
                if($data == amanha()) 		$chapeu = "Amanh�";
                elseif($data == hoje())		$chapeu = "Hoje";
                else 						$chapeu = "Atraso";

                // adiciona elemento a alertas
                $elemento = new Element("<strong>{$chapeu}:</strong> {$r->TITULO}");
                $elemento->link = "{$params['link']}{$r->HANDLE}";
                $elemento->data = converteDataSqlOrdenada($r->DATA);
                if(isset($params["icone"])) $elemento->icone = $params["icone"];

                $this->alertas[] = $elemento;
            }
        }
    }

    // m�todos p�blicos
    public function __construct($usuario = null) {
        $this->alertas = array();
        $this->mensagens = array();

        /* permite configurar o usu�rio que vai gerar as notifica��es
         * �til se voc� quer o debug das mensagens do 1 (gestorweb)
         */
        if(!empty($usuario)) {
            $this->usuario = $usuario;
        }
        else {
            $this->usuario = $_SESSION["ID"];
        }
    }

    public function fetch() {
        global $permissoes;

        // ----------------------------------------------------------------
        // contas a pagar
        if(!$permissoes->libera("Financeiro")) {
            $this->vencimentos(array(
                "tabela" => "K_FINANCEIRO",
                "data" => "DATAVENCIMENTOREAL",
                // "usuario" => "USUARIO", // n�o quero filtrar?
                "titulo" => "NUMERODOC",
                "handle" => "HANDLE",
                "link" => "?pagina=contabil_titulos&pesq_natureza=1&pesq_combo_2=F.HANDLE&pesq_combo_value=",
                "icone" => "money",
                "sql_adicional" => "AND NATUREZA = 1 AND ATIVO = 'S' AND VALORSALDO > 0 AND FILIAL = '" . __FILIAL__ . "'"
            ));

            // contas a receber
            $this->vencimentos(array(
                "tabela" => "K_FINANCEIRO",
                "data" => "DATAVENCIMENTOREAL",
                // "usuario" => "USUARIO", // n�o quero filtrar?
                "titulo" => "NUMERODOC",
                "handle" => "HANDLE",
                "link" => "?pagina=contabil_titulos&pesq_natureza=2&pesq_combo_2=F.HANDLE&pesq_combo_value=",
                "icone" => "money",
                "sql_adicional" => "AND NATUREZA = 2 AND ATIVO = 'S' AND VALORSALDO > 0 AND FILIAL = '".__FILIAL__."'"
            ));
        }



        // suporte
        $this->vencimentos(array(
            "tabela" => "K_CHAMADOS",
            "data" => "PRAZO",
            "usuario" => "RESPONSAVEL",
            "titulo" => "ASSUNTO",
            "handle" => "HANDLE",
            "link" => "?pagina=suporte_chamados&pesq_chamado=",
            "icone" => "support",
            "sql_adicional" => "AND STATUS < 5" // n�o resolvidos
        ));

        // jur�dico
        /*
        $this->vencimentos(array(
            "tabela" => "K_JUR_AGENDA",
            "data" => "DATA",
            "usuario" => "USUARIO",
            "titulo" => "TEXTO",
            "handle" => "PROCESSO",
            "link" => "?pagina=juridico_processos&pesq_processo=",
            "icone" => "balance-scale",
            "sql_adicional" => "AND STATUS <= 2" // n�o realizadas
        ));
        */

        // CRM
        if(!$permissoes->libera("CRM")) {
            $this->vencimentos(array(
                "tabela" => "K_CRM_TAREFAS",
                "data" => "DATA",
                "titulo" => "ASSUNTO",
                "handle" => "NEGOCIACAO",
                "link" => "?pagina=negociacao&pesq_codigo=",
                "icone" => "handshake-o",
                "sql_adicional" => "AND (CONCLUIDO = 'N' OR CONCLUIDO IS NULL)" // n�o conclu�dos
            ));
        }

        // agenda/educacional
        $this->vencimentos(array(
            "tabela" => "K_AGENDA",
            "data" => "DATA",
            "usuario" => "USUARIO",
            "titulo" => "TITULO",
            "handle" => "HANDLE",
            "link" => "?pagina=agenda&retorno=".urlEncode(getUrlRetorno())."&pesq_codigo=",
            "icone" => "clock-o",
            "sql_adicional" => "AND ATIVO = 'S' AND STATUS <= 2 AND FILIAL = " . __FILIAL__ // n�o realizados
        ));

        // di�rio de classe
        global $conexao;

        $sql = "SELECT TOP 1 A.*
				FROM K_AULA A
				LEFT JOIN K_FN_PESSOA P ON A.PROFESSOR = P.HANDLE
				LEFT JOIN K_PD_USUARIOS U ON U.CLIENTE = P.HANDLE
				WHERE U.HANDLE = '{$_SESSION['ID']}'
				ORDER BY A.DATA DESC";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetch(PDO::FETCH_OBJ);

        if(!empty($f->DATA)) {
            $dias = diasAtraso($f->DATA);

            if($dias > 1) {
                $elemento = new Elemento("<strong>Atraso:</strong> Di�rio de classe");
                $elemento->link = "?pagina=educacional_turma";
                $elemento->data = converteDataSqlOrdenada($f->DATA);
                $elemento->icone = "graduation-cap";

                $this->alertas[] = $elemento;
            }
        }

        // ----------------------------------------------------------------
        // carrega mensagens do usu�rio
        $chatfile = new ChatFile($this->usuario);

        if(!empty($chatfile->itens)) {
            foreach($chatfile->itens as $item) {
                // adiciona elemento a mensagens
                $elemento = new Element($item->texto);
                $elemento->link = "?pagina=mensagens&u=".urlencode(getUrlRetorno());
                $elemento->data = $item->data;
                $elemento->icone = "envelope-square";

                $this->mensagens[] = $elemento;
            }
        }
    }
}