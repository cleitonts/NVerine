<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:03
 */

namespace src\services\UAC;

use src\entity\ObjectETT;
use src\services\Transact\ExtPDO as PDO;

class Perfil extends ObjectETT {
    // propriedades
    public $handle;					// código do usuário: para usuário atual, passar $_SESSION["ID"]
    public $pessoa;					// campo de busca pelo código da pessoa (não é a mesma coisa que cliente!)

    public $senha_atual;			// segurança extra
    public $senha;
    public $filial;					// para selecionar nova filial; puxar lista das filiais disponíveis por um método
    public $avatar;					// caminho para upload
    public $pagina_inicial; 		// página inicial do sistema para cada grupo
    public $terminal;				// controle de produção

    /* estas propriedades são usadas no cadastro.
     * o cadastro de novo usuário é feito aqui só por loja virtual/sistema integrado!
     */
    public $nome;
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
    public $ultimo_acesso;			// isso não é mais exibido em lugar nenhum
    public $dia_vencimento;			// dia do mês para pagamento de comissões, salários, etc.
    public $primeiro_nome;

    // métodos públicos
    public function cadastra() {
        global $conexao;

        // duplicidade de nome
        $sql = "SELECT * FROM K_PD_USUARIOS WHERE APELIDO = :login";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":login", $this->login);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_OBJ);

        if(!empty($r)) {
            mensagem("Já existe um cadastro com este nome de usuário ou e-mail", MSG_ERRO);
            finaliza();
        }

        // gera handle
        $this->handle = newHandle("K_PD_USUARIOS", $conexao);

        // insere dados
        $sql = "INSERT INTO K_PD_USUARIOS (HANDLE, NOME, APELIDO, SENHA, CNPJCPF, CLIENTE, NIVEL, GRUPO, EMAIL, TERMINAL)
				VALUES (:handle, :nome, :login, :senha, :cpf, :cliente, :nivel, :grupo, :email, :terminal)";
        $stmt = $conexao->prepare($sql);

        // encripta senha
        $senha = safercrypt($this->senha);

        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":nome", $this->nome);
        $stmt->bindValue(":login", $this->login);
        $stmt->bindValue(":senha", $senha);
        $stmt->bindValue(":cpf", $this->cpf);
        $stmt->bindValue(":cliente", $this->cliente);
        $stmt->bindValue(":nivel", $this->nivel);
        $stmt->bindValue(":grupo", $this->grupo);
        $stmt->bindValue(":email", $this->email);
        $stmt->bindValue(":terminal", $this->terminal);
        $stmt->execute();

        retornoPadrao($stmt, "Novo usuário cadastrado.", "Não foi possível cadastrar o novo usuário.");
    }

    public function atualiza() {
        global $conexao;

        // criptografa senhas
        $senha_atual = safercrypt($this->senha_atual);
        $senha = safercrypt($this->senha);

        // atualiza senha, se inserida
        if(!empty($this->senha))
            $sql = "UPDATE K_PD_USUARIOS SET SENHA = :senha, EMAIL = :email WHERE HANDLE = :handle AND SENHA = :senhaatual";
        else
            $sql = "UPDATE K_PD_USUARIOS SET EMAIL = :email WHERE HANDLE = :handle";

        $stmt = $conexao->prepare($sql);

        $stmt->bindValue(":handle", $this->handle);
        $stmt->bindValue(":email", $this->email);
        // $stmt->bindValue(":terminal", $this->terminal);

        // atualiza senha?
        if(!empty($this->senha)){
            $stmt->bindValue(":senhaatual", $senha_atual);
            $stmt->bindValue(":senha", $senha);
        }

        $stmt->execute();

        retornoPadrao($stmt, "Dados de cadastro atualizados com sucesso.", "Por favor, certifique-se de que a senha digitada é correta.");

        // atualiza imagem
        if(temArquivo($this->avatar)) {
            $up = new Upload();
            $up->anexo = $this->avatar;
            $url = $up->getUrl();

            $sql = "UPDATE K_PD_USUARIOS SET IMAGEM = :url WHERE HANDLE = :handle";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":handle", $this->handle);
            $stmt->bindValue(":url", $url);
            $stmt->execute();

            retornoPadrao($stmt, "Imagem de exibição atualizada com sucesso.",
                "O arquivo foi salvo, mas a informação não pôde ser escrita no banco de dados. Certifique-se da integridade das tabelas.");
        }

        // atualiza filiais
        $stmt = $this->deleteStatement("K_FN_USUARIOFILIAL", array("USUARIO" => $this->handle));
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
    public function reiniciaSenha() {
        global $conexao;

        // encripta senha
        $senha = safercrypt($this->senha);

        // atualiza senha por e-mail
        $sql = "UPDATE K_PD_USUARIOS SET SENHA = :senha WHERE EMAIL = :email AND GRUPO IS NULL";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":senha", $senha);
        $stmt->bindValue(":email", $this->email);
        $stmt->execute();

        retornoPadrao($stmt, "Senha reiniciada com sucesso", "E-mail não encontrado");
    }

    public function fetch() {
        global $conexao;

        // monta cláusula de busca por pessoa
        if(!empty($this->pessoa)) {
            $where = "WHERE P.HANDLE = :codigo";
            $codigo = $this->pessoa;
        }
        // se não é por pessoa, busca só pode ser por usuário
        else {
            // se não possuir usuário definido, puxar do usuário logado
            if(empty($this->handle)) $this->handle = $_SESSION["ID"];

            $where = "WHERE U.HANDLE = :codigo";
            $codigo = $this->handle;
        }

        // puxa dados de usuário
        $sql = "SELECT U.*,
				G.NOME AS NOMEGRUPO, G.PAGINAINICIAL,
				P.NOME AS NOMECLIENTE
				FROM K_PD_USUARIOS U
				LEFT JOIN K_FN_GRUPOUSUARIO G ON U.GRUPO = G.HANDLE
				LEFT JOIN K_FN_PESSOA P ON U.CLIENTE = P.HANDLE
				{$where}";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $codigo);
        $stmt->execute();

        $f = $stmt->fetch(PDO::FETCH_OBJ);
        $this->nome = formataCase($f->NOME, true);
        $this->login = $f->APELIDO;
        $this->senha = $f->SENHA;
        $this->email = $f->EMAIL;
        $this->terminal = $f->TERMINAL;
        $this->cpf = $f->CNPJCPF;
        $this->grupo = formataCase($f->NOMEGRUPO, true);
        $this->cod_grupo = $f->GRUPO;
        $this->cod_regiao = $f->REGIAO;
        $this->avatar = $f->IMAGEM;
        $this->pagina_inicial = $f->PAGINAINICIAL;
        $this->cliente = formataCase($f->NOMECLIENTE, true);
        $this->cod_cliente = $f->CLIENTE;
        $this->dia_vencimento = $f->VENCIMENTO;
        $this->nivel = $f->NIVEL;

        // imagem fallback
        if(empty($this->avatar)) $this->avatar = "ui2/img/default-user.png";

        // monta string de último acesso
        /*
        $dias = diasAtraso($_SESSION["DATAULTIMOLOGIN"]);

        $this->ultimo_acesso = "última visita: ";
        if($dias < 0)
            $this->ultimo_acesso .= "viajante do tempo";
        elseif($dias == 0)
            $this->ultimo_acesso .= "hoje";
        elseif($dias == 1)
            $this->ultimo_acesso .= "ontem";
        else
            $this->ultimo_acesso .= $dias." dias atrás";
        */

        // nome parcial
        $partes = explode(" ", $this->nome, 2);
        $this->primeiro_nome = $partes[0];
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