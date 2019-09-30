<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/08/2019
 * Time: 15:54
 */

if(empty($_REQUEST["pesq_referencia"])){
    return;
}

$galeria = new \src\entity\GaleriaGUI();
$galeria->pesquisa["pesq_target"] = \src\entity\GaleriaETT::TARGET_SUPORTE;
$galeria->pesquisa["pesq_referencia"] = $_REQUEST["pesq_referencia"];
$galeria->fetch();

$component = new \src\creator\widget\Component();
$component->setGaleria($galeria, $_REQUEST["pesq_referencia"], \src\entity\GaleriaETT::TARGET_SUPORTE);

print_r(\src\creator\widget\Tools::toJson($component));