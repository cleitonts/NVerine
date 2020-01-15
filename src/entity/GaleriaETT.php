<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 04/07/2019
 * Time: 10:41
 */

namespace src\entity;


use src\creator\widget\Tools;
use src\services\Upload;

class GaleriaETT extends ObjectETT
{
    const TARGET_PESSOA = 0;
    const TARGET_PRODUTO = 1;
    const TARGET_FINANCEIRO = 2;
    const TARGET_SUPORTE = 3;

    const _CAMINHO_ = "uploads/galeria/";

    public $referencia; // handle da tabela de referencia
    public $target;     // diretorio
    public $dir;
    public $url;
    public $legenda;
    public $modelo;
    public $ativo;
    public $ordem;
    public $nome;

    public function __construct($target = "N")
    {
        if ($target == "N") {
            return;
        }

        $this->target = $target;
        $this->dir = self::getDir($this->target);

        // check se pasta existe
        if (!file_exists($this->dir)) {
            mkdir($this->dir, 0775, true);
        }
    }

    public function validaForm()
    {
        // campos obrigatorios
        validaCampo($this->target, "Target");
        validaCampo($this->nome, "Nome");
        validaCampo($this->referencia, "Referencia");
        validaCampo($this->legenda, "Legenda");
    }

    /**
     * @param $file
     * passar item a item do array
     */
    public function upload($file)
    {

        $tipo = explode(".", $this->nome);

        if(count($tipo) <> 2){
            mensagem("O unico ponto deve ser o da extensão do arquivo", MSG_ERRO);
            finaliza();
        }

        $this->nome = sanitize($tipo[0]).".".$tipo[1];
        $this->url = self::getDir($this->target) . $this->nome;

        if (in_array($this->target, array(self::TARGET_PESSOA, self::TARGET_PRODUTO))) {
            $this->checkImage();
        }

        $this->checkExists();
        $this->checkSize($file);

        $this->ativo = 'S';
        $this->modelo = 1;
        $this->ordem = 2;
        $this->cadastra();

        /**
         * essa parte precisa ser a ultima para o rollback funcionar corretamente
         */
        Upload::upload($this->dir . $this->nome, $file["tmp_name"], $this->nome);
    }

    /**
     * salva os dados na tabela
     */
    public function cadastra()
    {
        global $conexao;

        $this->validaForm();
        $this->handle = newHandle("K_GALERIA", $conexao);

        $stmt = $this->insertStatement("K_GALERIA",
            array(
                "HANDLE" => $this->handle,
                "TARGET" => $this->target,
                "REFERENCIA" => $this->referencia,
                "URL" => left($this->url, 250),
                "LEGENDA" => left($this->legenda, 250),
                "MODELO" => $this->modelo,
                "ATIVO" => left($this->ativo, 1),
                "ORDEM" => $this->ordem
            ));

        retornoPadrao($stmt, "Nova foto registrada na galeria", "Não foi possível registrar a nova foto na galeria");
    }

    // atualiza informações da imagem, inclusive o nome
    public function atualiza($old_nome)
    {
        global $conexao;
        $this->validaForm();

        $url = self::getDir($this->target);
        $old_url = $url . $old_nome;
        $this->url = $url . $this->nome;

        $this->checkImage();
        $this->checkExists();

        $sql = "UPDATE K_GALERIA SET URL = :url, LEGENDA = :legenda, ATIVO = :ativo, ORDEM = :ordem
                WHERE TARGET = :target AND REFERENCIA = :referencia AND URL = '{$old_url}'";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":target", $this->target);
        $stmt->bindValue(":referencia", $this->referencia);
        $stmt->bindValue(":url", left($this->url, 250));
        $stmt->bindValue(":legenda", left($this->legenda, 250));
        $stmt->bindValue(":ativo", left($this->ativo, 1));
        $stmt->bindValue(":ordem", $this->ordem);
        $stmt->execute();

        retornoPadrao($stmt, "Imagem {$old_nome} atualizada.", "Não foi possível atualizar a imagem {$old_nome}");

        // ultima coisa a fazer
        if (file_exists($this->dir . $old_nome)) {
            rename($this->dir . $old_nome, $this->dir . $this->nome);
            mensagem("Arquivo atualizado na pasta");
        } else {
            mensagem("Arquivo não foi encontrado na pasta", MSG_ERRO);
            finaliza();
        }
    }

    // deleta imagem e apaga na tabela
    public function delete()
    {
        global $conexao;

        $this->url = self::getDir($this->target) . $this->nome;

        // ultima coisa a fazer, para rollback funcionar corretamente
        $sql = "DELETE FROM K_GALERIA WHERE TARGET = :target AND URL = :url AND REFERENCIA = :referencia";

        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":target", $this->target);
        $stmt->bindValue(":url", $this->url);
        $stmt->bindValue(":referencia", $this->referencia);
        $stmt->execute();

        mensagem("Arquivo {$this->nome} foi removido do banco de dados!");

        if (file_exists($this->dir . $this->nome)) {
            unlink($this->dir . $this->nome);
            mensagem("Arquivo deletado da pasta");
        } else {
            mensagem("Arquivo não foi encontrado na pasta");
        }
    }

    /**
     * verifica se está tentando subir uma imagem
     */
    private function checkImage()
    {
        $imageFileType = strtolower(pathinfo($this->dir . "/" . $this->nome, PATHINFO_EXTENSION));

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif") {
            mensagem("Desculpe, somente JPG, JPEG, PNG e GIF são permitidos", MSG_ERRO);
            finaliza();
        }
    }

    private function checkExists()
    {
        // Check if file already exists
        if (file_exists($this->dir . "/" . $this->nome)) {
            mensagem("Um arquivo com o mesmo nome ja existe.", MSG_ERRO);
            finaliza();
        }
    }

    private function checkSize($file)
    {
        // Check file size
        if ($file["size"] > 50000000) {
            mensagem("Desculpe, seu arquivo é muito grande (> 50mb)", MSG_ERRO);
            finaliza();
        }
    }

    // caminho parcial, tanto para url quanto para pasta
    public static function getPath($target)
    {
        return strtolower(sanitize(str_replace(" ", "", __SISTEMA__))) . "/" . self::getTarget($target);
    }

    // retorna somente pasta interna
    public static function getTarget($target)
    {
        switch ($target) {
            case self::TARGET_PESSOA :
                return "pessoa/";
            case self::TARGET_FINANCEIRO :
                return "financeiro/";
            case self::TARGET_PRODUTO :
                return "produto/";
            case self::TARGET_SUPORTE:
                return "suporte/";
            default:
                return "Indefinido";
        }
    }

    // retorna caminho completo para a pasta
    public static function getDir($target)
    {
        return self::_CAMINHO_ . self::getPath($target);
    }
}