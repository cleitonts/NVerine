/**
 * @type {Alert}
 *
 * como usar:
 * prencher as propriedades da view com a mensagem a ser mostrada pro usuarios
 *
 * sempre invocar 'Alerta'
 *
 * é somente uma classe que controla todas as mensagens e as views de cada uma,
 * apos criado o controle deve ser feito no DOM
 *
 * preencher as ações que os botoes irão executar, não é possivel usar return dentro da classe
 *
 * http://bootstrap-notify.remabledesigns.com/
 */

//controla os alerts da pagina
class Alert{
    constructor(){
        // composição da mensagem
        this.tempo = null; // tempo em ms; se setado não exibe os botões
        /**
         * Error
         * Info
         * Success
         */
        this.typo = "";
        this.texto = "";
        this.act_close = "";
        this.altura = 0;
    }

    montaMensagem() {
        var elemento = this;

        var not = {
            // options
            //icon: 'notifications',
            //title: 'Bootstrap notify',
            message: this.texto,
            //url: 'https://github.com/mouse0270/bootstrap-notify',
            //target: '_blank'
        };

        var setings = {
            //element: 'body',
            //position: null,
            type: this.typo,
            //allow_dismiss: true,
            newest_on_top: true,
            //showProgressbar: false,
            /*placement: {
                from: "top",
                align: "right"
            },*/
            offset: {
                x: 20,
                y: 130
            },
            spacing: 10,
            //z_index: 1031,
            delay: 3000,
            timer: 1000,
            //url_target: '_blank',
            //mouse_over: null,
            /*animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },*/
            //onShow: null,
            //onShown: null,
            //onClose: null,
            //onClosed: null,
            //icon_type: 'class',
            template:   '<div data-notify="container" class="col-12 col-sm-3 col-md-3 col-xs-3 alert alert-{0}" role="alert">' +
                            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                            '<i data-notify="icon" class="material-icons">notifications</i> ' +
                            '<span data-notify="title">{1}</span> ' +
                            '<span data-notify="message">{2}</span>' +
                            '<div class="progress" data-notify="progressbar">' +
                                '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                            '</div>' +
                            '<a href="{3}" target="{4}" data-notify="url"></a>' +
                        '</div>'
        };

        if(this.typo.toLowerCase() == 'danger'){
            not.icon = 'notifications';
            setings.delay = 0;
            setings.onClose = function (){
                $(".dark-overlay").hide();
            };

            setings.template = setings.template.replace("col-sm-3 col-md-3 col-xs-3", "col-sm-4 col-md-4 col-xs-4");

            if(typeof elemento.act_close != "undefined"){
                setings.onClose = function (){
                    eval(elemento.act_close);
                    $(".dark-overlay").hide();
                };
            }

            setings.placement = {
                from: "top",
                align: "center"
            };

            $(".dark-overlay.overlay-message").show();
        }

        $.notify(not, setings);
    }
}