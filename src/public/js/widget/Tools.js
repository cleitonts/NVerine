/**
 * ferramentas e coias para normalização de dados
 */
class Tools{
    static capitalize(val){
        let captalize = val.toLowerCase();
        return captalize.charAt(0).toUpperCase() + captalize.slice(1);
    }

    static getUrlParameter(param) {
        const url = new URL(window.location.href);
        return (url.searchParams.get(param));
    }

    static showLog(){
        $(".log-wrapper").attr( "style", "display: block;" )
    }

    static hideLog(){
        $(".log-wrapper").attr( "style", "display: none;" )
    }

    static loadScript (url, callback) {
        jQuery.ajax({
            url: url,
            dataType: 'script',
            success: callback,
            async: true
        });
    }

    static retornar(padrao = ""){
        spinner(true);

        var pagina = Tools.getUrlParameter("retorno");

        // retorno que vem do php
        if(pagina == null){
            if(padrao.length ==0){
                window.location = "index.php";
            }
            else{
                Tools.redirect("?"+padrao);
            }
        }

        // retorno recebido da url
        else{
            // quebra url, o importante será somente os parametros
            pagina = pagina.split("?");

            // abre a pagina requisitada
            Tools.redirect("?"+pagina[1]);
        }
        spinner(false);
    }

    static redirect(url, back = false, limpa_log){
        // pinta spinner
        spinner(true);

        // se tem algum modal aberto, fechar
        $(".modal.show").modal("hide");

        $.ajax({
            url: "page.php" + url,
            dataType: "json",
            type: 'GET',
        }).done(function(valores){
            // cria widget
            let widget = new Creator();
            widget.create(valores, limpa_log);

            // atualiza sitemap
            let arr = valores.render.title.split("/");
            $("#modulo").text(arr[0]);
            $("#pagina").text(arr[1]);

            document.title = valores.render.title;

            // só adiciona a lista se não for false
            if(back === false) {
                window.history.pushState({href: url}, "", url);
            }
            spinner(false);

        }).fail(function(){
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Página não encontrada";
            popup.montaMensagem();
            spinner(false);
        });
    }

    static redirect_relatorio(url, back = false, limpa_log){
        window.location.replace("index.php" + url);
    }
}
