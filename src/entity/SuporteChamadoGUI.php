<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 31/07/2019
 * Time: 17:36
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class SuporteChamadoGUI extends ObjectGUI
{
// propriedades para estatísticas
    public $totalizadores;        // array de totalizadores por propriedade do chamado no BD
    public $dias_resolucao;        // somatório dos dias para resolver todos os chamados
    public $resolvidos;            // contador dos chamados resolvidos
    public $encerrados;            // contator dos chamados encerrados
    public $vencidos;            // array com o número dos chamados que precisam ser encerrados automaticamente

    // construtor
    public function __construct($handle = null)
    {
        // cabeçalho padrão
        $this->header = array(
            "Protocolo", "Tipo", "Status", "Prioridade", "Atraso", "Cliente", "Assunto", "Produto", "Responsável",
            "Data abertura", "Data última atividade", "Data prazo", "Reportou", "Duplicado"
        );

        $this->header_abrev = array(
            "Num.", "Tipo", "Status", "Prioridade", "Atraso", "Cliente", "Assunto", "Produto", "Responsável",
            "Aberto em", "Ativo em", "Prazo", "Reportou", "#Dup."
        );

        $this->exibe = array(0, 1, 2, 3, 5, 6, 7, 8, 9, 10, 11);

        // propriedades
        $this->totalizadores = array();
        $this->vencidos = array();
    }

    // métodos públicos
    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo($item->tipo),
            campo($item->status, "bg-color bg{$item->cod_status}"),
            campo("{$item->cod_prioridade} - {$item->prioridade}"),
            campo($item->atraso, $item->atraso > 0 ? "atrasado numerico" : "numerico"),
            campo(left($item->cliente, 30)),
            campo($item->assunto),
            campo($item->produto),
            campo($item->responsavel),
            campo(converteDataSqlOrdenada($item->data_abertura)),
            campo(converteDataSqlOrdenada($item->data_atualizacao)),
            campo(converteDataSqlOrdenada($item->prazo)),
            campo($item->reporter),
            campo($item->duplicado)
        ));
    }

    public function fetch()
    {
        global $conexao;
        global $permissoes;

        // se usuário é suporte, pode olhar todos os chamados
        if ($permissoes->libera("Equipe de suporte") || $permissoes->libera("Suporte")) {
            $where = "WHERE " . filtraFilial("C.FILIAL", "Suporte", true);
        } // senão, filtra os chamados do usuário logado
        else {
            $sql = "SELECT CLIENTE FROM K_PD_USUARIOS WHERE HANDLE = '{$_SESSION["ID"]}'";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $f = $stmt->fetch(PDO::FETCH_OBJ);

            $where = "WHERE C.CLIENTE = '{$f->CLIENTE}'\n";
        }

        // filtros de pesquisa
        if (!empty($this->pesquisa["pesq_chamado"])) $where .= "AND C.HANDLE = :chamado\n";
        if (!empty($this->pesquisa["pesq_responsavel"])) $where .= "AND C.RESPONSAVEL = :responsavel\n";
        if (!empty($this->pesquisa["pesq_produto"])) $where .= "AND C.PRODUTO = :produto\n";
        if (!empty($this->pesquisa["pesq_cod_cliente"])) $where .= "AND C.CLIENTE = :cliente\n";
        if (!empty($this->pesquisa["pesq_assunto"])) $where .= "AND C.ASSUNTO LIKE :assunto\n";
        if (!empty($this->pesquisa["pesq_duplicado"])) $where .= "AND C.DUPLICADO = :duplicado\n";

        // status múltiplos
        $status = "";
        for ($i = 1; $i <= SuporteChamadoETT::MAX_STATUS; $i++) {
            if (isset($this->pesquisa["pesq_status_{$i}"])) $status .= "{$i}, ";
        }
        $status = trim($status, ", ");

        if (!empty($status))
            $where .= "AND C.STATUS IN ({$status})\n";
        elseif (empty($this->pesquisa["pesq_chamado"]) && empty($this->pesquisa["pesq_duplicado"]))
            $where .= "AND C.STATUS <= 5\n"; // some da lista padrão quando for validado

        // tipos múltiplos
        $tipos = "";
        for ($i = 1; $i <= SuporteChamadoETT::MAX_TIPOS; $i++) {
            if (isset($this->pesquisa["pesq_tipo_{$i}"])) $tipos .= "{$i}, ";
        }
        $tipos = trim($tipos, ", ");

        if (!empty($tipos)) $where .= "AND C.TIPO IN ({$tipos})\n";

        // puxa dados
        $sql = "SELECT {$this->top} 
				C.*, RS.NOME AS NOMERESPONSAVEL, RP.NOME AS NOMEREPORTER, P.NOME AS NOMEPRODUTO, CL.NOME AS NOMECLIENTE,
				
				-- data abertura
				(
					SELECT TOP 1 DATA FROM K_CHAMADOHISTORICO H
					WHERE H.CHAMADO = C.HANDLE
					ORDER BY H.HANDLE ASC
				) AS DATAABERTURA,
				
				-- conta numero de alterações
				( SELECT COUNT(H.HANDLE) FROM K_CHAMADOHISTORICO H 
				  WHERE H.CHAMADO = C.HANDLE) AS CONTADOR,
				  
				-- data atualização
				(
					SELECT TOP 1 DATA FROM K_CHAMADOHISTORICO H
					WHERE H.CHAMADO = C.HANDLE
					ORDER BY H.HANDLE DESC
				) AS DATAATUALIZACAO,
				
				-- data resolução
				(
					SELECT TOP 1 DATA FROM K_CHAMADOHISTORICO H
					WHERE H.CHAMADO = C.HANDLE
					AND H.STATUS >= 5
					ORDER BY H.HANDLE ASC
				) AS DATARESOLUCAO
				
				FROM K_CHAMADOS C
				LEFT JOIN K_PD_USUARIOS RS ON C.RESPONSAVEL = RS.HANDLE
				LEFT JOIN K_PD_USUARIOS RP ON C.REPORTER = RP.HANDLE
				LEFT JOIN PD_PRODUTOS P ON C.PRODUTO = P.HANDLE
				LEFT JOIN K_FN_PESSOA CL ON C.CLIENTE = CL.HANDLE
				{$where}
				AND C.HANDLE > 10
				ORDER BY C.STATUS ASC, C.AFTER DESC, C.HANDLE";
        $stmt = $conexao->prepare($sql);

        if (!empty($this->pesquisa["pesq_chamado"])) $stmt->bindValue(":chamado", $this->pesquisa["pesq_chamado"]);
        if (!empty($this->pesquisa["pesq_responsavel"])) $stmt->bindValue(":responsavel", $this->pesquisa["pesq_responsavel"]);
        if (!empty($this->pesquisa["pesq_produto"])) $stmt->bindValue(":produto", $this->pesquisa["pesq_produto"]);
        if (!empty($this->pesquisa["pesq_cod_cliente"])) $stmt->bindValue(":cliente", $this->pesquisa["pesq_cod_cliente"]);
        if (!empty($this->pesquisa["pesq_assunto"])) $stmt->bindValue(":assunto", "%" . $this->pesquisa["pesq_assunto"] . "%");
        if (!empty($this->pesquisa["pesq_duplicado"])) $stmt->bindValue(":duplicado", $this->pesquisa["pesq_duplicado"]);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // insere no array
        $i = 0;

        foreach ($f as $r) {
            $item = new SuporteChamadoETT();
            $item->cont = $i;

            $item->handle = $r->HANDLE;
            $item->after = $r->AFTER;
            $item->tipo = $item->getNomeTipo($r->TIPO);
            $item->tipo_abrev = $item->getNomeTipoAbreviado($r->TIPO);
            $item->cod_tipo = $r->TIPO;
            $item->status = insereZeros($r->STATUS, 2) . " - " . $item->getNomeStatus($r->STATUS);
            $item->cod_status = $r->STATUS;
            $item->prioridade = $item->getNomePrioridade($r->PRIORIDADE);
            $item->cod_prioridade = $r->PRIORIDADE;
            $item->cliente = formataCase($r->NOMECLIENTE, true);
            $item->cod_cliente = $r->CLIENTE;
            $item->produto = formataCase($r->NOMEPRODUTO, true);
            $item->cod_produto = $r->PRODUTO;
            $item->componente = $r->COMPONENTE;
            $item->assunto = $r->ASSUNTO;
            $item->responsavel = empty($r->NOMERESPONSAVEL) ? "--" : formataCase($r->NOMERESPONSAVEL, true);
            $item->cod_responsavel = $r->RESPONSAVEL;
            $item->prazo = $r->PRAZO;
            $item->atraso = empty($item->prazo) || $item->cod_status >= SuporteChamadoETT::STATUS_CTRL_QUALIDADE ? "--" : insereZeros(diasAtraso($r->PRAZO), 2);
            $item->reporter = formataCase($r->NOMEREPORTER, true);
            $item->cod_reporter = $r->REPORTER;
            $item->contato_nome = $r->CONTATONOME;
            $item->contato_email = $r->CONTATOEMAIL;
            $item->contato_telefone = $r->CONTATOTELEFONE;
            $item->copia_carbono = $r->COPIACARBONO;
            $item->duplicado = noZeroes($r->DUPLICADO);
            $item->data_abertura = $r->DATAABERTURA;
            $item->data_atualizacao = $r->DATAATUALIZACAO;
            $item->contador = $r->CONTADOR;

            // preenche/soma totalizadores
            $this->totalizadores["Clientes"][$item->cliente]++;
            $this->totalizadores["Produtos"][$item->produto]++;
            $this->totalizadores["Tipos"][$item->tipo]++;
            $this->totalizadores["Status"][$item->status]++;
            $this->totalizadores["Responsáveis"][$item->responsavel]++;
            $this->totalizadores["Reporters"][$item->reporter]++;

            if (!empty($this->pesquisa["pesq_chamado"])) {
                $historico = new SuporteHistoricoGUI();
                $historico->chamado = $item->handle;
                $historico->fetch();
                $item->historico = $historico->itens;
            }

            if (!empty($r->DATARESOLUCAO)) {
                // calcula intervalos de dias
                $dias_resolucao = diasEntre($r->DATAABERTURA, $r->DATARESOLUCAO);
                $dias_vencimento = diasEntre($r->DATARESOLUCAO, agora());

                // insere nos vencidos?
                if ($item->status == SuporteChamadoETT::STATUS_CTRL_QUALIDADE && $dias_vencimento >= 30) $this->vencidos[] = $item->handle;

                // soma contadores para médias
                $this->dias_resolucao += $dias_resolucao;
                $this->resolvidos++;
                if ($item->status >= SuporteChamadoETT::STATUS_HOMOLOGADO) $this->encerrados++;
            }

            array_push($this->itens, $item);
            $i++;
        }
    }
}