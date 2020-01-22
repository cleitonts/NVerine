/**
 * Carrega um grafico curtomizado
 */
class CustomChart{
    create(val, obj) {
        var wrapper = document.createElement("div");
        wrapper.className = "chart-wrapper grid-"+val.size;

        this.body = document.createElement("div");
        this.body.className = "chart-body";

        // faz a conexao e retorna os dados;
        this.getDados(val, []);

        $(wrapper).append(this.body);
        $(obj).append(wrapper);
    }

    // esta função configura botão de enviar pesquisa
    btnSend(val, html){

        var elemento = this;

        $(html).find("#campo_pesquisar").click(function(e){
            e.preventDefault();
            elemento.getDados(val, $("#form_pesquisa").serializeArray());
        });
    }

    // faz o request dos dados para a charts.php
    getDados(val, obj = []){
        // antes de mais nada printa o spinner
        spinner(true);
        var elemento = this;

        if(typeof elemento.ctx != "undefined"){
            elemento.ctx.remove();
        }

        this.ctx = document.createElement("canvas");
        this.ctx.id = val.name;

        $(this.body).append(this.ctx);

        obj.push(
            {name: "class", value: val.entity},
        );

        $.ajax({
            url: "charts.php",
            dataType: "json",
            type: 'POST',
            data: obj
        }).done(function(valores){
            if (valores != null) {
                obj.opcoes = valores.header;
                window[val.name](valores, elemento.ctx);

                spinner(false);
            }
        }).fail(function(){
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Gráfico não encontrado";
            popup.montaMensagem();
            spinner(false);
        })
    }
}
