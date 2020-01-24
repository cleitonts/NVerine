/**
 * faz o request de dados e renderiza em uma tabela
 */
class Tabelas{
    constructor(obj, table){
        this.check = table.check;
        this.destino = table.target + "&pesq_num=XhandleX";
        this.class = table.entity;
        this.relatorio = table.relatorio;
        if(typeof this.relatorio === "undefined"){
            this.relatorio = 0;
        }

        let wrapper_table = document.createElement("div");
        wrapper_table.id = table.name;
        wrapper_table.className = "card-body";
        wrapper_table.style = "overflow:hidden;";

        this.pesq_pagina = 1;
        this.only_headers = false;
        this.pesq_top = 10;
        this.ordena = 0;
        this.handle = null;
        this.obj = wrapper_table;
        this.tabela = document.createElement("table");
        this.tabela.className = "table table-striped mb-1";

        if(this.relatorio == 1){
            this.tabela.className += " relatorio";
            wrapper_table.id = "relatorio_"+table.name;
        }

        this.id = wrapper_table.id;
        this.tabela.id = "tabela_"+this.id;

        let table_scroll = document.createElement("div");
        table_scroll.className = "table-responsive";
        table_scroll.style = "width:100%;";
        
        $(table_scroll).append(this.tabela);
        $(wrapper_table).append(table_scroll);
        $(obj).append(wrapper_table);

        if(this.relatorio == 0) {
            this.geraPaginas(obj);
            //this.geraEdicao();
            this.atualizaNavegacao();
        }
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

        let temp = window.location.href.split("?");

        let retorno =  "?" + temp[1] + "&" + $("#form_pesquisa").serialize();

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
    geraPaginas(obj){
        const elemento = this;
        const div = document.createElement("div");
        div.className = "col-md-12 p-0";

        const select = {
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
                {description: "Todos", value: "", checked: 'N'},
            ],
            value: "10",
            size: "3 col-6",
            type: "SELECT"
        };

        const button = {
            class: "btn-info",
            options: [],
            icon: "",
            description: "Editar tabela",
            function: "",
            name: "edita_tabela",
            size: "3 col-6 mt-4 float-right",
            type: "BUTTON"
        };

        Field.select(div, select);
        Field.button("button", div, button);
        $(div).find("#campo_edita_tabela").click(function () {

            $(document).on('tabela_'+elemento.id, function (e, e1) {
                elemento.editaTabela(e1);
                elemento.only_headers = false;
            });

            elemento.only_headers = true;
            elemento.getDados();
        });
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
            {name: "only_headers", value: this.only_headers},
            {name: "nome_tabela", value: this.tabela.id}
        );

