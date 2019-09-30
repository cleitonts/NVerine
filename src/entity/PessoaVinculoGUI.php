<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 13:29
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class PessoaVinculoGUI extends ObjectGUI implements InterfaceGUI
{
    public $pessoa;

    /**
     * InterfaceGUI constructor.
     * @param null $handle
     * entre outras coisas no momento da inicialização,
     * monta o header com nomes de colunas para os relatorios
     */
    public function __construct($handle = null)
    {
        parent::__construct($handle);
    }

    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorios
     */
    public function getCampo($linha, $coluna)
    {
        // TODO: Implement getCampo() method.
    }

    /**
     * @return mixed
     * mapeia os itens salvos no banco de dados e transforma em um array de ETT
     */
    public function fetch()
    {
        global $conexao;

        // inicializa array
        $this->itens = array();

        // monta query de pesquisa
        $where = "";
        if (isset($this->tipo)) $where .= "AND V.TIPOVINCULO = {$this->tipo}\n";
        if (isset($this->pesquisa["pesq_responsavel"])) $where .= "AND V.RESPONSAVEL = 'S'\n";

        // puxa dados
        $sql = "SELECT {$this->top}
				V.*,	P1.NOME NOMEPAI, P2.NOME NOMEFILHO, T.NOME NOMEVINCULO, T.RELACIONADO, P1.PROFISSAO
				FROM K_FN_PESSOAVINCULO V
				LEFT JOIN K_FN_PESSOA P1 ON V.PAI = P1.HANDLE
				LEFT JOIN K_FN_PESSOA P2 ON V.FILHO = P2.HANDLE
				LEFT JOIN K_FN_TIPOVINCULO T ON V.TIPOVINCULO = T.HANDLE
				WHERE (V.PAI = :pessoa OR V.FILHO = :pessoa)
				{$where}";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":pessoa", $this->pessoa);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // insere no array
        $i = 0;

        foreach ($f as $r) {
            $item = new PessoaVinculoETT();

            $item->handle = $r->HANDLE;
            $item->cod_pai = $r->PAI;
            $item->cod_filho = $r->FILHO;
            $item->profissao = formataCase($r->PROFISSAO);
            $item->cod_tipo = $r->TIPOVINCULO;
            $item->pai = "<a href='index.php?pagina=cadastro_pessoa&pesq_codigo={$item->cod_pai}'>" . formataCase($r->NOMEPAI, true) . "</a>";
            $item->filho = "<a href='index.php?pagina=cadastro_pessoa&pesq_codigo={$item->cod_filho}'>" . formataCase($r->NOMEFILHO, true) . "</a>";
            $item->tipo = formataCase($r->NOMEVINCULO);
            $item->relacionado = formataCase($r->RELACIONADO);
            $item->responsavel = $r->RESPONSAVEL;

            array_push($this->itens, $item);
            $i++;
        }
    }
}