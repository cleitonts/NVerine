<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 05/06/2019
 * Time: 14:05
 */

namespace src\entity;


class FilialETT extends ObjectETT
{
    // regimes tribut�rios
    const SIMPLES_NACIONAL = 1;
    const SIMPLES_NACIONAL_EXCESSO = 2;
    const REGIME_NORMAL = 3;

    // propriedades
    public $nome; 				// nome "fantasia"
    public $razao_social;		// nome legal
    public $empresa; 			// mesmo que cod_empresa por enquanto?
    public $cnpj;
    public $cnae;
    public $regime_tributario;	// c�digo
    public $inscricao_estadual;
    public $inscricao_municipal;
    public $telefone;
    public $logo_contrato;
    public $logotipo;
    public $sequencia_nota;		// numera��o inicial da nota fiscal
    public $informacoes_fisco;	// informa��es adicionais da nota fiscal
    public $timezone;			/* formato [-/+]99:90 -- s� � necess�rio usar esse objeto
								 * se for para recuperar a timezone que n�o � da filial logada
								 * (__TIMEZONE__ definido em conex�o?)
								 */
    public $csc;				/* c�digo de seguran�a do contribuinte (para emiss�o de NFC-e)
								 * (n�o � usado por aqui; lido em conex�o como a timezone)
								 */
    public $id_csc;				// identificador do CSC
    public $cod_inep;			// educacional

    // objetos externos
    public $endereco;

    // apenas gui
    public $cod_empresa;
    public $crt; 				// regime_tributario
    public $ie;					// inscricao_estadual
    public $im;					// inscricao_municipal

    // -------------------------------------------------------------------
    // construtor
    public function __construct() {
        // instancia endere�o
        $this->endereco = new PessoaEnderecoETT();

        // refer�ncias por compatibilidade
        $this->crt = &$this->regime_tributario;
        $this->ie = &$this->inscricao_estadual;
        $this->im = &$this->inscricao_municipal;
    }
}