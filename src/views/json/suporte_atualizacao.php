<?php

use ExtPDO as pdo;

global $conexao;

// puxa dados de usuário
$sql = "SELECT C.HANDLE + (
                          SELECT TOP 1 HANDLE FROM K_CHAMADOHISTORICO H                    
                          ORDER BY H.HANDLE DESC
                      ) AS TOTAL
        FROM K_CHAMADOS C ORDER BY C.HANDLE DESC";
$stmt = $conexao->prepare($sql);
$stmt->execute();

$f = $stmt->fetch(PDO::FETCH_OBJ);
print_r(json_encode($f->TOTAL));