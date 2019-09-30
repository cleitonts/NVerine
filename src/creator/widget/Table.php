<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 17/01/2019
 * Time: 15:53
 */

namespace src\creator\widget;


/**
 * Class Table
 * @package src\creator\widget
 * informações necessárias para montar a tabela de pesquisa de pagina
 */
class Table
{
    /**
     * @var string
     * nome da tabela
     */
    public $name;

    /**
     * @var string
     * endereço que sera aberto ao clicar na linha
     */
    public $target;     // URL de redirecionamento dos itens

    /**
     * @var string
     * passar a entidade por referencia.
     * entidade que sera instancia no momento de montagem da tabela
     */
    public $entity;

    /**
     * @var bool
     * controla se a tabela carrega checkbox ou não
     */
    public $check = false;

}