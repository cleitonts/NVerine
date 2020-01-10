/**
 * faz o request de dados e renderiza em uma tabela
 */
class Tabelas{
    constructor(obj, name){
        let wrapper_table = document.createElement("div");
        wrapper_table.id = name;
        wrapper_table.className = "card-body";
        wrapper_table.style = "overflow:hidden;";

        this.class = "Faturamento\\NotaGUI";
        this.pesq_pagina = 1;
        this.only_headers = false;
        this.pesq_top = 10;
        this.ordena = 0;
        this.handle = null;
        this.destino = false;
        this.check = false;
        this.obj = wrapper_table;
        this.id = wrapper_table.id;
        this.tabela = document.createElement("table");
        this.tabela.id = "tabela_"+this.id;
        this.tabela.className = "table table-striped mb-1";

        let table_scroll = document.createElement("div");
        table_scroll.className = "table-responsive";
        table_scroll.style = "width:100%;";
        
        $(table_scroll).append(this.tabela);
        $(wrapper_table).append(table_scroll);
        $(obj).append(wrapper_table);

        Tabelas.geraPaginas(obj);
        //this.geraEdicao();
        this.atualizaNavegacao();
    }

    sethandle(obj) {
        var elemento = this;
        let handle = $(obj).attr("num");

        let td = document.createElement("td");
        td.className = "tabc";

        let div = document.createElement("div");
        div.className = "btn btn-warning btn-round btn-sm m-0 btn-fab";
        div.id = "uniform-campo_sel_" + handle;
        //div.title = "editar";

        let icon = document.createElement("i");
        icon.className = "material-icons";
        $(icon).append(document.createTextNode("edit"));

        let retorno = window.location.href + "&" + $("#form_pesquisa").serialize();

        let url = elemento.destino.replace("XhandleX", handle) + "&retorno=" + encodeURIComponent(retorno);

        $(div).attr("onclick", "Tools.redirect('" + url + "');");
        $(div).append(icon);
        $(td).append(div);
        $(obj).prepend(td);

        // adiciona checkbox
        if (elemento.check == 1) {
            let td = document.createElement("td");
            td.className = "tabc";

            let div = document.createElement("div");
            div.className = "form-check form-check-inline";

            let label = document.createElement("label");
            label.className = "form-check-label";

            let input = document.createElement("input");
            input.type = "checkbox";
            input.className = "form-check-input";
            input.value = handle;
            input.name = "sel_" + handle;
            input.setAttribute("num", handle);

            let sign = document.createElement("span");
            sign.className = "form-check-sign";

            let check = document.createElement("span");
            check.className = "check";

            // monta a arvore
            $(sign).append(check);
            $(label).append(input);
            $(label).append(sign);
            $(div).append(label);
            $(td).append(div);
            $(obj).prepend(td);
        }
    }

    acoes(){
        var elemento = this;
        // $("#pag-"+this.id+" a").each(function(){
        //     $(this).on("click", function(){
        //         // elemento.pesq_pagina = parseInt($(this).attr("num"));
        //         // elemento.atualizaNavegacao($("#"+elemento.id));
        //         // elemento.send();
        //         //elemento.acoes();
        //     });
        // });

        // $("#nav-"+this.id+" #campo_pesq_top").change(function(){
        //     elemento.pesq_top = parseInt($(this).val());
        //     elemento.send();
        // });

        // ação de ordenação
        $("#tabela_"+this.id+" th").click(function(){
            elemento.ordena = $(this).attr("num");
            elemento.send();
        });
    }

    // gera o botão de edição
    geraEdicao(){
        var elemento = this;
        var edicao = document.createElement('div');
        edicao.className = "tabela_edicao";
        edicao.id = "editor-"+this.id;
        edicao.innerHTML = "<div class='round pull-right'><i class='fa fa-pencil'></i></div> \n";

        edicao.onclick = function () {
            new EditaTabela(elemento);
        };
        $(this.obj).before(edicao);
    }

    // gera o controle de paginas
    static geraPaginas(obj){
        var div = document.createElement("div");
        div.className = "col-md-12 p-0";

        var select = {
            class: "",
            description: "Registros por página",
            function: "tabelas.setTop(this)",
            icon: "",
            name: "pesq_top",
            options: [
                {description: 10, value: 10, checked: 'S'},
                {description: 25, value: 25, checked: 'N'},
                {description: 50, value: 50, checked: 'N'},
                {description: 100, value: 100, checked: 'N'},
            ],
            value: "10",
            size: "3",
            type: "SELECT"
        };

        Field.select(div, select);
        $(obj).prepend(div);
    }

    setTop(obj){
        this.pesq_top = parseInt($(obj).val());
        this.send();
    }

