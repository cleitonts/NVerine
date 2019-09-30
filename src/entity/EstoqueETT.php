<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 24/01/2019
 * Time: 15:50
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EstoqueETT extends ObjectETT
{
    public static function getEndereco(){
        global $conexao;

        $where = "WHERE ".filtraFilial("A.FILIAL", "Almoxarifado");

        // puxa dados
        $sql = "SELECT A.NOME AS ALMOXARIFADO, E.NOME AS ENDERECO, E.HANDLE
			FROM K_FN_ENDERECO E LEFT JOIN K_FN_ALMOXARIFADO A ON E.ALMOXARIFADO = A.HANDLE
			{$where}
			ORDER BY E.NOME";

        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr  = array();
        foreach($f as $r){
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->ALMOXARIFADO . " -- " . $r->ENDERECO;
        }

        return $arr;
    }
}