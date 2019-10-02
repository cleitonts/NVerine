<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/01/2019
 * Time: 15:35
 */

namespace src\creator\widget;

use src\services\Transact\ExtPDO as PDO;

/**
 * Class Fields
 * @package src\creator\widget
 * cada instancia representa um campo no formulario ou tabela dinamica
 */
class Fields
{
    // tipos de botões
    const TEXT = "TEXT";
    const SELECT = "SELECT";
    const DISABLED_SELECT = "DISABLED_SELECT";
    const CHECKBOX = "CHECKBOX";
    const RADIO = "RADIO";
    const BUTTON = "BUTTON";
    const SUBMIT = "SUBMIT";
    const HIDDEN = "HIDDEN";
    const LABEL = "LABEL";        // semelhante ao readonly
    const AREA = "AREA";           // textarea
    const FILE = "FILE";

    /**
     * @var string
     * atribuir uma das constantes.
     * Tipo de botão à ser exibido na interface
     */
    public $type;

    /**
     * @var string
     * informar valor padrão ou ja salvo do campo.
     * o preenchimento é executado quando invocado defaults da classe widget
     */
    public $value;

    /**
     * @var string
     * nome e id do campo e caso $this->description esteja vazio será a descrição também
     */
    public $name;

    /**
     * @var string
     * descrição que aparece na label do campo
     */
    public $description;    // quando nome é diferente da descrição mostrada

    /**
     * @var integer
     * numero de 1 a 12 que diz o tamanho de colunas no grid do layout
     */
    public $size;

    /**
     * @var string
     * icone que aparece dentro do input
     */
    public $icon;

    /**
     * @var string
     * nome da propriedade do objeto que está sendo mapeado.
     * campo usado no momento da tradução
     */
    public $property;

    /**
     * @var string
     * funçao javascript associada ao campo
     */
    public $function;

    /**
     * @var array
     * options para ser selecionadas caso campo seja select
     */
    public $options = array();

    //public $args = array();

    /**
     * @var string
     * classes css que afetam a aparencia do campo
     */
    public $class;         // somente para botões

    /**
     * @param $type
     * @param $size
     * @param $name
     * @param string $value
     * @param string $icon
     * geração rapida de um novo campo
     */
    public function now($type, $size, $name, $value = "", $icon = "")
    {
        $this->size = $size;
        $this->name = $name;
        $this->type = $type;
        $this->icon = $icon;
        $this->value = $value;
    }

    /**
     * @param $type
     * @param $size
     * @param $name
     * @param string $value
     * @param string $property
     * @return Fields
     */
    public static function novo($type, $size, $name, $property = "", $value = "")
    {
        $campo = new Fields();
        $campo->size = $size;
        $campo->name = $name;
        $campo->type = $type;
        $campo->property = $property;
        $campo->value = $value;
        return $campo;
    }

    /**
     * @param $type
     * @param $size
     * @param $name
     * @param $tabela
     * @param $labels
     * @param $values
     * @param string $where
     * @param string $property
     * @return Fields
     */
    public static function fromTable($type, $size, $name, $tabela, $labels, $values, $where = "", $property = "")
    {
        global $conexao;

        $labels = sqlConcat($labels);
        $sql = "SELECT {$labels} AS LABEL, {$values} AS VALUE FROM {$tabela} {$where} ORDER BY {$labels}";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $campo = new Fields();
        $campo->size = $size;
        $campo->name = $name;
        $campo->property = $property;
        $campo->options[] = new Options("", "");

        foreach ($f as $r){
            $campo->options[] = new Options($r->VALUE, $r->LABEL);
        }
        $campo->type = $type;

        return $campo;
    }
}
