<?php
namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

/* ==============================================================================================
 * TO-DO:
 * calcular médias por avaliação
 * calcular total e menção por aluno (regra padrão)
 */
class EducacionalBoletimGUI extends ObjectGUI
{
    // totalizadores
    public $nota_final;

    // construtor obrigatório
    public function __construct($handle = null)
    {
        $this->header = array("Turma", "Período", "Disciplina", "Avaliação", "Aluno", "Nota", "Peso", "Total");

        $this->nota_final = 0;
    }

    // métodos públicos
    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->obj_avaliacao->turma),
            campo($item->obj_avaliacao->periodo . "/" . $item->obj_avaliacao->ano),
            campo($item->obj_avaliacao->disciplina),
            campo($item->obj_avaliacao->nome),
            campo($item->aluno),
            campo($item->nota_revisao, "numerico"),
            campo($item->obj_avaliacao->peso, "numerico"),
            campo($item->nota_pesada, "numerico")
        ));
    }

    public function fetch()
    {
        global $conexao;

        $this->itens = array();
        $where = " WHERE 1 = 1 \n ";

        // monta query de pesquisa
        if (!empty($this->pesquisa["pesq_avaliacao"])) $where .= "AND A.HANDLE = " . $this->pesquisa["pesq_avaliacao"] . " \n";
        if (!empty($this->pesquisa["pesq_codigo"])) $where .= "AND B.HANDLE = " . $this->pesquisa["pesq_codigo"] . " \n";
        if (!empty($this->pesquisa["pesq_turma"])) $where .= "AND A.TURMA = " . $this->pesquisa["pesq_turma"] . " \n";
        if (!empty($this->pesquisa["pesq_segmento"])) $where .= "AND T.SEGMENTO = " . $this->pesquisa["pesq_segmento"] . " \n";
        if (!empty($this->pesquisa["pesq_disciplina"])) $where .= "AND A.DISCIPLINA = " . $this->pesquisa["pesq_disciplina"] . " \n";
        if (!empty($this->pesquisa["pesq_ano"])) $where .= "AND T.ANO = " . $this->pesquisa["pesq_ano"] . " \n";

        // quando pesquisar por turma, aluno não é obrigatório
        $join_aluno = "LEFT JOIN K_BOLETIM B ON B.AVALIACAO = A.HANDLE";

        if (!empty($this->pesquisa["pesq_aluno"])) $join_aluno =
        $join_aluno = "INNER JOIN K_BOLETIM B ON (B.AVALIACAO = A.HANDLE AND B.ALUNO = :aluno)";

        $sql = "SELECT {$this->top} B.*,
				A.HANDLE AS AVALIACAO, A.NOME AS NOMEAVALIACAO, A.PESO, A.TURMA, A.DISCIPLINA, A.DESCRICAO,
				T.NOME AS NOMETURMA, T.ANO, T.PERIODO, T.SEGMENTO,
				D.NOME AS NOMEDISCIPLINA, S.NOME AS NOMESERIE,
				P.NOME AS NOMEALUNO
				
				-- ordem inversa para trazer dados de avaliações sem nota lançada
				FROM K_TURMAAVALIACAO A
				{$join_aluno}
				LEFT JOIN K_DISCIPLINA D ON A.DISCIPLINA = D.HANDLE 
				LEFT JOIN K_TURMA T ON A.TURMA = T.HANDLE
				LEFT JOIN K_SERIE S ON S.HANDLE = T.SERIE
				LEFT JOIN K_FN_PESSOA P ON B.ALUNO = P.HANDLE
				LEFT JOIN K_TURMAALUNO TA ON TA.TURMA = T.HANDLE AND TA.ALUNO = B.ALUNO
				{$where}
				ORDER BY A.DISCIPLINA DESC, A.TURMA DESC, TA.NUMERO ASC, A.HANDLE DESC, P.NOME ASC";
        $stmt = $conexao->prepare($sql);


        // mapeando filtros
        if (!empty($this->pesquisa["pesq_avaliacao"])) $stmt->bindValue(":avaliacao", $this->pesquisa["pesq_avaliacao"]);
        if (!empty($this->pesquisa["pesq_codigo"])) $stmt->bindValue(":handle", $this->pesquisa["pesq_codigo"]);
        if (!empty($this->pesquisa["pesq_aluno"])) $stmt->bindValue(":aluno", $this->pesquisa["pesq_aluno"]);
        if (!empty($this->pesquisa["pesq_turma"])) $stmt->bindValue(":turma", $this->pesquisa["pesq_turma"]);
        if (!empty($this->pesquisa["pesq_disciplina"])) $stmt->bindValue(":disciplina", $this->pesquisa["pesq_disciplina"]);
        if (!empty($this->pesquisa["pesq_ano"])) $stmt->bindValue(":ano", $this->pesquisa["pesq_ano"]);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if (!empty($f)) {
            foreach ($f as $r) {
                $item = new EducacionalBoletimETT();

                $item->handle = $r->HANDLE;
                $item->avaliacao = formataCase($r->NOMEAVALIACAO, true);
                $item->aluno = formataCase($r->NOMEALUNO, true);
                $item->cod_avaliacao = $r->AVALIACAO;
                $item->cod_aluno = $r->ALUNO;
                $item->nota = EducacionalBoletimGUI::formataNota($r->NOTA);
                $item->nota_revisao = EducacionalBoletimGUI::formataNota($r->NOTAREVISAO);
                $item->nota_pesada = EducacionalBoletimGUI::formataNota($item->nota_revisao * $r->PESO);
                $item->historico = $r->HISTORICO;

                // dados da avaliação
                $item->obj_avaliacao = new EducacionalAvaliacaoETT();
                $item->obj_avaliacao->handle = $item->cod_avaliacao;    // isso é redundante
                $item->obj_avaliacao->nome = $item->avaliacao;            // ||
                $item->obj_avaliacao->turma = formataCase($r->NOMETURMA, true);
                $item->obj_avaliacao->disciplina = formataCase($r->NOMEDISCIPLINA, true);
                $item->obj_avaliacao->cod_turma = $r->TURMA;
                $item->obj_avaliacao->cod_disciplina = $r->DISCIPLINA;
                $item->obj_avaliacao->descricao = $r->DESCRICAO;        // acho que não deverá ser acessado por boletim
                $item->obj_avaliacao->peso = $r->PESO;
                $item->obj_avaliacao->ano = $r->ANO;
                $item->obj_avaliacao->periodo = $r->PERIODO;
                $item->obj_avaliacao->cod_segmento = $r->SEGMENTO;
                $item->obj_avaliacao->serie = $r->NOMESERIE;
                if (empty($item->obj_avaliacao->serie)) $item->obj_avaliacao->serie = "Não informado";

                // totalizadores
                $this->nota_final += $item->nota_pesada;

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }

    public static function formataNota($str)
    {
        $arr = explode(".", $str);
        return str_pad($arr[0], 2, "0", STR_PAD_LEFT) . "," . str_pad($arr[1], 2, "0", STR_PAD_RIGHT);
    }
}