<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 27/03/19
 * Time: 12:34.
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalSerieGUI extends ObjectGUI implements InterfaceGUI
{
    public function __construct($handle = null)
    {
        $this->handle = $handle;

        $this->header = array(
            'Cod.registro', 'Nome', 'Faixa etária', 'Nome do ciclo',
        );
    }

    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        switch ($coluna) {
            case 0:        return campo($item->handle);
            case 1:        return campo($item->nome);
            case 2:        return campo(EducacionalSerieETT::getFaixaIdade($item->faixa_etaria));
            case 3:        return campo($item->nome_ciclo);
        }
    }

    public function fetch()
    {
        global $conexao;
        $where = 'WHERE 1 = 1';
        if (!empty($this->pesquisa['pesq_num'])) {
            $where .= " AND HANDLE = :HANDLE \n";
        }
        // tras os indices da lista
        $sql = "SELECT * FROM K_SERIE {$where}";
        $stmt = $conexao->prepare($sql);
        if (!empty($this->pesquisa['pesq_num'])) {
            $stmt->bindValue(':HANDLE', $this->pesquisa['pesq_num']);
        }

        $stmt->execute();
        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        $i = 0; //inicia contador

        if (!empty($listas)) {
            foreach ($listas as $r) {
                $item = new EducacionalSerieETT();

                //CABEÇALHO DA LISTA
                $item->handle = $r->HANDLE;
                $item->nome = $r->NOME;
                $item->faixa_etaria = $r->FAIXA_ETARIA;
                $item->ciclo_etapa = $r->CICLO_ETAPA;
                $item->nota_aprovacao = $r->NOTA_APROVACAO;
                $item->nome_ciclo = EducacionalSerieETT::getNomeCiclo($r->CICLO_ETAPA, "");

                // carrega lista de materias
                if(!empty($this->handle)){
                    $template = new EducacionalSerieMateriasGUI();
                    $template->pesquisa['pesq_serie'] = $this->handle;
                    $template->fetch();
                    $item->materias = $template->itens;
                }
                $this->itens[] = $item;
                ++$i;
            }
        }
    }
}
