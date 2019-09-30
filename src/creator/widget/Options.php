<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 07/01/2019
 * Time: 15:11
 */

namespace src\creator\widget;


class Options
{
    /**
     * @var string
     * valor de cada option do select
     */
    public $value;

    /**
     * @var string
     * Descrição que aparecerá na option na hora de selecionar
     */
    public $description;

    /**
     * @var string
     * 'S', 'N'
     * usar somente quando for checkbox ou radio
     */
    public $checked;

    /**
     * Options constructor.
     * @param string $value
     * @param string $description
     * Monta rapidamente uma option
     */
    public function __construct($value = "", $description = "", $checked = 'N')
    {
        $this->value = $value;
        $this->description = $description;
        $this->checked = $checked;
    }

    /**
     * @param $value
     * @param array $description
     * @return array
     * cria uma lista de options de um array predefinido
     */
    public static function byArray($value, $description = array())
    {
        $arr = array();
        for ($i = 0; $i < count($value); $i++){
            if(empty($description)){
                $temp = new Options($value[$i], $value[$i]);
            }
            else{
                $temp = new Options($value[$i], $description[$i]);
            }
            $arr[] = $temp;
        }
        return $arr;
    }
}