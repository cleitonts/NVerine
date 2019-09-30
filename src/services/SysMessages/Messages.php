<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:43
 */

namespace src\services\SysMessages;

use src\creator\widget\Tools;

/* setar GLASS_VERBOSE como falso vai fazer o sistema esconder toda mensagem que n�o for MSG_SUCESSO ou MSG_AVISO.
 * se ocorrer um erro, o log completo de mensagens ser� exibido.
 *
 * o sistema N�O est� maduro para usar essa configura��o enquanto:
 * - houver mensagens que n�o passam as tags corretas (especialmente MSG_ERRO no evento de erro)
 * - houver mensagens escondidas na marra [chamadas como: retornoPadrao($stmt, "", "Minha mensagem de erro")]
 * - o fluxo do actions n�o chamar iniciaTransacao, redir, finaliza ou $mensagens->pronto() corretamente
 */

define("__GLASS_VERBOSE__", true);

/* usu�rio que recebe log nas mensagens.
 * 1 = gestorweb
 */
define("__LOG_USUARIO__", 1);


class Messages {
    public $itens;			    // array de Mensagem()

    public $erro;			    // trap se um erro for gerado
    public $aviso;			    // trap se um aviso for gerado
    public $nao_continua; 	    // alias de erro (interrompe o bot�o "continuar")
    public $force_json = true;  // obriga retonro em JSON
    public $retorno;            // url de retorno para javascript atualizar a pagina

    /* tipos de mensagem a ocultar.
     * tem que ser um par�metro global e consistente -- n�o deixe isso p�blico nem fa�a uma fun��o set!
     */
    private $esconde;

    // -----------------------------------------------------------------------------------------
    // m�todos p�blicos
    public function __construct() {
        $this->itens = array();
        $this->erro = false;
        $this->aviso = false;
        $this->nao_continua = &$this->erro;

        if(__GLASS_VERBOSE__)
            $this->esconde = array();
        else
            $this->esconde = array(MSG_PADRAO, MSG_DEBUG);
    }

    public function set($texto, $tipo = MSG_PADRAO) {
        $mensagem = new Message($texto, $tipo);

        /* s� insere mensagem debug se tiver o par�metro global.
         * assim n�o precisamos encapsular as mensagens com if(__GLASS_DEBUG__) {...}
         */
        if($tipo != MSG_DEBUG || __GLASS_DEBUG__) {
            array_push($this->itens, $mensagem);
        }

        // seta a armadilha de erro
        if($tipo == MSG_ERRO) {
            $this->erro = true;
            $this->esconde = array(); // se houve erro, vai mostrar todas.
        }

        // seta a armadilha de aviso
        elseif($tipo == MSG_AVISO) {
            $this->aviso = true;
        }
    }

    /* s� � necess�rio chamar essa fun��o se voc� quiser que a pr�pria classe renderize as mensagens no iframe.
     * os itens s�o acess�veis publicamente para leitura um a um
     */
    public function pronto()
    {
        /* isso � importante: n�o cuspa nada que v� quebrar a formata��o do json
         * a fun��o n�o foi feita pra isso. trate o retorno de erro como um json!
         */
        if (strpos($_SERVER["PHP_SELF"], "json") !== false) return;

        // retorno de mensagens for�ando json
        if ($this->force_json) {
            global $dumper;

            // {message: [{typo: "Alert", text: "texto", timestamp: "time"}]}
            $message = array();

            foreach ($this->itens as $r) {
                // lan�a erro caso url retorno n�o for informada
                if (empty($this->retorno)) {
                    $linha["text"] = utf8_encode("Url retorno n�o informada");
                    $time = explode(".", $r->getTimestamp());
                    $linha["timestamp"] = utf8_encode($time[0]);
                    $linha["typo"] = "danger";

                    $message[] = $linha;
                    break;
                }

                switch ($r->getTipo()) {
                    case MSG_ERRO:
                        if ($this->retorno == "refresh") {
                            $linha["onclose"] = "location.reload()";
                        }
                        $linha["typo"] = "danger";
                        $this->retorno = null;
                        break;

                    case MSG_AVISO:
                        $linha["typo"] = "warning";
                        break;

                    case MSG_SUCESSO:
                        $linha["typo"] = "success";
                        break;

                    case MSG_DEBUG:
                        $linha["typo"] = "info";
                        break;

                    default:
                        $linha["typo"] = "info";
                        break;
                }
                $linha["text"] = utf8_encode(strip_tags($r->getTexto()));
                $time = explode(".", $r->getTimestamp());
                $linha["timestamp"] = utf8_encode($time[0]);

                $message[] = $linha;
            }
            // faz o push dos prints com o retorno padr�o
            if (!__DEVELOPER__) $dumper->dumped = "";
            $json["dev_log"] = Tools::toUTF8($dumper->dumped);
            $json["messages"] = $message;
            $json["retorno"] = utf8_encode($this->retorno);
            print_r(json_encode($json));
            return;
        }
    }
}