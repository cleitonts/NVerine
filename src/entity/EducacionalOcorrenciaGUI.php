<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/10/2019
 * Time: 10:23
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalOcorrenciaGUI extends ObjectGUI
{
// construtor obrigatório
    public function __construct($handle = 0)
    {
        $this->header = array("Registro", "Data", "Tipo", "Severidade", "Envolvidos", "Responsável");
    }

    // métodos públicos
    public function getCampo($linha, $coluna)
    {
        $item = $this->itens[$linha];

        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo(converteDataSqlOrdenada($item->data)),
            campo($item->tipo),
            campo($item->severidade),
            campo($item->envolvidos),
            campo($item->usuario)
        ));
    }

    public function fetch()
    {
        global $conexao;

        $this->itens = array();
        $where = "WHERE 1 = 1 \n ";

        if (!empty($this->pesquisa["pesq_codigo"])) $where .= "AND O.HANDLE = :handle \n";
        if (!empty($this->pesquisa["pesq_usuario"])) $where .= "AND O.USUARIO = :usuario \n";
        if (!empty($this->pesquisa["pesq_tipo"])) $where .= "AND O.TIPO = :tipo \n";
        if (!empty($this->pesquisa["pesq_data_inicial"]) && !empty($this->pesquisa["pesq_data_final"])) {
            $where .= "AND O.DATA >= :datainicial AND O.DATA <= :datafinal \n";
        }

        $sql = "SELECT {$this->top} O.*,
				T.NOME AS NOMETIPO, T.SEVERIDADE,
				U.NOME AS NOMEUSUARIO,
					(SELECT TOP 1 P.NOME
					FROM K_FN_PESSOA P
					INNER JOIN K_OCORRENCIAPESSOA OP
					ON OP.PESSOA = P.HANDLE
					WHERE OP.OCORRENCIA = O.HANDLE
					ORDER BY OP.HANDLE ASC
					)
				PESSOA,
					(SELECT COUNT(*) FROM K_OCORRENCIAPESSOA OP
					WHERE OP.OCORRENCIA = O.HANDLE)
				ENVOLVIDOS
				
				FROM K_OCORRENCIA O
				LEFT JOIN K_TIPOOCORRENCIA T ON O.TIPO = T.HANDLE
				LEFT JOIN K_PD_USUARIOS U ON O.USUARIO = U.HANDLE
				{$where} 
				ORDER BY O.HANDLE DESC";

        $stmt = $conexao->prepare($sql);

        // se houve filtros de pesquisa definidos, precisamos mapear aqui
        if (!empty($this->pesquisa["pesq_codigo"])) $stmt->bindValue(":handle", $this->pesquisa["pesq_codigo"]);
        if (!empty($this->pesquisa["pesq_usuario"])) $stmt->bindValue(":usuario", $this->pesquisa["pesq_usuario"]);
        if (!empty($this->pesquisa["pesq_tipo"])) $stmt->bindValue(":tipo", $this->pesquisa["pesq_tipo"]);
        if (!empty($this->pesquisa["pesq_data_inicial"]) && !empty($this->pesquisa["pesq_data_final"])) {
            $stmt->bindValue(":datainicial", converteData($this->pesquisa["pesq_data_inicial"]));
            $stmt->bindValue(":datafinal", converteData($this->pesquisa["pesq_data_final"]));
        }

        $stmt->execute();

        // recuperamos todos os itens do statement executado
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if (!empty($f)) {
            foreach ($f as $r) {
                $item = new EducacionalOcorrenciaETT();
                $item->cont = $i;

                $item->handle = $r->HANDLE;
                $item->data = $r->DATA;
                $item->usuario = formataCase($r->NOMEUSUARIO, true);
                $item->cod_usuario = $r->USUARIO;
                $item->tipo = formataCase($r->NOMETIPO);
                $item->cod_tipo = $r->TIPO;
                $item->severidade = $r->SEVERIDADE;
                $item->notas = $r->NOTAS;

                $item->envolvidos = formataCase($r->PESSOA, true);
                if ($r->ENVOLVIDOS > 1) $item->envolvidos .= " +" . ($r->ENVOLVIDOS - 1) . "...";
                if (empty($item->envolvidos)) $item->envolvidos = "(Ninguém)";

                // se for único, buscar a lista de pessoas envolvidas
                if (!empty($this->pesquisa["pesq_codigo"])) {
                    $sql = "SELECT P.NOME, P.HANDLE, OP.OBSERVACOES
							FROM K_OCORRENCIAPESSOA OP
							INNER JOIN K_FN_PESSOA P ON OP.PESSOA = P.HANDLE
							WHERE OP.OCORRENCIA = '{$item->handle}'";
                    $stmt = $conexao->prepare($sql);
                    $stmt->execute();
                    $item->pessoas = $stmt->fetchAll(PDO::FETCH_OBJ);
                }

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }
}