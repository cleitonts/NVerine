<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/03/2019
 * Time: 17:11
 */

namespace src\creator\widget;


/**
 * Class Chart
 * @package src\creator\widget
 * responsavel por enviar dados basicos para montar o grafico
 */
class Chart
{
    const BARRAS_H = 1;
    const BARRAS_V = 2;
    const PIZZA = 3;
    const STACKED_GROUP = 4;

    /**
     * @var string
     * nome do gráfico
     */
    public $name;

    /**
     * @var string
     * passar a entidade por referencia.
     * entidade que sera instancia no momento de montagem da tabela
     */
    public $entity;

    /**
     * @var bool
     * controla o tipo de grafico que sera reenderizado
     */
    public $type;

    /**
     * @var integer
     * tamanho do grafico que sera renderizado de 0 a 12
     */
    public $size;
}