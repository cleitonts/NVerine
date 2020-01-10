<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 14:01
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalMatriculaGUI extends ObjectGUI
{
    // indicador de consulta de vínculos
    protected $vinculos = false;

    // construtor obrigatório
    public function __construct($handle = null) {
        $this->header = array(
            "Matrícula", "Aluno", "Escola", "Turma", "Turno", "Ano", "Período", "Situação",
            "Data nascimento", "Responsável"
        );
    }

    // métodos públicos
    public function getCampo($linha, $coluna) {
        $item = $this->itens[$linha];

        // trocar para a função
        switch($item->turno) {
            case "M": $texto_turno = "Matutino"; break;
            case "V": $texto_turno = "Vespertino"; break;
            case "I": $texto_turno = "Integral"; break;
            default: $texto_turno = $item->turno;
        }

        $situacao = $item->historico;
        if(empty($situacao)) $situacao = $item->ativo == "S" ? "ATIVO" : "INATIVO";

        return $this->campos($coluna, array(
            campo($item->numero_matricula_aluno),
            campo($item->aluno),
            campo($item->filial),
            campo($item->turma."<span class='escondido'>({$item->cod_turma})</span>"),
            campo($texto_turno),
            campo($item->ano, "numerico"),
            campo($item->periodo, "numerico"),
            //campo(EducacionalTurmaETT::($item->cod_segmento)),
            campo($situacao),
            campo(converteDataSqlOrdenada($item->data_nascimento)),
            campo($item->responsavel)
        ));
    }

    public function fetch() {
        global $conexao;

        $this->itens = array();
        // $where = "WHERE ".filtraFilial("T.FILIAL", "Educacional");
        $where = "WHERE 1 = 1 \n";

        // pesquisa pelo handle da matrícula
        if(!empty($this->pesquisa["pesq_codigo"])) $where .= "AND R.HANDLE = :handle \n";

        // pesquisa por turma
        if(!empty($this->pesquisa["pesq_turma"])) $where .= "AND R.TURMA = :turma \n";

        // pesquisa por aluno
        if(!empty($this->pesquisa["pesq_aluno"])) $where .= "AND R.ALUNO = :aluno \n";

        // pesquisa por matrículas ativas
        if(!empty($this->pesquisa["pesq_vigente"]))	$where .= "AND T.ATUAL = :vigente \n";
        if(!empty($this->pesquisa["pesq_ativo"])) $where .= "AND R.ATIVO = 'S' \n";

        // pesquisa por datas (ano?)
        if(!empty($this->pesquisa["pesq_data_inicial"])) $where .= "AND T.ANO >= :inicio \n";
        if(!empty($this->pesquisa["pesq_data_final"])) $where .= "AND T.ANO <= :fim \n";

        $sql = "SELECT {$this->top} R.*, 
				P.NOME AS NOMEALUNO, P.HANDLE AS HANDLEALUNO, P.DATAADESAO, P.SEXO, P.DADOSCENSO, P.NASCIMENTO,
				T.NOME AS NOMETURMA, T.ANO, T.PERIODO, T.TURNO AS TURNOALT, T.SEGMENTO, T.CARGAHORARIA, T.ATUAL AS VIGENTE,
				F.NOME AS FILIAL, E.SIGLA AS ESTADO, M.NOME AS CIDADE,
				
				(	SELECT TOP 1 RESP.NOME
					FROM K_FN_PESSOA RESP, K_FN_PESSOAVINCULO V
					WHERE V.PAI = RESP.HANDLE
					AND V.FILHO = P.HANDLE
					AND V.RESPONSAVEL = 'S'
				) RESPONSAVEL
				
				FROM K_TURMAALUNO R 
				LEFT JOIN K_FN_PESSOA P ON R.ALUNO = P.HANDLE
				LEFT JOIN K_TURMA T ON R.TURMA = T.HANDLE
				LEFT JOIN K_FN_FILIAL F ON T.FILIAL = F.HANDLE
				LEFT JOIN ESTADOS E ON F.ESTADO = E.HANDLE
				LEFT JOIN MUNICIPIOS M ON F.CIDADE = M.HANDLE
				{$where}
				ORDER BY T.ANO DESC, T.PERIODO DESC, T.NOME ASC, R.HANDLE ASC";

        $stmt = $conexao->prepare($sql);

        // se houve filtros de pesquisa definidos, precisamos mapear aqui
        if(!empty($this->pesquisa["pesq_codigo"])) $stmt->bindValue(":handle", $this->pesquisa["pesq_codigo"]);
        if(!empty($this->pesquisa["pesq_turma"])) $stmt->bindValue(":turma", $this->pesquisa["pesq_turma"]);
        if(!empty($this->pesquisa["pesq_aluno"])) $stmt->bindValue(":aluno", $this->pesquisa["pesq_aluno"]);
        if(!empty($this->pesquisa["pesq_vigente"])) $stmt->bindValue(":vigente", $this->pesquisa["pesq_vigente"]);
        if(!empty($this->pesquisa["pesq_data_inicial"])) $stmt->bindValue(":inicio", $this->pesquisa["pesq_data_inicial"]);
        if(!empty($this->pesquisa["pesq_data_final"])) $stmt->bindValue(":fim", $this->pesquisa["pesq_data_final"]);

        $stmt->execute();

        // recuperamos todos os itens do statement executado
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if(!empty($f)) {
            foreach($f as $r) {
                $item = new EducacionalMatriculaETT();

                $item->handle = $r->HANDLE;
                $item->aluno = formataCase($r->NOMEALUNO, true);
                $item->cod_aluno = $r->ALUNO;
                $item->cod_turma = $r->TURMA;
                $item->turma = $r->NOMETURMA;

                // acha a situação no meio do json
                $json = json_decode($r->DADOSCENSO);
                $item->situacao = $json->motivoTransf;

                $item->ativo = $r->ATIVO;
                $item->ano = $r->ANO;
                $item->periodo = $r->PERIODO;
                $item->cod_segmento = $r->SEGMENTO;
                $item->vigente = $r->VIGENTE;
                $item->data_entrada = $r->DATAENTRADA;
                $item->data_saida = $r->DATASAIDA;
                $item->historico = $r->HISTORICO;
                $item->conceito = $r->CONCEITO;
                $item->numero_matricula_aluno = left($r->DATAADESAO, 4)."/".insereZeros($r->HANDLEALUNO, 6);
                $item->filial = formataCase($r->FILIAL, true);
                $item->carga_horaria = $r->CARGAHORARIA;
                $item->turno = $r->TURNO;
                $item->data_nascimento = $r->NASCIMENTO;
                $item->sexo = $r->SEXO;
                $item->responsavel = formataCase($r->RESPONSAVEL, true);

                // fallback de turno do aluno (pela turma)
                if(empty($item->turno)) $item->turno = $r->TURNOALT;

                // soft-campos de histórico escolar
                if(strpos($item->historico, "|") !== false) {
                    $partes = explode("|", $item->historico);
                    $item->escola = $partes[0];
                    $item->municipio = $partes[1];
                    $item->estado = $partes[2];
                    $item->serie = $partes[3];
                    $item->total_aulas = "";
                    $item->total_faltas = "";
                }
                else {
                    $item->escola = $item->filial;
                    $item->municipio = formataCase($r->CIDADE, true);
                    $item->estado = $r->ESTADO;
                    $item->serie = left($item->turma, 1);
                    $item->total_aulas = "";
                    $item->total_faltas = "";
                }

                // busca todos os vínculos do aluno?
                if($this->vinculos) {
                    $vinculo = new PessoaVinculoGUI();
                    $vinculo->top = "TOP 10";
                    $vinculo->pessoa = $item->cod_aluno;
                    // $vinculo->pesquisa["pesq_responsavel"] = 1;
                    $vinculo->fetch();

                    $item->vinculos = $vinculo->itens;

                    // busca endereço do primeiro vinculado
                    if(isset($item->vinculos[0])) {
                        $pessoa = new PessoaGUI();
                        $pessoa->pesquisa["pesq_codigo"] = $item->vinculos[0]->cod_pai;
                        $pessoa->top = "TOP 10";
                        $pessoa->fetch();
                        $item->vinculos[0]->endereco = $pessoa->itens[0]->endereco;
                        $item->vinculos[0]->telefone = $pessoa->itens[0]->telefone;
                    }
                }

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }
}