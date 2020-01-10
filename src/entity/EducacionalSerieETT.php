<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 27/03/19
 * Time: 13:52.
 */

namespace src\entity;


class EducacionalSerieETT extends ObjectETT
{
    //variáveis recebidas da controller da lista de preço
    public $faixa_etaria;
    public $ciclo_etapa;
    public $nome_ciclo;
    public $nota_aprovacao;
    public $materias = array();

    public function validaForm(){
        global $transact;

        // campos obrigatorios
        validaCampo($this->nome, "Nome");
        validaCampo($this->ciclo_etapa, "Ciclo escolar");
        validaCampo($this->faixa_etaria, "Faixa etária");
        validaCampo($this->nota_aprovacao, "Nota aprovação");
    }

    public function cadastra()
    {
        global $conexao;

        $this->validaForm();

        // gera novo handle de serie
        $this->handle = newHandle('K_SERIE', $conexao);

        $stmt = $this->insertStatement("K_SERIE",
            array(
                "HANDLE"		        => $this->handle,
                "NOME"		            => $this->nome,
                "FAIXA_ETARIA"			=> $this->faixa_etaria,
                "CICLO_ETAPA"			=> $this->ciclo_etapa,
                "NOTA_APROVACAO"	    => $this->nota_aprovacao
            ));

        retornoPadrao($stmt, "Série #{$this->handle} cadastrada com sucesso", "Não foi possível cadastrar a Série {$this->handle}");
    }

    public function atualizar()
    {
        $this->validaForm();

        $stmt = $this->updateStatement("K_SERIE",
            array(
                "HANDLE"		        => $this->handle,
                "NOME"		            => $this->nome,
                "FAIXA_ETARIA"			=> $this->faixa_etaria,
                "CICLO_ETAPA"			=> $this->ciclo_etapa,
                "NOTA_APROVACAO"	    => $this->nota_aprovacao
            ));

        retornoPadrao($stmt, "Atualizar a Série #{$this->handle}", "Não foi possível Atualizare a Série {$this->handle}");
    }

    /**
     * @return mixed
     * retorna uma lista de ciclo ou um unico
     */
    public static function getNomeCiclo($ciclo_etapa = 0, $lista_completa = false)
    {
        $array_nome_ciclo = array(
            '',
            'Creche',
            'Pré-escola',
            'Unificada',
            'Ciclo de alfabetização',
            'Ciclo complementar',
            'Ciclo intermediário',
            'Ciclo da consolidação',
            'Ensino fundamental - Multi',
            'Educação infantil e ensino fundamental - Multietapas',
            '1ª série',
            '2ª série',
            '3ª série',
            'Anos inicias - ensino fundamental',
            'Anos finais - ensino fundamental',
            'Ensino médio Jovens & Adultos',
        );

        if ($lista_completa) {
            return $array_nome_ciclo;
        }

        return $array_nome_ciclo[$ciclo_etapa];
    }

    /**
     * retorna a faia etaria
     */
    public static function getFaixaIdade($lista = 0, $lista_completa = false)
    {
        $lista_idade = array(
            '',
            '0 meses à 11 meses',
            '12 meses à 1,11 anos',
            '2 anos à 2,11 anos',
            '3 anos à 3,11 anos',
            '4 anos à 4,11 anos',
            '5 anos à 5,11 anos',
            '6 anos à 6,11 anos',
            '7 anos à 7,11 anos',
            '8 anos à 8,11 anos',
            '9 anos à 9,11 anos',
            '10 anos à 10,11 anos',
            '11 anos à 11,11 anos',
            '12 anos à 12,11 anos',
            '13 anos à 13,11 anos',
            '14 anos à 14,11 anos',
            '15 anos à 15,11 anos',
            '16 anos à 16,11 anos',
            '17 anos à 17,11 anos',
            '0 meses à 14,11 anos',
            '6 anos à 14,11 anos',
            '18 anos à 29,11 anos',
            'Acima de 18 anos',
            'Acima de 30 anos'
        );

        if ($lista_completa) {
            return $lista_idade;
        }

        return $lista_idade[$lista];
    }
}
