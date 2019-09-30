<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/02/2019
 * Time: 22:39
 */

namespace src\services;


use src\creator\widget\Fields;
use src\creator\widget\Form;

class Translate
{
    public function translate($foreign, $base)
    {
        // instancia a classe base
        $temp = get_class($base->entity);
        $entity = new $temp();

        // monta uma matriz para ser possivel cruzar os dados
        foreach ($base->body->tabs as $k => $v) {
            // itera cada campos normais
            foreach ($v->form->field as $r) {
                $prop = $r->property;

                if(!empty($prop)){
                    $entity->$prop = self::setValue($prop, $foreign[$r->name]);
                }
            }

            // itera campos de componentes
            $fields = $this->unwrap($v->form->children); // retorna array de fields

            foreach ($fields as $r) {
                $prop = $r->property;

                if (!empty($prop)) {
                    $entity->$prop = self::setValue($prop, $foreign[$r->name]);
                }
            }

            // itera cada tabela, pode ter mais de uma
            if (!empty($v->form->table)) {
                // para cada tabela na pagina
                foreach($v->form->table as $table) {
                    $i = 1;

                    // itera as o $_REQUEST par ao caso de ser uma tabela dinamica, assim sera instanciada quantas classes precisar
                    // para cada linha na pagina
                    foreach ($foreign[$table->reference] as $key => $row){
                        // sempre deve haver um template
                        if(is_numeric($key)) {

                            // instancia a nova classe
                            $temp = get_class($table->rows[0]->entity);
                            $reference = new $temp();

                            // itera os campos para pegar a property
                            // para cada campo na página
                            foreach ($table->rows[0]->field as $field){

                                // campos com readonly não são lidos
                                if(!empty($field->property) && $field->type != Fields::LABEL){

                                    // descobre valor e property
                                    $property = $field->property;
                                    $valor = self::setValue($property, $row[$field->name."_".$i]);

                                    // joga na propriedade da classe

                                    $reference->$property = $valor;
                                }
                            }

                            $ref = $table->reference;
                            array_push($entity->$ref, $reference);
                            $i++;
                        }
                    }
                }
            }
        }

        // retorna a entidade populada
        return $entity;
    }

    /**
     * retira o involucro componente
     */
    private function unwrap($component){
        $campos = array();
        foreach ($component as $r){
            if(!empty($r->children)){
                $arr = $this->unwrap($component->children);

                foreach ($arr as $f){
                    $campos[] = $f;
                }
            }
            foreach ($r->field as $f){
                $campos[] = $f;
            }
        }
        return $campos;
    }

    /**
     * @param $prop
     * @param $value
     * corrige os valores, como a data e a percentagem por exemplo
     * @return null|string
     */
    private function setValue($prop, $value){
        $value = utf8_decode($value);
        // se está vazio ja faz o retorno
        if(empty($value)){
            return null;
        }

        // excessões
        if(strpos($prop, 'data') !== false){
            $value = converteData($value);
        }
        if(strpos($prop, 'perc') !== false){
            $value = str_replace("%", "", $value);
            if(strpos($value, ',') !== false){
                $value = str_replace(".", "", $value);
                $value = str_replace(",", ".", $value);
            }
        }
        if(strpos($prop, 'valor') !== false){
            if(strpos($value, ',') !== false){
                $value = str_replace(".", "", $value);
                $value = str_replace(",", ".", $value);
            }
        }

        return $value;
    }
}