        return dados;
    }

    getDados(referencia = false) {
        // antes de mais nada printa o spinner
        spinner(true);

        let myform = "";

        if (referencia) {
            myform = $(referencia).find("#form_pesquisa");
        } else {
            myform = $("#form_pesquisa");
        }

        // Find disabled inputs, and remove the "disabled" attribute
        let disabled = myform.find(':disabled').removeAttr('disabled');

        // envia todos os forms como o mesmo nome
        let submit = myform.serializeArray();

        // re-disabled the set of inputs that you previously enabled
        disabled.attr('disabled', 'disabled');

        submit.push({name: "form_name", value: "form_pesquisa"});

        // ajax nao funciona muito bem com o this
        let elemento = this;

        // monta a pesquisa em função separada
        let pesquisa = elemento.montapesquisa();

        // completa com o formulario de pesquisa
        if (typeof submit !== "undefined") {
            for (var r in submit) {
                pesquisa.push(submit[r]);
            }
        }

        $.ajax({
            url: "tabela.php",
            dataType: "json",
            type: 'POST',
            data: pesquisa
        }).done(function (valores) {
            // carrega o dumper
            if (valores.dev_log.length != 0) {
                // renderiza os prints
                DevInfo.init(false);
                DevInfo.renderDump(valores.dev_log);
            }

            if (valores.render != null) {
                if (!elemento.only_headers) {
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

                    if (elemento.relatorio == 0) {
                        elemento.acoes();
                    }
                }
                $(document).trigger('tabela_'+elemento.id, [valores.render]);
                spinner(false);
            }
        }).fail(function () {
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

        for (var i in header) {
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

                this.sethandle(linha);

                // joga no objeto da tbody
                $(obj).append(linha);
            }
        }
    }

    editaTabela(val){
        // limpa caso ja exista
        $("#modal_edita_tabela .modal-body").html("");

        const elemento = this;
        var header = Object.assign({}, val.header);

        const modal = new Modal();
        modal.title = "Editar tabela";
        modal.btnSuccess = "Enviar";
        modal.btnCancel = "Cancelar";
        modal.size = "modal-lg"; // "modal-lg, modal-sm";
        modal.btnSuccessAct = function() {Tabelas.atualizaHeader()};
        modal.name = "modal_edita_tabela";
        modal.no_footer = false;

        var rendered = modal.render();

        // itera primeiro os ativos e exclui do array original
        let elemento_ativo = `<div class="col-6 pl-1"><h3 class="my-1">Ativos</h3><ul class="list-group mt-0" id="edita_tabela_ativos">`;
        for(var i in val.exibe){
            var index = i.replace("e", "");
            elemento_ativo += `<li class="list-group-item d-block p-2" data-nome="${header[index]}" data-num="${index}">
                                    <button type="button" class="item_left btn py-1 px-3 my-0 btn-sm btn-info d-block float-left fa fa-angle-left"></button>
                                    ${header[index]}
                                    <button type="button" class="d-none item_right btn my-0 py-1 px-3 btn-sm btn-info float-right fa fa-angle-right"></button>
                                    <div class="item_control d-block float-right">
                                        <button type="button" class="item_up btn py-0 px-3 d-block btn-sm btn-info fa fa-angle-up"></button>
                                        <button type="button" class="item_down btn py-0 px-3 d-block btn-sm btn-info fa fa-angle-down"></button>
                                    </div>
                                </li>`;
            delete header[index];
        }
        elemento_ativo += `</ul></div>`;

        // itera o restante dos elementos
        let elemento_inativo = `<div class="col-6 pr-1"><h3 class="my-1">Inativos</h3><ul class="list-group mt-0" id="edita_tabela_inativos">`;
        for (var i in header){
            elemento_inativo += `<li class="list-group-item d-block p-2" data-nome="${header[i]}" data-num="${i}">
                                    <button type="button" class="item_left btn py-1 d-none px-3 my-0 btn-sm btn-info float-left fa fa-angle-left"></button>
                                    ${header[i]} 
                                    <button type="button" class="d-block item_right btn my-0 py-1 px-3 btn-sm btn-info float-right fa fa-angle-right"></button>
                                    <div class="item_control d-none float-right">
                                        <button type="button" class="item_up btn py-0 px-3 d-block btn-sm btn-info fa fa-angle-up"></button>
                                        <button type="button" class="item_down btn py-0 px-3 d-block btn-sm btn-info fa fa-angle-down"></button>
                                    </div>
                                </li>`;
        }
        elemento_inativo += `</ul></div>`;

        const html_static = `<input type="hidden" id="tabela_editar" value="${this.tabela.id}"><div class="row">${elemento_inativo} ${elemento_ativo}</div>`;
        $("#main-content").append(rendered);
        $("#modal_edita_tabela .modal-body").append(html_static);
        $("#modal_edita_tabela").modal("show");

        // funções
        $(".item_left").click(function(){
            const li = $(this).closest("li")[0];
            $(li).find(".item_control").toggleClass("d-block d-none");
            $(li).find(".item_left").toggleClass("d-block d-none");
            $(li).find(".item_right").toggleClass("d-block d-none");

            $("#edita_tabela_inativos").append(li);
        });
        $(".item_right").click(function(){
            const li = $(this).closest("li")[0];
            $(li).find(".item_control").toggleClass("d-block d-none");
            $(li).find(".item_left").toggleClass("d-block d-none");
            $(li).find(".item_right").toggleClass("d-block d-none");

            $("#edita_tabela_ativos").append(li);
        });
        $(".item_up").click(function(){
            const li = $(this).closest("li")[0];
            const i = parseInt($(li).index());

            if(i === 0){
                return;
            }

            $("#edita_tabela_ativos li:nth-child("+i+")").before(li);
        });
        $(".item_down").click(function(){
            const li = $(this).closest("li")[0];
            const i = parseInt($(li).index() + 2);

            if(($("#edita_tabela_ativos li").length +1) === i){
                return;
            }

            $("#edita_tabela_ativos li:nth-child("+i+")").after(li);
        });
    }

    static atualizaHeader(){
        spinner(true);

        let submit = [{name: "tabela", value: $("#tabela_editar").val()}];

        var i = 0;
        $("#edita_tabela_inativos li").each(function () {
            submit.push(
                    {name: "inativo["+i+"][nome]", value: $(this).attr("data-nome")},
                    {name: "inativo["+i+"][num]", value: $(this).attr("data-num")},
                    {name: "inativo["+i+"][pos]", value: i.toString()}
                );
            i++;
        });

        var i = 0;
        $("#edita_tabela_ativos li").each(function () {
            submit.push(
                {name: "ativo["+i+"][nome]", value: $(this).attr("data-nome")},
                {name: "ativo["+i+"][num]", value: $(this).attr("data-num")},
                {name: "ativo["+i+"][pos]", value: i.toString()}
            );
            i++;
        });

        $.ajax({
            url: "actions.php?pagina=atualizaheaders",
            dataType: "json",
            type: 'POST',
            data: submit
        }).done(function (valores) {
            Message.adiciona(valores.messages);

            if (valores.retorno.length > 0) {
                Tools.redirect(valores.retorno, false, false);
            }
            if (valores.dev_log.length != 0) {
                DevInfo.init();
                DevInfo.renderDump(valores.dev_log);
            }
            spinner(false);
        })
            .fail(function () {
                spinner(false);
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Não foi possível enviar os dados, tente atualizar a página";
                popup.montaMensagem();
            });
    }

    static montaLinha(dados, obj){
        for (const i in dados.colunas) {
            let td = document.createElement("td");
            td.className = "tabc "+dados.classes[i];
            td.innerHTML = dados.colunas[i];

            // joga no objeto da linha
            $(obj).append(td);
        }
    }
}
