<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 10:23
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class PessoaEnderecoETT extends ObjectETT
{
// propriedades
    public $pessoa;                    // pai
    public $cod_pessoa;
    public $estado;
    public $cidade;
    public $logradouro;
    public $bairro;
    public $complemento;
    public $numero;
    public $cep;
    public $ordem;
    public $tipo;                    // texto livre ("próprio", "alugado", etc.)

    // apenas para gui
    public $cod_estado;
    public $cod_estado_ibge;
    public $sigla_estado;
    public $cod_cidade;            // é o código IBGE?
    public $cod_cidade_ibge;        // se precisar separar... não tem padrão :(
    public $pais;
    public $cod_pais;
    public $cod_pais_bacen;
    public $sigla_pais;

    // construtor
    public function __construct()
    {
        // implementar endereços estrangeiros: trocar essas propriedades se o estado for EX
        $this->pais = "Brasil";
        $this->cod_pais = 55;
        $this->cod_pais_bacen = 1058;
        $this->sigla_pais = "BRA";
    }

    public function validaForm()
    {
        global $transact;

        // campos obrigatorios
        $transact->validaCampo($this->cidade, "Cidade");
        $transact->validaCampo($this->bairro, "Bairro");
        $transact->validaCampo($this->logradouro, "Logradouro");
        $transact->validaCampo($this->numero, "Número");
        $transact->validaCampo($this->cep, "Cep");
        $transact->validaCampo($this->cep, "Estado");
        $transact->validaCampo($this->cod_pessoa, "Pessoa");

        // por acaso alguns campos estão entrando/puxando espaço
        $this->logradouro = trim($this->logradouro);
        $this->bairro = trim($this->bairro);
        $this->complemento = trim($this->complemento);
        $this->numero = trim($this->numero);
    }


    // métodos públicosFaturamentoExportacaoETT
    public function cadastra()
    {
        global $conexao;

        $this->validaForm();

        // gera handle
        $this->handle = newHandle("K_FN_PESSOAENDERECO", $conexao);

        // insere
        $stmt = $this->insertStatement("K_FN_PESSOAENDERECO",
            array(
                "HANDLE" => $this->handle,
                "PESSOA" => $this->cod_pessoa,
                "ORDEM" => $this->ordem,
                "ESTADO" => $this->cod_estado,
                "CIDADE" => left($this->cidade, 30),
                "LOGRADOURO" => left($this->logradouro, 50),
                "BAIRRO" => left($this->bairro, 30),
                "COMPLEMENTO" => left($this->complemento, 50),
                "NUMERO" => left($this->numero, 10),
                "CEP" => left($this->cep, 12),
                "TIPO" => left($this->tipo, 20)
            ));

        retornoPadrao($stmt, "Endereço cadastrado com sucesso.", "Não foi possível cadastrar o endereço");
    }

    public function atualiza()
    {
        $this->validaForm();

        $stmt = $this->updateStatement("K_FN_PESSOAENDERECO",
            array(
                "HANDLE" => $this->handle,
                "ORDEM" => $this->ordem,
                "ESTADO" => $this->cod_estado,
                "CIDADE" => left($this->cidade, 30),
                "LOGRADOURO" => left($this->logradouro, 50),
                "BAIRRO" => left($this->bairro, 30),
                "COMPLEMENTO" => left($this->complemento, 50),
                "NUMERO" => left($this->numero, 10),
                "CEP" => left($this->cep, 12),
                "TIPO" => left($this->tipo, 20)
            ));

        retornoPadrao($stmt, "Endereço atualizado com sucesso.", "Não foi possível atualizar o endereço");
    }

    public function delete()
    {
        $stmt = $this->deleteStatement("K_FN_PESSOAENDERECO", array("HANDLE" => $this->handle));

        retornoPadrao($stmt, "Endereço foi removido.", "Não foi possível remover o endereço");
    }

    public static function getListaEstados()
    {
        global $conexao;

        $sql = "SELECT * FROM ESTADOS ORDER BY NOME ASC";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        foreach ($f as $r) {
            $arr["uf"][] = $r->SIGLA;
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
        }
        return $arr;
    }

    // retorna o handle do estado de acordo com a uf
    public function getEstado($uf)
    {
        // transforma a uf em uppercase
        $uf = strtoupper($uf);

        // estados por uf
        $estado["AC"] = 8;
        $estado["AL"] = 9;
        $estado["AM"] = 10;
        $estado["AP"] = 11;
        $estado["BA"] = 12;
        $estado["CE"] = 13;
        $estado["DF"] = 14;
        $estado["ES"] = 15;
        $estado["EX"] = 48;
        $estado["GO"] = 16;
        $estado["MA"] = 17;
        $estado["MG"] = 18;
        $estado["MS"] = 19;
        $estado["MT"] = 20;
        $estado["PA"] = 21;
        $estado["PB"] = 22;
        $estado["PE"] = 23;
        $estado["PI"] = 24;
        $estado["PR"] = 3;
        $estado["RJ"] = 25;
        $estado["RN"] = 26;
        $estado["RO"] = 27;
        $estado["RR"] = 28;
        $estado["RS"] = 7;
        $estado["SC"] = 1;
        $estado["SE"] = 29;
        $estado["SP"] = 6;
        $estado["TO"] = 30;

        // verifica se não passou parâmetro errado
        if (!empty($estado["{$uf}"])) {
            return $estado["{$uf}"];
        } else {
            mensagem("A UF não foi encontrada no cadastro.", MSG_ERRO);
            finaliza();
        }
    }
}