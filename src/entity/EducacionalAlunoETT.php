<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 10:15
 */

namespace src\entity;

/* ==============================================================================================
     * aluno � uma extens�o de pessoa.
     * esta classe trata de forma separada e independente
     * todos os campos extra do educacional.
     */
class EducacionalAlunoETT extends PessoaETT
{
    // propriedades
    public $aluno;                    // booleano (S/N)
    public $professor;                // ||
    public $bolsa;                    // || para relat�rio "bolsa fam�lia" -- pode servir outras fun��es
    public $data_nascimento;
    public $data_adesao;
    public $matricula;
    public $sexo;                    // char (M/F)
    public $naturalidade;
    public $profissao;
    public $etnia;
    public $escolaridade;
    public $especializacao;            // usado para professores
    public $religiao;
    public $tipo_sanguineo;            // varchar(3) ex.: 'AB+'

    // JSON de especifica��o livre -- mantenha enxuto!
    public $dados_censo;
    public $campos_censo;            // GUI apenas -- formato $campo => $descri��o

    // gui apenas
    public $idade;                    // em anos (calculada em cima do nascimento)
    public $tipo_transtorno;        // puxa da �ltima ficha m�dica
    public $lista_transtornos;        // ||
    public $lista_avaliacao;        // ||
    public $responsavel;            // v�nculo de pai/m�e respons�vel

    /* inutilizados -- cadastro separado!
     * talvez manter apenas para fetch de turmas ativas? (ensino fundamental)
     */
    public $turma;
    public $cod_turma;
    public $turno;

    /* -----------------------------------------------------------------------------------
     * m�todos p�blicos
     */
    public function __construct()
    {
        // constr�i Pessoa
        parent::__construct();

        // define campos de especifica��o livre
        $this->campos_censo = array(
            "transferido" => "Aluno transferido (S/N)", // vou usar isso como hint para a interface
            "escolaOrigem" => "Escola de origem",
            "motivoTransf" => "Motivo transfer�ncia",
            "transporte" => "Transporte escolar (S/N)",
            "bairroTransp" => "Bairro",
            "autorizaEntrega" => "Para quem a crian�a poder� ser entregue",
            "autorizaImagem" => "Autoriza��o de uso de imagem (S/N)",
            "nBolsaFam" => "N� Bolsa Fam�lia",
            "resBolsaFam" => "Respons�vel Bolsa Fam�lia",
        );
    }

    public function cadastra()
    {
        parent::cadastra();

        // if(!$this->pre_cadastro) $this->atualiza(); // chama o atualiza do pai ou do filho?
    }

    public function atualiza()
    {
        parent::atualiza();

        // convers�o de formato dos dados de censo
        $json = json_encode($this->dados_censo);

        // atualiza campos educacionais
        $stmt = $this->updateStatement("K_FN_PESSOA",
            array(
                "HANDLE" => $this->handle,
                "ALUNO" => left($this->aluno, 1),
                "PROFESSOR" => left($this->professor, 1),
                "BOLSA" => left($this->bolsa, 1),
                "NASCIMENTO" => converteData($this->data_nascimento),
                "DATAADESAO" => converteData($this->data_adesao),
                "MATRICULA" => left($this->matricula, 15),
                "SEXO" => left($this->sexo, 1),
                "NATURALIDADE" => left($this->naturalidade, 30),
                "PROFISSAO" => left($this->profissao, 30),
                "ETNIA" => left($this->etnia, 20),
                "ESCOLARIDADE" => left($this->escolaridade, 30),
                "ESPECIALIZACAO" => left($this->especializacao, 100),
                "RELIGIAO" => left($this->religiao, 30),
                "TIPOSANGUINEO" => left($this->tipo_sanguineo, 3),
                "DADOSCENSO" => $json
            ));

        retornoPadrao($stmt, "Campos educacionais atualizados", "N�o foi poss�vel atualizar os campos educacionais");
    }

    // tabela para fichas de usu�rio
    public function geraResumo()
    {
        // monta pares chave->valor
        $dados = array();

        // contatos
        $telefones = "";
        $emails = "";

        if (!empty($this->contatos)) {
            foreach ($this->contatos as $contato) {
                if (!empty($contato->telefone)) $telefones .= "{$contato->telefone}, ";
                if (!empty($contato->email)) $emails .= "{$contato->email}, ";
            }
        }

        $telefones = trim($telefones, ", ");
        $emails = trim($emails, ", ");

        // especial do aluno
        if ($this->aluno == "S") {
            $dados["Matricula"] = $this->matricula;
            /*
            $dados["Turma"] = $this->turma;
            $dados["Turno"] = $this->turno_extenso;
            */
        }

        $dados["Nome"] = $this->nome;
        $dados["Data nascimento"] = converteDataSql($this->data_nascimento);
        $dados["Naturalidade"] = $this->naturalidade;
        $dados["Sexo"] = $this->sexo;
        $dados["Endere�o"] = "{$this->endereco->logradouro} {$this->endereco->complemento} {$this->endereco->numero} {$this->endereco->bairro}";
        $dados["Cidade"] = "{$this->endereco->cidade}/{$this->endereco->sigla_estado}";
        $dados["CEP"] = $this->endereco->cep;
        $dados["CPF"] = $this->cpf_cnpj;
        $dados["RG"] = $this->rg;
        $dados["Telefone"] = $telefones;
        $dados["E-mail"] = $emails;

        // dados dos campos livres
        if (!empty($this->dados_censo)) {
            foreach ($this->dados_censo as $key => $value) {
                $dados[$this->campos_censo[$key]] = $value;
            }
        }

        // monta lista/tabela
        // $html = "<table style='border: 1px solid black;'>";
        $i = 0;
        $html = "";

        foreach ($dados as $key => $value) {
            if ($i % 2 == 0) $html .= "<tr>";

            $html .= "<td style='width: 50%;'><b>{$key}:</b> {$value}</td>";

            if ($i % 2 == 1) $html .= "</tr>";

            $i++;
        }

        // $html .= "</table>";

        return $html;
    }
}