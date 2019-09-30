<?php
namespace src\entity;

use src\services\Transact\ExtPDO as PDO;


/**
 * Class GaleriaGUI
 * @package src\entity
 * interface que cuida apenas de imagens do sistema
 */
class GaleriaGUI extends ObjectGUI{
    
    public $produto;
	public $url;
    public $legenda;
	
	// inutilizados por enquanto
	public $modelo;
	public $ativo;
	public $ordem;
	
	// -----------------------------------------------------------------------------------------
	// métodos públicos
	public function __construct($handle = null) {
		$this->modelo = 1;
		$this->ativo = "S";
		$this->ordem = 1;
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
        $where = 'WHERE 1 = 1';
        if (!empty($this->pesquisa['pesq_target'])) {
            $where .= " AND G.TARGET = :target \n";
        }
        if (!empty($this->pesquisa['pesq_referencia'])) {
            $where .= " AND G.REFERENCIA = :referencia \n";
        }

        // tras os indices da lista
        $sql = "SELECT * FROM K_GALERIA G {$where}";

        $stmt = $conexao->prepare($sql);
        if (!empty($this->pesquisa['pesq_target'])) $stmt->bindValue(':target', $this->pesquisa['pesq_target']);
        if (!empty($this->pesquisa['pesq_referencia'])) $stmt->bindValue(':referencia', $this->pesquisa['pesq_referencia']);
        $stmt->execute();

        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        $i = 0; //inicia contador

        if (!empty($listas)) {
            foreach ($listas as $r) {
                $item = new GaleriaETT();

                $item->target = $r->TARGET;
                $item->url = $r->URL;
                $item->handle = $r->HANDLE;
                $item->referencia = $r->REFERENCIA;
                $item->ordem = $r->ORDEM;
                $item->ativo = $r->ATIVO;
                $item->legenda = $r->LEGENDA;

                $this->itens[] = $item;
                $i++;
            }
        }
    }
}