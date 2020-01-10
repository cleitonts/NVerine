<?php


namespace src\entity;


class SuporteGitGUI extends ObjectGUI
{
    /**
     * SuporteDiagGUI constructor.
     * @param null $handle
     */
    public function __construct($handle = null)
    {
        $this->header = array("Nome da pasta", "Hash", "Data");
    }
    
    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorios
     */
    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        switch ($coluna)
        {
            case 0:        return campo($item->nome);
            case 1:        return campo($item->hash_abreviado);
            case 2:        return campo($item->data);
        }
    }

    /**
     * @return mixed
     * mapeia os itens salvos no banco de dados e transforma em um array de ETT
     */
    public function fetch()
    {
        $lista = SuporteDiagGUI::scanDir("../");
        
        $i = 0;
        foreach ($lista as $d){
            if(!empty($this->pesquisa["pesq_num"])){
                if($this->pesquisa["pesq_num"] != $d){
                    continue;
                }
            }
            
            $atual = "../".$d."/";
            // su mostra os diretorios
            if(!is_dir($atual)){
                continue;
            }
            
            $HEAD_hash = trim(file_get_contents($atual.".git/refs/heads/master"));
            
            $item = new SuporteGitETT();
            $item->cont = $i;
            $item->handle = $d;
            $item->nome = $d;
            $item->hash = $HEAD_hash;
            $item->hash_abreviado = substr($HEAD_hash, 0, 7);
            $data = exec("git -C {$atual} show -s --format=%ci {$item->hash_abreviado}");
            
            //atualiza para o portugues
            $arr = explode(" ", $data);
            $arr2 = explode("-", $arr[0]);
            $arr3 = explode(":", $arr[1]);
            $item->data = "{$arr2[2]}-{$arr2[1]}-{$arr2[0]} {$arr3[0]}:{$arr3[1]}";
            
            $this->itens[] = $item;
        }
    }
}