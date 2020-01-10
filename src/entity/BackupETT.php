<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/08/2019
 * Time: 23:49
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class BackupETT extends ObjectETT
{
    public $tables;

    public function getStructure()
    {
        $this->getTables();

        $string = "";
        foreach ($this->tables as $k => $v) {
            $virgula = false;
            $string .= "CREATE TABLE {$k} (";
            foreach ($v as $r){
                if($virgula){
                    $string .= ",";
                }
                $virgula = true;
                $string .= "\n {$r['nome']} {$r['tipo']}";

                if($r["size"] != null){
                    $string .= "({$r['size']})";
                }

                if($r["nome"] == "HANDLE"){
                    $string .= " PRIMARY KEY";
                }

                if($r["null"] == "NO"){
                    $string .= " NOT NULL";
                }
            }
            $string .= "\n ); \n \n";
        }
        file_put_contents(_base_path."backup-structure.sql", $string);
        $this->getData();
    }

    public function getData()
    {
        global $conexao;
        // jogar tudo aqui não fica bonito, mas otimiza memoria
        $i = 0;
        $cont = 1;
        $string = "";

        foreach ($this->tables as $k => $v) {
            $sql = "SELECT * FROM {$k}";

            //adiciona paginação
            $stmt = $conexao->prepare($sql);
            $stmt->execute();

            $f = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($f as $r){
                $insert = "INSERT INTO {$k} (";
                $value = "VALUES (";
                $virgula = false;

                foreach ($v as $c){
                    $atual = $c["nome"];
                    if($virgula){
                        $insert .= ",";
                        $value .= ",";
                    }
                    $virgula = true;
                    $insert .= " {$atual}";
                    if(in_array($c["tipo"], array("char", "varchar", "date", "datetime", "text"))){
                        $value .= " '{$r->$atual}'";
                    }
                    else{
                        if(!empty($r->$atual)){
                            $value .= " {$r->$atual}";
                        }
                        else{
                            $value .= " NULL";
                        }
                    }
                }

                $string .= $insert.") ".$value."); \n";
                $i++;

                // salva os dados e reseta td
                if($i > 5000){
                    file_put_contents(_base_path."backup-data-{$cont}.sql", $string); // reseta arquivo
                    $cont++;
                    $string = "";
                    $i = 0;
                }
            }
        }
        // salva o restante
        file_put_contents(_base_path."backup-data-{$cont}.sql", $string); // reseta arquivo
    }

    public function saveData($string)
    {
//        // Copy big file from somewhere else
//        $tmp_filepath = '/uploads/temp.sql';
//        $tmp = fopen($tmp_filepath, 'w');
//        $buffer_size = 512;
//
//        while (!feof($string)) {
//            $buffer = fread($string, $buffer_size);     // Read big file/data source/etc. in small chunks
//            fwrite($tmp, $buffer);                   // Write in small chunks
//        }
//
//        fclose($tmp);                      // Clean up
//
//        rename($tmp_filepath, '/uploads/backup-data.sql');
    }

    public function getTables()
    {
        global $conexao;

        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo'";

        //adiciona paginação
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($f as $r){
            $this->tables[$r->TABLE_NAME] = self::getColumns($r->TABLE_NAME);
        }
    }

    public static function getColumns($table)
    {
        global $conexao;

        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}'";

        //adiciona paginação
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        foreach ($f as $r){
            $temp = array();
            $temp["nome"] = $r->COLUMN_NAME;
            $temp["tipo"] = $r->DATA_TYPE;
            $temp["size"] = $r->CHARACTER_MAXIMUM_LENGTH;
            $temp["null"] = $r->IS_NULLABLE;
            $arr[] = $temp;
        }
        return $arr;
    }
}