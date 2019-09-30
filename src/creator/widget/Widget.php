<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/01/2019
 * Time: 15:32
 */

namespace src\creator\widget;

/**
 * Class Widget
 * @package src\creator\widget
 * guarda todas as informações de widget
 */
class Widget {

    /**
     * @var
     * titulo da pagina
     */
    public $title;

    /**
     * @var Header
     * instancia de Header
     */
    public $header;

    /**
     * @var Body
     * instancia de Body
     */
    public $body;

    /**
     * @var string
     * entidade que serve de base para a página
     */
    public $entity;

    /**
     * @var array
     * array com endereços de novos arquivos de javascript para importar
     */
    public $includes = array();

    /**
     * Widget constructor.
     * cria novas instancias de Header e Body
     */
    public function __construct()
    {
        global $__PAGINA__;
        global $__MODULO__;

        $this->title = $__MODULO__ . " / " . $__PAGINA__;
        $this->header = new Header();
        $this->body = new Body();
    }

    /**
     * atualiza todos os valores defaults por meio de objeto e propriedades informadas
     */
    public function setDefaults(){
        foreach($this->body->tabs as &$tab){

            // campos soltos no form
            foreach ($tab->form->field as &$field){
                // corrige nomes
                $this->setNames($field);

                if(empty($field->value) && !empty($field->property)) {
                    $property = $field->property;

                    // as vezes tem um objeto dentro de outro
                    if (strpos($property, "->") !== false) {
                        $property = explode("->", $property);

                        $entidade = $this->entity;
                        foreach($property as $r){
                            $entidade = $entidade->$r;
                        }
                        $value = $entidade;
                    }
                    else{
                        $value = $this->entity->$property;
                        $this->checkProperties($this->entity, $property);
                    }

                    $this->setValue($field, $value, $property);
                }
            }
            // quando tem componentes / divs
            foreach ($tab->form->children as &$comp){
                foreach ($comp->field as &$field){
                    // corrige nomes
                    $this->setNames($field);

                    if(empty($field->value) && !empty($field->property)) {
                        $property = $field->property;

                        // as vezes tem um objeto dentro de outro
                        if (strpos($property, "->") !== false) {
                            $property = explode("->", $property);

                            $entidade = $this->entity;
                            foreach($property as $r){
                                $entidade = $entidade->$r;
                            }
                            $value = $entidade;
                        }
                        else{
                            $value = $this->entity->$property;
                            $this->checkProperties($this->entity, $property);
                        }

                        $this->setValue($field, $value, $property);
                    }
                }
            }

            // campos dentro de tabelas / listas
            foreach ($tab->form->table as &$table) {
                foreach ($table->rows as &$row) {
                    foreach ($row->field as &$field) {
                        // corrige nomes
                        $this->setNames($field);

                        if(empty($field->value) && !empty($field->property)) {
                            $property = $field->property;

                            // as vezes tem um objeto dentro de outro
                            if (strpos($property, "->") !== false) {
                                $property = explode("->", $property);

                                $entidade = $row->entity;
                                foreach($property as $r){
                                    $entidade = $entidade->$r;
                                }
                                $value = $entidade;
                            }
                            else{
                                $value = $row->entity->$property;
                                $this->checkProperties($row->entity, $property);
                            }

                            $this->setValue($field, $value, $property);
                        }

//                        if (empty($field->value) && !empty($field->property)) {
//                            $property = $field->property;
//
//                            $value = $row->entity->$property;
//
//                            $this->checkProperties($row->entity, $property);
//
//                            $this->setValue($field, $value, $property);
//                        }
                    }
                }
            }
        }
    }

    /**
     * @param $field
     * @param $value
     * @param $property
     * função para tratar as excessões
     */
    private function setValue(&$field, $value, $property)
    {
        // as vezes chega como array
        if(is_array($property)){
            $property = end($property);
        }

        // excessões
        if (strpos(strtolower($property), 'data') !== false) {
            $value = converteDataSql($value);
        }
        if (strpos(strtolower($property), 'valor') !== false) {
            //strpos(strtolower($property), 'perc') !== false) {
            $value = formataValor($value);
        }

        // campos que nao sofrem alteração de caixa
        if (strpos(strtolower($field->name), 'uf') !== false) {
            $field->value = strtoupper($value);
            return;
        }

        // campos proprios
        if (strpos(strtolower($field->name), 'estado') !== false ||
            strpos(strtolower($field->name), 'name') !== false ||
            strpos(strtolower($field->name), 'cidade') !== false ||
            strpos(strtolower($field->name), 'nome') !== false ||
            strpos(strtolower($field->name), 'pessoa') !== false
        ) {
            $field->value = formataCase($value, true);
            return;
        }

        $field->value = ucfirst(mb_strtolower($value, 'ISO-8859-1'));
    }
    /**
     * @param $field
     * corrige nomes dos campos
     */
    private function setNames(&$field){
        // corrige nome e descrição
        if(empty($field->description)){
            $field->name = mb_strtolower($field->name, 'ISO-8859-1');
            $field->description = ucfirst($field->name);
            $field->name = str_replace("%", "perc", $field->name);
            $field->name = str_replace("$", "valor", $field->name);
            $field->name = sanitize($field->name);
        }
        if($field->type == Fields::SELECT){
            foreach($field->options as &$r){
                // campos que nao sofrem alteração de caixa
                if( strpos(strtolower($field->name), 'uf') !== false
                ) {
                    $r->description = strtoupper($r->description);
                }

                // campos proprios
                elseif( strpos(strtolower($field->name), 'estado') !== false ||
                    strpos(strtolower($field->name), 'name') !== false ||
                    strpos(strtolower($field->name), 'cidade') !== false ||
                    strpos(strtolower($field->name), 'nome') !== false
                ) {
                    $r->description = formataCase($r->description, true);
                }
                else {
                    $r->description = ucfirst(mb_strtolower($r->description, 'ISO-8859-1'));
                }
            }
        }
    }

    private function checkProperties($class, $property)
    {
        if (__DEVELOPER__) {
            if (!property_exists($class, $property)) {
                dumper("Propriedade " . $property . " inxistente na classe " . get_class ($class));
            }
        }
    }
}