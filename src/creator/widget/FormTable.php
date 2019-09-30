<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 24/01/2019
 * Time: 15:07
 */

namespace src\creator\widget;


/**
 * Class FormTable
 * @package src\creator\widget
 * cada instancia significa uma tabela dinamica
 */
class FormTable
{
    const TABLE_STATIC = 1;
    const TABLE_DYNAMIC = 2;
    const LIST_STATIC = 3;
    const LIST_DYNAMIC = 4;

    /**
     * @var string
     * Informar o nome do campo que servir� de referencia no layout da p�gina.
     * O javascript jogar� a tabela depois deste campo.
     */
    public $after;

    /**
     * @var int
     * os valores veem das constantes declaradas anteriormente
     */
    public $view = self::TABLE_STATIC;

    /**
     * @var string
     * Isto guarda o indice onde o objeto ser� inserido no objeto m�e
     * EX: NotaGUI->produtos; NotaGUI->duplicatas;
     */
    public $reference;

    /**
     * @var bool
     * libera ou bloqueia a exclus�o de linhas ja salvas
     */
    public $delete_block = true;

    /**
     * @var array
     * array da tableRow, lista de linhas da tabela
     */
    public $rows = array();

    /**
     * @var string
     * n�o obirgatorio, serve para diferenciar quando existem varias tabelas em uma tela
     */
    public $name;

    /**
     * FormTable constructor.
     * @param $reference
     * informar o indice no momento da instancia��o
     */
    public function __construct($reference)
    {
        $this->reference = $reference;
    }
}