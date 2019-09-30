<?php

global $permissoes;

// puxa as informações de perfil
$perfil = new \src\entity\UsuarioGUI();
$perfil->fetch();

// busca horários
$horario = new src\entity\EducacionalGradeHorariaGUI();
$horario->top = "TOP 100";
$horario->setPesquisa();

// professor pode ver somente o seu horario
if(!$permissoes->libera("Administração")){
    $horario->pesquisa["pesq_professor"] = $perfil->itens[0]->cod_cliente;
}
$horario->fetch();

// emenda com o diário de classe
foreach($horario->itens as &$item) {
    $diario = new src\entity\EducacionalDiarioClasseGUI();
    $diario->top = "TOP 100";
    $diario->usa_horario = false;
    $diario->pesquisa["pesq_horario"] = $item->handle;
    $diario->pesquisa["pesq_turma"] = $_REQUEST["pesq_turma"];
    $diario->pesquisa["pesq_data"] = $_REQUEST["pesq_data"];
    $diario->fetch();

    $item->diario = $diario->itens;
}

echo $horario->exportaJSON();
