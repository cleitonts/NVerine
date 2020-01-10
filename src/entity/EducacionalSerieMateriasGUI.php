<?php


namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalSerieMateriasGUI extends ObjectGUI implements InterfaceGUI
{

    /**
     * InterfaceGUI constructor.
     * @param null $handle
     * entre outras coisas no momento da inicialização,
     * monta o header com nomes de colunas para os relatorios
     */
    public function __construct($handle = null)
    {
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

        $this->itens = array();
        $where = " WHERE 1 = 1 \n ";

        // monta query de pesquisa
        if(!empty($this->pesquisa["pesq_serie"])) $where .= "AND SERIE = :serie \n";


        $sql = "SELECT * FROM K_SERIE_MATERIA {$where} ";
        $stmt = $conexao->prepare($sql);



        // mapeando filtros
        if(!empty($this->pesquisa["pesq_serie"])) $stmt->bindValue(":serie", $this->pesquisa["pesq_serie"]);


        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if(!empty($f)) {
            foreach($f as $r) {
                $item = new EducacionalSerieMateriasETT();

                $item->handle = $r->HANDLE;
                $item->serie = $r->SERIE;
                $item->mascara_nota = $r->MASCARA_NOTA;
                $item->componente_curricular = $r->COMPONENTE_CURRICULAR;
                $item->nome = $r->NOME;
                $item->codigo = $r->CODIGO;
                $item->carga_horaria = number_format($r->CARGA, 1, ".", "");
                $item->base_curricular = $r->BASE_CURRICULAR;
                $item->area_conhecimento = $r->AREA_CONHECIMENTO;

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }

}