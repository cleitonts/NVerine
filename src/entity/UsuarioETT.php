<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 04/06/2019
 * Time: 14:34
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;
use Upload;

class UsuarioETT extends ObjectETT
{
    public $pessoa;                 // campo de busca pelo código da pessoa (não é a mesma coisa que cliente!)
    public $senha_atual;            // segurança extra
    public $senha;
    public $filial;                 // para selecionar nova filial; puxar lista das filiais disponíveis por um método
    public $avatar;                 // caminho para upload
    public $pagina_inicial;         // página inicial do sistema para cada grupo
    public $terminal;               // controle de produção

    /* estas propriedades são usadas no cadastro.
     * o cadastro de novo usuário é feito aqui só por loja virtual/sistema integrado!
     */
    public $login;
    public $email;
    public $cpf;
    public $cliente;
    public $nivel;
    public $grupo;
    // public $regiao;

    // para gui
    public $cod_cliente;
    public $cod_grupo;
    public $cod_regiao;
    public $ultimo_acesso;            // isso não é mais exibido em lugar nenhum
    public $dia_vencimento;           // dia do mês para pagamento de comissões, salários, etc.
    public $primeiro_nome;

    // métodos públicos
    public function cadastra()
    {
        global $conexao;

        // duplicidade de nome
        $sql = "SELECT * FROM K_PD_USUARIOS WHERE APELIDO = :login";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":login", $this->login);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_OBJ);

        if (!empty($r)) {
            mensagem("Já existe um cadastro com este nome de usuário ou e-mail", MSG_ERRO);
            finaliza();
        }

        // gera handle
        $this->handle = newHandle("K_PD_USUARIOS", $conexao);

        // encripta senha
        $senha = safercrypt($this->senha);

        // insere
        $stmt = $this->insertStatement("K_PD_USUARIOS",
            array(
                "HANDLE" => $this->handle,
                "NOME" => $this->nome,
                "APELIDO" => $this->login,
                "SENHA" => $senha,
                "CNPJCPF" => $this->cpf,
                "CLIENTE" => $this->cliente,
                "NIVEL" => $this->nivel,
                "GRUPO" => $this->grupo,
                "EMAIL" => $this->email,
                "TERMINAL" => $this->terminal,
            ));

        retornoPadrao($stmt, "Novo usuário cadastrado.", "Não foi possível cadastrar o novo usuário.");
    }

    public function atualiza()
    {
        $senha = safercrypt($this->senha);

        if (!empty($this->senha)){
            $stmt = $this->updateStatement("K_PD_USUARIOS",
                array(
                    "HANDLE" => $this->handle,
                    "EMAIL" => $this->email,
                    "SENHA" => $senha
                ));
        }
        else {
            $stmt = $this->updateStatement("K_PD_USUARIOS",
                array(
                    "HANDLE" => $this->handle,
                    "EMAIL" => $this->email,
                ));
        }

        retornoPadrao($stmt, "Dados de cadastro atualizados com sucesso.", "Por favor, certifique-se de que a senha digitada é correta.");

        // atualiza imagem
        if (temArquivo($this->avatar)) {
            $up = new Upload();
            $up->anexo = $this->avatar;
            $url = $up->getUrl();

            $stmt = $this->updateStatement("K_PD_USUARIOS",
                array(
                    "HANDLE" => $this->handle,
                    "IMAGEM" => $url,
                ));

            retornoPadrao($stmt, "Imagem de exibição atualizada com sucesso.",
                "O arquivo foi salvo, mas a informação não pôde ser escrita no banco de dados. Certifique-se da integridade das tabelas.");
        }

        // atualiza filiais
        $this->deleteStatement("K_FN_USUARIOFILIAL", array("USUARIO" => $this->handle));
        $stmt = $this->insertStatement("K_FN_USUARIOFILIAL", array(
            "HANDLE" => newHandle("K_FN_USUARIOFILIAL"),
            "USUARIO" => $this->handle,
            "FILIAL" => $this->filial,
            "PRIORIDADE" => 1));

        retornoPadrao($stmt, "Filial alterada com sucesso.", "Erro alterando filial");
    }

    /* precisa de uma forma de travar isso a apenas os usuários da loja virtual!
     * (grupo = null?)
     */
    public function reiniciaSenha()
    {
        // encripta senha
        $senha = safercrypt($this->senha);

        $stmt = $this->updateStatement("K_PD_USUARIOS",
            array(
                "HANDLE" => $this->handle,
                "EMAIL" => $this->email,
                "SENHA" => $senha
            ), "AND GRUPO IS NULL");

        retornoPadrao($stmt, "Senha reiniciada com sucesso", "E-mail não encontrado");
    }

    public function listaFiliais() {
        global $conexao;

        // níveis de acesso
        if($this->nivel == 1 && !__PRODUCAO__) {
            $sql = "SELECT F.*
					FROM K_FN_FILIAL F
					WHERE F.HANDLE = :filial";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":filial", __FILIAL__);
        }
        elseif($this->nivel == 2 && !__PRODUCAO__) {
            $sql = "SELECT F.*
					FROM K_FN_FILIAL F
					LEFT JOIN K_FN_USUARIOFILIAL UF ON UF.USUARIO = :handle AND UF.FILIAL = F.HANDLE
					WHERE F.REGIAO = :regiao
					ORDER BY UF.PRIORIDADE DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":handle", $this->handle);
            $stmt->bindValue(":regiao", $this->cod_regiao);
        }
        else {
            $sql = "SELECT F.*
					FROM K_FN_FILIAL F";
            $stmt = $conexao->prepare($sql);
        }
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // monta array de filiais
        $labels = array();
        $values = array();

        foreach($f as $r) {
            $labels[] = $r->NOME;
            $values[] = $r->HANDLE;
        }

        return array($labels, $values);
    }
}