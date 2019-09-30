<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 24/01/2019
 * Time: 15:21
 */

namespace src\creator\widget;


/**
 * Class FormTableRow
 * @property string after
 * @package src\creator\widget
 * cada instancia significa uma linha em tabelas de campos
 */
class FormTableRow
{
    /**
     * @var array
     * lista de campos em cada linha da tabela
     */
    public $field = array();       // array de campos

    /**
     * @var string
     * entidade a ser mapeada no momento da traduчуo
     */
    public $entity;
}