    // gera o rodape de navegações
    atualizaNavegacao(){
        var elemento = this;

        // limpa antes de inserir novamente
        if($("#pag-"+this.id).length > 0) {
            $("#pag-"+this.id).remove();
        }

        // cria nova navegação
        var navegacao = document.createElement("nav");
        navegacao.id = "pag-"+this.id;

        var group = document.createElement("ul");
        group.className = "pagination pagination-info";

        var starter = this.pesq_pagina - 3;
        var finish = this.pesq_pagina + 4;

        var cont = 0;
        while (starter < finish){
            // pula qando item for negativo
            if(starter <= 0){
                cont++;
                starter++;
                continue;
            }
            var li = document.createElement("li");
            li.className = "page-item";
            if(cont == 3){
                li.className = li.className + " active";
            }
            var button = document.createElement("a");
            button.id = "paginate_button";
            button.className = "page-link";
            button.setAttribute("num", (starter));
            button.appendChild(document.createTextNode(starter));
            button.onclick =  function(){
                elemento.pesq_pagina = parseInt($(this).attr("num"));
                elemento.atualizaNavegacao();
                elemento.send();
            };
            $(li).append(button);
            $(group).append(li);

            cont++;
            starter++;
        }

        $(navegacao).append(group);
        $(this.obj).append(navegacao);
    }

    // chamar esta função quando clicar no botao de pesquisa para serializar o formulario
    send(){
        this.getDados();
    }

    // monta o array de dados
    montapesquisa(){
        var dados = [];
        dados.push(
            {name: "pesq_top", value: this.pesq_top},
            {name: "pesq_pagina", value: this.pesq_pagina},
            {name: "ordena_por", value: this.ordena},
            {name: "class", value: this.class},
            {name: "only_headers", value: this.only_headers}
        );

        return dados;
    }

    getDados(referencia = false){
        // antes de mais nada printa o spinner
        spinner(true);

        let myform = "";

        if(referencia) {
            myform = $(referencia).find("#form_pesquisa");
        }
        else{
            myform = $("#form_pesquisa");
        }

        // Find disabled inputs, and remove the "disabled" attribute
        let disabled = myform.find(':disabled').removeAttr('disabled');

        // envia todos os forms como o mesmo nome
        let submit = myform.serializeArray();

        // re-disabled the set of inputs that you previously enabled
        disabled.attr('disabled','disabled');

        submit.push({name: "form_name", value: "form_pesquisa"});

        // ajax nao funciona muito bem com o this
        let elemento = this;

        // monta a pesquisa em função separada
        let pesquisa = elemento.montapesquisa();

        // completa com o formulario de pesquisa
        if(typeof submit !== "undefined"){
            for(var r in submit){
                pesquisa.push(submit[r]);
            }
        }

        $.ajax({
            url: "tabela.php",
            dataType: "json",
            type: 'POST',
            data: pesquisa
        }).done(function(valores){
            if (valores.render != null) {
                if(!elemento.only_headers) {
                    // cria a wrapper da header
                    let header = document.createElement("thead");
                    //$(header).append(document.createElement("tr"));
                    Tabelas.montaHeader(valores.render.header, header);

                    // cria a wrapper da body
                    let body = document.createElement("tbody");
                    elemento.montaBody(valores.render.itens, body);    // não pode ser chamada static

                    //limpa dados antigos
                    elemento.tabela.innerHTML = "";

                    // salva novos dados
                    $(elemento.tabela).append(header);
                    $(elemento.tabela).append(body);

                    elemento.acoes();

                    $(document).trigger('tabela_carregada', [valores]);
                    // por ultimo, remove o spinner
                    spinner(false);
                }
                // else{
                //     obj.opcoes = valores.header;
                //     back(obj);
                //     spinner(false);
                // }
            }

            if(valores.dev_log.length != 0) {

                // renderiza os prints
                DevInfo.init(false);
                DevInfo.renderDump(valores.dev_log);
            }
        })
            .fail(function(){
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Tabela não encontrada";
                popup.montaMensagem();
                spinner(false);
            });
    }

    static montaHeader(header, obj){
        $(obj).prepend("<th num='0'>&nbsp;</th>");
        if(tabelas.check == 1) $(obj).prepend("<th num='0'>&nbsp;</th>");
        for (let i = 0; i <= header.length -1; i++) {

            // cria a th e o texto
            let th = document.createElement("th");
            th.setAttribute("num", i);
            th.appendChild(document.createTextNode( header[i]));

            // cria o icon e joga no inicio
            let icon = document.createElement("i");
            icon.className = "icon-sort";
            $(th).prepend(icon);

            // joga no objeto da tbody
            $(obj).append(th);
        }
    }

    montaBody(dados, obj){
        if (typeof(dados) == "undefined") {
            let linha = document.createElement("tr");
            linha.className = "odd";

            // monta td
            let td = document.createElement("td");
            td.className = "dataTables_empty";
            td.colSpan = 3;
            td.style = "valign:top;";
            td.appendChild(document.createTextNode("Sem dados na tabela"));

            // joga td dentro da tr
            $(linha).append(td);
            $(obj).append(linha);
        }
        else {
            for (let i = 0; i <= dados.length - 1; i++) {
                let linha = document.createElement("tr");
                linha.setAttribute("num", dados[i].handle);

                // montas as tds
                Tabelas.montaLinha(dados[i], linha);

                // so carrega se tiver dados nas tabelas
                this.sethandle(linha);

                // joga no objeto da tbody
                $(obj).append(linha);
            }
        }
    }

    static montaLinha(dados, obj){
        for (let i = 0; i <= dados.colunas.length -1; i++) {
            let td = document.createElement("td");
            td.className = "tabc "+dados.classes[i];
            td.innerHTML = dados.colunas[i];

            // joga no objeto da linha
            $(obj).append(td);
        }
    }
}
