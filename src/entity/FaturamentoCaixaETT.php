<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 29/05/2019
 * Time: 15:40
 */

namespace src\entity;


class FaturamentoCaixaETT extends FaturamentoDuplicataETT
{
// tipos de movimento
    const TIPO_ABERTURA = "A";
    const TIPO_FECHAMENTO = "F";
    const TIPO_SANGRIA = "S";
    const TIPO_REFORCO = "R";
    const TIPO_OUTROS = "O";

    // propriedades
    public $tipo_movimento;
    public $hora;
    public $historico;
    public $usuario;

    // ----------------------------------------------------------------------------
    public function __construct()
    {
        if (!__USA_CAIXA__) {
            return null;
        }
        $this->usuario = $_SESSION["ID"];

        parent::__construct(null); // sem vínculo de nota
    }

    public function cadastra()
    {
        global $conexao;

        // reforça data e hora corretas
        $this->data_emissao = hoje();
        $this->hora = date("H:m:s");

        // se for uma abertura, valida se o caixa já foi aberto
        if ($this->tipo_movimento == self::TIPO_ABERTURA) {
            $sql = "SELECT D.HANDLE, U.NOME
					FROM K_NOTADUPLICATAS D
					LEFT JOIN K_PD_USUARIOS U ON D.USUARIOCAIXA = U.HANDLE
					WHERE D.DATAEMISSAO = '" . converteData($this->data_emissao) . "'
					AND D.TIPOMOVIMENTO = 'A'
					AND D.CAIXA = '" . intval($this->numero_caixa) . "'";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $f = $stmt->fetch(PDO::FETCH_OBJ);

            if (!empty($f->HANDLE)) {
                mensagem("Caixa #{$this->numero_caixa} já foi aberto hoje pelo usuário '{$f->NOME}'", MSG_ERRO);
                finaliza();
            }
        }

        // cadastra movimento
        $this->handle = newHandle("K_NOTADUPLICATAS", $conexao);

        // insere
        $stmt = $this->insertStatement("K_NOTACOMISSAO",
            array(
                "HANDLE" => $this->handle,
                "NOTA" => null,
                "BAIXADO" => "N",
                "TIPOMOVIMENTO" => left($this->tipo_movimento, 1),
                "CAIXA" => intval($this->numero_caixa),
                "DATAEMISSAO" => $this->data_emissao,
                "DATAVENCIMENTOREAL" => $this->data_emissao,
                "HORA" => $this->hora,
                "HISTORICO" => left($this->historico, 250),
                "VALOR" => $this->valor,
                "VALORTOTAL" => $this->valor,
                "USUARIOCAIXA" => validaVazio($this->usuario),
            ));

        retornoPadrao($stmt, "Movimento cadastrado com sucesso no caixa #{$this->numero_caixa}", "Não foi possível cadastrar o movimento de caixa");
    }

    public function atualiza()
    {
        mensagem("Movimento de caixa não pode ser atualizado", MSG_ERRO);
        finaliza();
    }

    static public function getNomeTipo($tipo)
    {
        switch ($tipo) {
            case self::TIPO_ABERTURA:
                return "Abertura";
            case self::TIPO_FECHAMENTO:
                return "Fechamento";
            case self::TIPO_REFORCO:
                return "Reforço";
            case self::TIPO_SANGRIA:
                return "Sangria";
            case self::TIPO_OUTROS:
                return "Outras saídas";
            default:
                return "Mov. indefinido";
        }
    }
}