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
    //vari�veis recebidas da controller da lista de pre�o
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
        validaCampo($this->faixa_etaria, "Faixa et�ria");
        validaCampo($this->nota_aprovacao, "Nota aprova��o");
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

        retornoPadrao($stmt, "S�rie #{$this->handle} cadastrada com sucesso", "N�o foi poss�vel cadastrar a S�rie {$this->handle}");
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

        retornoPadrao($stmt, "Atualizar a S�rie #{$this->handle}", "N�o foi poss�vel Atualizare a S�rie {$this->handle}");
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
            'Pr�-escola',
            'Unificada',
            'Ciclo de alfabetiza��o',
            'Ciclo complementar',
            'Ciclo intermedi�rio',
            'Ciclo da consolida��o',
            'Ensino fundamental - Multi',
            'Educa��o infantil e ensino fundamental - Multietapas',
            '1� s�rie',
            '2� s�rie',
            '3� s�rie',
            'Anos inicias - ensino fundamental',
            'Anos finais - ensino fundamental',
            'Ensino m�dio Jovens & Adultos',
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
            '0 meses � 11 meses',
            '12 meses � 1,11 anos',
            '2 anos � 2,11 anos',
            '3 anos � 3,11 anos',
            '4 anos � 4,11 anos',
            '5 anos � 5,11 anos',
            '6 anos � 6,11 anos',
            '7 anos � 7,11 anos',
            '8 anos � 8,11 anos',
            '9 anos � 9,11 anos',
            '10 anos � 10,11 anos',
            '11 anos � 11,11 anos',
            '12 anos � 12,11 anos',
            '13 anos � 13,11 anos',
            '14 anos � 14,11 anos',
            '15 anos � 15,11 anos',
            '16 anos � 16,11 anos',
            '17 anos � 17,11 anos',
            '0 meses � 14,11 anos',
            '6 anos � 14,11 anos',
            '18 anos � 29,11 anos',
            'Acima de 18 anos',
            'Acima de 30 anos'
        );

        if ($lista_completa) {
            return $lista_idade;
        }

        return $lista_idade[$lista];
    }
}
