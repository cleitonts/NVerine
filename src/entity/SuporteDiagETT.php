<?php


namespace src\entity;


use PDO;
use src\services\Transact\ExtPDO;

class SuporteDiagETT extends ObjectETT
{
    public $db_host = __DB_HOST__;
    public $db_name;
    public $db_user = __DB_USER__;
    public $db_pass = __DB_PASS__;

    public $nome;
    public $contrato;
    public $url;
    public $ping;
    public $senha = "senhamestre";
    public $segmento;

    public function cadastra()
    {
        // atualiza os nomes dos diretorios primeiro
        $this->handle = sanitize($this->nome);
        $this->db_name = sanitize($this->db_name);

        $dir = _base_root;
        if(_base_root != "uploads/"){
            $dir = _base_root . "/" . strtolower($this->handle);

            // bloqueia caso diretorio ja exista
            if (is_dir($dir) && 1 != 1){
                mensagem("Ja existe um diretório com este nome.", MSG_ERRO);
                finaliza();
            }
        }


        try {
            //Executa os SQL
            $dbh = new PDO("mysql:host=" . $this->db_host. ";", $this->db_user, $this->db_pass);
            $dbh->exec("CREATE DATABASE `$this->db_name`;") or die(print_r($dbh->errorInfo(), true));
        }
        catch(\PDOException $erro) {
            mensagem("Falha de conexão com banco de dados: ".$erro->getMessage(), MSG_ERRO);
            finaliza();
        }

        $conexao = new ExtPDO("mysql:host=".$this->db_host."; dbname=".$this->db_name, $this->db_user, $this->db_pass,
            array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        self::parseScript("install.sql", $conexao);
        self::parseScript("locais.sql", $conexao);
        self::parseScript("conteudo-default.sql", $conexao);
        if (!empty($this->segmento)){
            self::parseScript("conteudo-{$this->segmento}.sql", $conexao);
        }
        self::parseScript("constraints.sql", $conexao);

        // cria os diretorios
        if($dir != "uploads/"){
            $dir = _base_root . "/" . strtolower($this->handle);
            mkdir($dir);
            $dir .= "/erp";
        }

        mkdir($dir);
        mkdir($dir . "/documentos", 0777);
        mkdir($dir . "/xml", 0777);
        mkdir($dir . "/certs", 0777);
        mkdir($dir . "/msg", 0777);
        mkdir($dir . "/nfe", 0777);
        mkdir($dir . "/remessa", 0777);
        mkdir($dir . "/galeria", 0777);

        // puxa sample
        $config = file_get_contents("cfg/config.ini.php.sample");

        /* sobrescreve valores
         * ATENÇÂO: mudar os defaults em config.ini.php.sample vai quebrar este script!
         */
        $config = str_replace("@@masterkey@@", $this->senha, $config);
        $config = str_replace("@@host@@", $this->db_host, $config);
        $config = str_replace("@@database@@", $this->db_name, $config);
        $config = str_replace("@@user@@", $this->db_user, $config);
        $config = str_replace("@@pass@@", $this->db_pass, $config);
        $config = str_replace("@@driver@@", "mysql", $config);
        $config = str_replace("@@sistema@@", "Nverine", $config);
        //$config = str_replace("@@identidade@@", "", $config);
        //$config = str_replace("@@contrato@@", $this->contrato, $config);

        // escreve o novo arquivo
        if (file_put_contents($dir . "/config.ini.php", $config)) {     /* só em uploads temos permissão de escrita
                                                                                  * é importante que o diretório tenha sido criado antes!
                                                                                  */
            mensagem("Parabéns! Você já pode fazer o login no sistema utilizando as credenciais que lhe foram fornecidas.");
        } else {
            mensagem("Não foi possível salvar o arquivo de configuração. O instalador possui permissões de escrita?", MSG_ERRO);
            finaliza();
        }

        // tenta tornar o arquivo writeable
        shell_exec("chmod a+rw {$dir}/config.ini.php");
    }

    // interpreta e executa um script sql
    public static function parseScript($arq, $conexao, $path = "scripts/")
    {
        // puxa script
        $script = @file_get_contents($path.$arq);

        if ($script === false) { // manual diz para usar equivalência de tipo
            mensagem("Arquivo {$script} não foi encontrado!");
            finaliza();
        }

        // se os arquivos são utf-8, precisamos converter aqui.
        $script = utf8_decode($script);

        /* quebra script em consultas individuais.
         * BEGIN TRANSACTION e COMMIT devem constar do script, senão as coisas podem ficar feias!
         */
        $script = str_replace("CODIGO\n", "CODIGO ", $script); // pra não ter problemas. o parser é burro, eu sei
        $script = explode(";\n", $script); // quebra de linha é importante! código gerado por máquina.

        // hora da verdade
        $i = 0;
        foreach ($script as $sql) {
            $sql = trim($sql);

            // ignora se for uma linha vazia
            if (!empty($sql)) {
                $i++;

                // executa consulta
                $stmt = $conexao->prepare($sql);
                $stmt->execute();

                // tenta capturar erro da execução
                if ($stmt->rowCount() == 0) {
                    $err = $stmt->errorInfo();
                    $cod = $err[1];

                    if ($cod > 0) {
                        $mensagem = "Consulta retornou código de erro {$cod} na etapa #{$i} {$conexao->last_sql_statement}";
                        mensagem($mensagem, MSG_ERRO);
                        finaliza();
                    }
                }
            }
        }

        // chegou ao final do script
        mensagem("{$arq} executado com sucesso!");
    }
}