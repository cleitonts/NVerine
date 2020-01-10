<?php
$pessoas = new \src\entity\PessoaGUI();
$pessoas->setPesquisa();
$pessoas->fetch();

$arr = array();

foreach($pessoas->itens as $r){
    $atual = array();
    $atual["value"] = $r->nome;
    $atual["id"] = $r->handle;
    $atual["label"] = $r->handle. " - " . $r->nome;
    
    // transforma encode
    $atual = array_map("utf8_encode", $atual);
    $arr[] = $atual;
}

print_r(json_encode($arr));