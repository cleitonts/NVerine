<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:15
 */

namespace src\services\Notifier;


class ChatFile {
    const SEPARADOR = "\\";

    // dados de acesso p�blico
    public $itens;
    public $ultimo_contador;

    // dados lidos no construtor que s�o guardados para edi��o
    private $usuario;
    private $txt;

    // ---------------------------------------------------------------
    // construtor � o fetch.
    public function __construct($usuario = null) {
        // inicializa mensagens n�o-lidas
        $this->itens = array();

        // contador inicial. isso valida a estrutura do arquivo
        $this->ultimo_contador = 1;

        // usu�rio passado por par�metro ou o padr�o (logado)
        $this->usuario = !empty($usuario) ? $usuario : $_SESSION["ID"];

        // essa string guarda o conte�do do arquivo para edi��o
        $this->txt = "";

        // l� o arquivo de mensagens, se existir
        $arquivo = "uploads/msg/{$this->usuario}.log";
        if(file_exists($arquivo)) $this->txt = file_get_contents($arquivo);

        if(!empty($this->txt)) {
            // faz a separa��o das linhas
            $linhas = explode("\n", $this->txt);

            foreach($linhas as $linha) {
                // faz a separa��o das partes da mensagem pelo delimitador ;
                $partes = explode(self::SEPARADOR, $linha);

                // insere nova mensagem no array
                $chat = new Chat();

                $chat->indice = $partes[0];
                $chat->lido = $partes[1] == "!" ? false : true;
                $chat->data = $partes[2];
                $chat->remetente = $partes[3];
                $chat->texto = $partes[4];

                // valida o contador
                if($chat->indice == $this->ultimo_contador) {
                    if(!$chat->lido) $this->itens[$chat->indice] = $chat;	// insere mensagem no array de n�o-lidas
                    $this->ultimo_contador++;								// incrementa
                }
                else {
                    // arquivo de mensagens mal formatado! (ou end-of-file)
                    break;
                }
            }

            // troca a ordem das mensagens
            $this->itens = array_reverse($this->itens, true);
        }
    }

    // concatena uma nova mensagem ao final do arquivo
    public function escreve($obj_chat) {
        // sanitiza a mensagem
        $obj_chat->texto = str_replace("\n", " ", $obj_chat->texto); // aparentemente ainda pode inserir CR sem problema
        $obj_chat->texto = str_replace(self::SEPARADOR, "/", $obj_chat->texto);

        // monta a linha a escrever
        $linha = $this->ultimo_contador.self::SEPARADOR;
        $linha .= "!".self::SEPARADOR;
        $linha .= agora().self::SEPARADOR;
        $linha .= $obj_chat->remetente.self::SEPARADOR;
        $linha .= $obj_chat->texto;

        // insere no conte�do
        if(!empty($this->txt)) $this->txt .= "\n";
        $this->txt .= $linha;

        // salva no arquivo
        if(file_put_contents("uploads/msg/{$this->usuario}.log", $this->txt)) {
            mensagem("Mensagem enviada ao usu�rio #{$this->usuario}");
        }
        else {
            mensagem("N�o foi poss�vel salvar a mensagem no servidor. Certifique-se da integridade dos diret�rios (/uploads/msg)", MSG_ERRO);
            finaliza();
        }
    }

    // marca as mensagens como lidas
    public function marcaLidas() {
        // remove o marcador [!]
        $this->txt = str_replace(self::SEPARADOR."!", self::SEPARADOR, $this->txt);

        // salva no arquivo
        if(file_put_contents("uploads/msg/{$this->usuario}.log", $this->txt)) {
            mensagem("Arquivo alterado.");
        }
        else {
            mensagem("N�o foi poss�vel marcar as mensagens como lidas. O arquivo n�o existe ou n�o h� permiss�es de escrita?", MSG_ERRO);
            finaliza();
        }
    }
}