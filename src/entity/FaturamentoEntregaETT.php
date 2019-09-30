<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 15:22
 */

namespace src\entity;


class FaturamentoEntregaETT extends PessoaEnderecoETT
{
// nota à qual pertence (setado no construtor)
    protected $nota;

    // propriedades
    public $transportadora;
    public $data_entrega;
    public $tipo_frete;        // 9 - sem frete | 1 - FOB | 2 - CIF | 3 - terceiros

    // dados do veículo
    public $placa;
    public $uf_placa;
    public $rntc;
    public $motorista;
    public $endereco;
    // volumes
    public $volume_quantidade;
    public $volume_especie;
    public $volume_marca;
    public $volume_numeracao;
    public $volume_peso_liquido;
    public $volume_peso_bruto;

    // ** apenas gui
    public $cod_transportadora;
    public $cod_tipo_frete;

    // ----------------------------------------------------------------------------
    public function __construct($nota = 0)
    {
        $this->endereco = new PessoaVinculoETT();
        $this->nota = $nota;
    }

    public function cadastra()
    {
        // este método não cadastra porque salva na tabela da nota. use se a estrutura for alterada.
        return false;
    }

    public function atualiza()
    {
        // trata defaults
        if (empty($this->data_entrega)) $this->data_entrega = amanha();

        // sanitiza placa
        $this->placa = str_replace("-", "", $this->placa);
        $this->placa = str_replace(" ", "", $this->placa);

        $stmt = $this->updateStatement("K_NOTA",
            array(
                "HANDLE" => $this->nota,
                "TRANSPORTADORA" => validaVazio($this->transportadora),
                "FRETE" => $this->tipo_frete,
                "DATAENTREGA" => $this->data_entrega,
                "BAIRRO" => left($this->bairro, 60),
                "LOGRADOURO" => left($this->logradouro, 60),
                "COMPLEMENTO" => left($this->complemento, 60),
                "NUMERO" => left($this->numero, 60),
                "ESTADO" => left($this->estado, 2),
                "MUNICIPIO" => validaVazio($this->cidade),
                "PLACA" => left($this->placa, 15),
                "UFPLACA" => left($this->uf_placa, 2),
                "RNTC" => left($this->rntc, 20),
                "MOTORISTA" => left($this->motorista, 50),
                "VOLQUANTIDADE" => $this->volume_quantidade,
                "VOLESPECIE" => left($this->volume_especie, 60),
                "VOLMARCA" => left($this->volume_marca, 60),
                "VOLNUMERACAO" => left($this->volume_numeracao, 60),
                "VOLPESOLIQUIDO" => $this->volume_peso_liquido,
                "VOLPESOBRUTO" => $this->volume_peso_bruto,
            ));

        retornoPadrao($stmt, "Dados de entrega salvos", "Não foi possível atualizar os dados de entrega");
    }
}