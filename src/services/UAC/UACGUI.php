<?php


namespace src\services\UAC;


use src\entity\ObjectGUI;
use src\services\Transact\ExtPDO as PDO;

class UACGUI extends ObjectGUI
{
    public function __construct($handle = null)
    {
        $this->header = array("Handle", "Nome");
    }

    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorio
     */
    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];
        // para a coluna, retorna um array com o valor e a classe a aplicar
        switch ($coluna) {
            case 0:         return campo($item->handle);
            case 1:         return campo($item->nome);
            default:        return "não implementado";
        }
    }

    /**
     * @return mixed
     * mapeia os itens salvos no banco de dados e transforma em um array de ETT
     */
    public function fetch()
    {
        global $conexao;

        // puxa dados de usuário
        $sql = "SELECT G.*  
                FROM K_FN_GRUPOUSUARIO G ";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($f as $r) {
            $item = new \stdClass();

            $item->handle = $r->HANDLE;
            $item->nome = formataCase($r->NOME, true);
            $this->itens[] = $item;
        }
    }
}