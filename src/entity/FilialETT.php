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
    // regimes tributários
    const SIMPLES_NACIONAL = 1;
    const SIMPLES_NACIONAL_EXCESSO = 2;
    const REGIME_NORMAL = 3;

    // propriedades
    public $nome; 				// nome "fantasia"
    public $razao_social;		// nome legal
    public $empresa; 			// mesmo que cod_empresa por enquanto?
    public $cnpj;
    public $cnae;
    public $regime_tributario;	// código
    public $inscricao_estadual;
    public $inscricao_municipal;
    public $telefone;
    public $logo_contrato;
    public $logotipo;
    public $sequencia_nota;		// numeração inicial da nota fiscal
    public $informacoes_fisco;	// informações adicionais da nota fiscal
    public $timezone;			/* formato [-/+]99:90 -- só é necessário usar esse objeto
								 * se for para recuperar a timezone que não é da filial logada
								 * (__TIMEZONE__ definido em conexão?)
								 */
    public $csc;				/* código de segurança do contribuinte (para emissão de NFC-e)
								 * (não é usado por aqui; lido em conexão como a timezone)
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
        // instancia endereço
        $this->endereco = new PessoaEnderecoETT();

        // referências por compatibilidade
        $this->crt = &$this->regime_tributario;
        $this->ie = &$this->inscricao_estadual;
        $this->im = &$this->inscricao_municipal;
    }
}