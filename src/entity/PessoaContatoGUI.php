<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 11:16
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class PessoaContatoGUI extends ObjectGUI implements InterfaceGUI
{

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

        $sql = "SELECT * FROM K_FN_CONTATO WHERE PESSOA = :codigo ORDER BY ORDEM DESC";

        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $this->pesquisa["pesq_pessoa"]);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // novos contatos
        if (!empty($f)) {
            foreach ($f as $r) {
                $item = new PessoaContatoETT();
                $item->handle = $r->HANDLE;
                $item->nome = formataCase($r->NOME, true);
                $item->area = formataCase($r->AREA);
                $item->email = strtolower($r->EMAIL);
                $item->telefone = $r->TELEFONE;
                $item->ordem = $r->ORDEM;
                $this->itens[] = $item;
            }
        }
    }
}