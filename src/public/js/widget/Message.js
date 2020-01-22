/**
 * Renderiza as mensagens de retorno do sistema
 * tambem pode carregar mensagens no lugar do alert()
 */
class Message{
    static toggle(obj){
        // troca ação entre mostrar e ocultar
        if($(obj).hasClass("active")){
            $("#message-log").hide('slide', {}, 500, {});
        }
        else {
            $("#message-log").show("slide", {}, 500, {});
        }

        // troca classes para efeito visual
        $(obj).toggleClass("active");
        $(obj).find("i").toggleClass("fa-exclamation-triangle fa-times");
    }

    /**
     *
     * @param val
     * adiciona mensagens de retorno no final da lista
     */
    static adiciona(val){

        //itera cada linha
        for(var i = 0; i < val.length; i++){
            var li = document.createElement("li");
            li.className = "typo p-2 "+val[i].typo;

            var div = document.createElement("div");
            div.className = "clear";

            var info = document.createElement("span");
            info.appendChild(document.createTextNode(val[i].typo));
            info.className = "pull-left";

            var time = document.createElement("span");
            time.appendChild(document.createTextNode(val[i].timestamp));
            time.className = "pull-right";

            var text = document.createElement("p");
            text.className = "m-0";
            text.appendChild(document.createTextNode(val[i].text));

            // monta a estrutura da mensagem
            $(div).append(info);
            $(div).append(time);
            $(li).append(div);
            $(li).append(text);
            $("#lista-mensagens").prepend(li);

            var popup = new Alert();
            popup.typo = val[i].typo;
            popup.texto = val[i].text;
            popup.act_close = val[i].onclose;
            // instancia popup de erro
            if(val[i].typo != "danger"){
                popup.tempo = 5000;
            }

            popup.montaMensagem();
        }
    }
}
