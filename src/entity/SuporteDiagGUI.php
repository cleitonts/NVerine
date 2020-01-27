<?php


namespace src\entity;


class SuporteDiagGUI extends ObjectGUI
{

    /**
     * SuporteDiagGUI constructor.
     * @param null $handle
     */
    public function __construct($handle = null)
    {
        $this->header = array("Nome base", "Banco de dados", "Contrato", "URL", "Ping");
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
        switch ($coluna)
        {
            case 0:        return campo($item->nome);
            case 1:        return campo($item->db_name);
            case 2:        return 
                campo("<a target='_blank' href='index.php?pagina=contrato&pesq_codigo={$item->contrato}'>".
                $item->contrato."</a>");
            case 3:        return campo("<a target='_blank' href='http://{$item->url}'>{$item->url}</a>");
            case 4:        return campo($item->ping);
        }
    }

    /**
     * @return mixed
     * mapeia os dados nos arquivos de config
     */
    public function fetch()
    {
        $bases = self::scanDir(_base_root);

        $i = 0;
        foreach ($bases as $r) {
            if (!in_array($r, array("suporte", "equipe"))) {
                if (!empty($this->pesquisa["pesq_num"])) {
                    if($this->pesquisa["pesq_num"] != $r){
                        continue;
                    }
                }

                if (!empty($this->pesquisa["pesq_nome"])) {
                    if(strpos($r, $this->pesquisa["pesq_nome"]) === false){
                        continue;
                    }
                }

                $atual_path = _base_root . "/" . $r . "/erp";
                $ini = @parse_ini_file($atual_path . "/config.ini.php", false);

                $item = new SuporteDiagETT();
                $item->cont = $i;
                $item->handle = $r;
                $item->nome = $r;
                $item->db_name = $ini["dbname"];
                $item->contrato = $ini["contrato"];
                $item->senha = $ini["senha"];
                $item->url = "google.com/".$r."/";
                
                $http_code = $this->ping($item->url);

                if ($http_code >= 200 && $http_code <= 301) {
                    $item->ping = "<span class='text-success'>{$http_code}<!--ONLINE--></span>";
                }                     
                else {
                    //$item->url = "Offline";
                    $item->ping = "<span class='text-danger'>{$http_code}<!--OFFLINE--></span>";
                }

                // meses em atraso
                //$item->situacao_financeiro = 0;

                $this->itens[] = $item;
                $i++;
            }
        }
    }
    
    private function ping($url){
        // ping test
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_exec($curl);

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return $http_code;
    }
    
    public static function scanDir($dir){
        $bases = scandir($dir);
        return array_diff($bases, array('.', '..'));
    }
}