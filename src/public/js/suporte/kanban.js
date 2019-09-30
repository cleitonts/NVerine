var somente_suporte = true;
var responsaveis = [{value: "", description: "", checked: "N"}];
var ultimo_update = false;

function pageUpdate(){
    kanban.getDados();
}

var kanban = class kanban{

    static checkUpdates(){
        $.ajax({
            url: "json.php?pagina=suporte_atualizacao",
            dataType: "json",
            type: 'POST'
        }).done(function (valores) {
            if($("#modal_edita_card").hasClass("show")){
                setTimeout(kanban.checkUpdates, 5000);
                return;
            }
            if(!ultimo_update){
                ultimo_update = valores;
                setTimeout(kanban.checkUpdates, 5000);
            }
            else{
                if(ultimo_update == valores){
                    setTimeout(kanban.checkUpdates, 5000);
                }
                else{
                    window.location.reload();
                }
            }
        }).fail(function () {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Não foi possível checar atualizações, tente atualizar a página.";
            popup.montaMensagem();
        });
    }

    static getDados(){
        // antes de mais nada printa o spinner
        spinner(true);

        $.ajax({
            url: "json.php?pagina=suporte_chamados",
            dataType: "json",
            type: 'POST'
        }).done(function (valores) {
            for (var item in valores) {
                kanban.lista(item, valores[item]);
            }

            var add = document.createElement("button");
            add.className = "btn btn-success btn-block m-0 mt-2";
            add.type = "button";
            add.onclick = function(){kanban.newLista()};
            $(add).append(document.createTextNode("adicionar lista"));

            if(!somente_suporte){
                $("#gera_kanban").after(add);
            }

            // $( "#gera_kanban" ).sortable({
            //     placeholder: "ui-state-highlight col-sm-4 col-md-3 card m-0",
            //     stop: function( ) {
            //         //TODO.setQuadro();
            //     }
            // }).disableSelection();
            $( ".cards_sortable" ).sortable({
                placeholder: "card ui-state-highlight my-2",
                connectWith: ".cards_sortable",
                scroll: true,
                scrollSensitivity: 100,
                scrollSpeed: 40,
                start: function (e, ui) {
                    var lista = $(ui.item[0]).closest("[lista-status]").attr("lista-status");
                    var pos = $(ui.item).parent().children().index(ui.item);
                    $(ui.item).attr("original-position", lista+"-"+pos);
                },
                stop: function(e, ui) {
                    var id = $(ui.item[0]).attr("card-id");
                    var status =  $(ui.item[0]).closest("[lista-status]").attr("lista-status");
                    var pos = $(ui.item).parent().children().index(ui.item);
                    var original =  $(ui.item).attr("original-position");
                    if(status+"-"+pos == original){
                        return;
                    }

                    var old_after = $("[card-after='"+id+"']");
                    var prev = $(ui.item[0]).prev();
                    var next = $(ui.item[0]).next();
                    var old_status = $(old_after[0]).closest("[lista-status]").attr("lista-status");
                    var prev2 = $(old_after).prev();
                    var changes = [];

                    if(prev.length == 0){
                        $(ui.item[0]).attr("card-after", 0);
                        changes.push({handle: id, after: 0, status: status});
                    }
                    // antes de atualizar a card original preciso atualizar alguma que ja esteja na fila
                    else{
                        if(next.length > 0){
                            $(next[0]).attr("card-after", id);
                            changes.push({handle: $(next[0]).attr("card-id"), after: 0 , status: status});
                        }
                        $(ui.item[0]).attr("card-after", $(prev[0]).attr("card-id"));
                        changes.push({handle: id, after: $(prev[0]).attr("card-id"), status: status});
                    }

                    if(prev2.length == 0){
                        if(old_after.length > 0){
                            $(old_after).attr("card-after", 0);
                            changes.push({handle: $(old_after).attr("card-id"), after: 0, status: old_status});
                        }
                    }
                    else{
                        $(old_after).attr("card-after", $(prev2[0]).attr("card-id"));
                        changes.push({handle: $(old_after).attr("card-id"), after: $(prev2[0]).attr("card-id"), status: old_status});
                    }

                    kanban.send(true, changes);
                }
            }).disableSelection();

            kanban.criaModal();
            $(".abrir_modal_edicao").click(function(){
                kanban.abrirModal($(this).closest('[card-id]').attr("card-id"));
            });
            $('[title]:not(".dropdown-toggle")').tooltip();
            kanban.checkUpdates();

            spinner(false);
        }).fail(function () {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Kanban não encontrado";
            popup.montaMensagem();
            spinner(false);
        });
    }

    static send(onlyStatus = false, changes = [], novo = false){
        spinner(true);

        let submit;
        if(!onlyStatus){
            // atualiza o editor de texto
            for (var instance in CKEDITOR.instances ) {
                CKEDITOR.instances[instance].updateElement();
            }
            // envia todos os forms como o mesmo nome
            submit = $("#form_chamado").serializeArray();
            submit.push({name: "form_name", value: "form_chamado"});
        }
        else{
            submit = {changes: changes, onlyStatus: true};
        }

        if(novo) {
            submit = {novo: true};
        }

        $.ajax({
            url: "actions.php?pagina=suportekanban",
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

    static criaModal(){
        spinner(true);

        $("#modal_galeria").on("hidden.bs.modal", function () {
            $("html > body").addClass("modal-open");
        });

        var modal = new Modal();
        modal.title = "Editar chamado";
        modal.btnSuccess = "Enviar";
        modal.btnCancel = "Cancelar";
        modal.size = "modal-lg"; // "modal-lg, modal-sm";
        modal.btnSuccessAct = function() {kanban.send()}; //function() {FieldTables.deleteLinha(modal)};
        //modal.btnCancelAct = ""; //function() {FieldTables.deleteLinha(modal)};
        modal.name = "modal_edita_card";
        modal.no_footer = false;

        var rendered = modal.render();
        $("#modal_galeria").before(rendered);

        $.ajax({
            url: "json.php?pagina=usuarios",
            dataType: "json",
            type: 'POST'
        }).done(function (valores) {
            for (var i = 0; i < valores.length; i++) {
                responsaveis.push({value: valores[i].handle, description: valores[i].nome, checked: "N"});
            }
            spinner(false);
        });
    }

    static abrirModal(card){
        spinner(true);
        var body = $("#modal_edita_card .modal-body");
        $("#modal_edita_cardLabel").text("Chamado "+card);// atualiza o titulo
        $(body).html("");

        $.ajax({
            url: "json.php?pagina=suporte_chamados",
            dataType: "json",
            type: 'POST',
            data: {pesq_chamado: card}
        }).done(function (valores) {
            var form = document.createElement("form");
            form.name = "form_chamado";
            form.id = "form_chamado";

            kanban.montaInputs(form, valores);

            $(body).append(form);

            if(valores.historico.length > 0){
                var detalhes = document.createElement("div");
                detalhes.className = "col-12 mt-3";
                detalhes.id = "chamados_detalhes";
                detalhes.style = "display:none;";

                var div1 = document.createElement("button");
                div1.className = "btn btn-info btn-block";
                div1.type = "button";
                div1.onclick = function(){
                    var obj = $("#chamados_detalhes");
                    if($(obj).css("display") == "block"){
                        $(obj).hide( "slide", {direction:'up'}, 500);
                    }
                    else{
                        $(obj).show( "slide", {direction:'up'}, 500);
                    }
                };
                $(div1).append(document.createTextNode("Detalhes"));
                $(body).append(div1);

                $(body).append(detalhes);

                for(var c = 0; c < valores.historico.length; c++){
                    kanban.montaDetalhes(detalhes, valores.historico[c]);
                }
            }

            $("textarea[name*='editor']").each(function () {
                var editor = CKEDITOR.instances[this.id];
                if (editor) {
                    editor.destroy(true);
                }
                CKEDITOR.replace(this.id);
            });

            //    Activate bootstrap-select
            if ($(".selectpicker").length != 0) {
                $(".selectpicker").not("[id*='templatex']").selectpicker();
            }

            $(".datepicker-date").datetimepicker({
                format: 'DD-MM-YYYY',
                icons: dateicons,
                locale: 'pt-br'
            }).blur(function(){
                $(this).closest(".form-group").addClass("is-filled");
            });

            // autocompletar
            $("#campo_cliente").autocomplete({
                source: "json.php?pagina=pessoas",
                select: function(event, ui) {
                    $("#campo_cod_cliente").val(ui.item.id);

                    if(ui.item.novo) alert("Função não disponível nesta página");
                }
            });

            $("#modal_edita_card").modal("show");
            spinner(false);

        }).fail(function () {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Kanban não encontrado";
            popup.montaMensagem();
            spinner(false);
        });
    }

    static montaInputs(body, valores){
        var status = { class: "",
            description: "status",
            function: "",
            icon: "",
            name: "status",
            size: "8",
            type: "HIDDEN",
            value: valores.cod_status
        };
        Field.input("hidden", body, status);

        var handle = { class: "",
            description: "handle",
            function: "",
            icon: "",
            name: "handle",
            size: "8",
            type: "HIDDEN",
            value: valores.handle
        };
        Field.input("hidden", body, handle);

        var assunto = { class: "",
            description: "Assunto",
            function: "",
            icon: "",
            name: "assunto",
            size: "8",
            type: "TEXT",
            value: valores.assunto
        };
        Field.input("text", body, assunto);

        var data = moment(valores.prazo);
        var prazo = { class: "datepicker-date",
            description: "Prazo para resolução",
            function: "",
            icon: "",
            name: "prazo",
            size: "4",
            type: "TEXT",
            value: data.format("DD-MM-YYYY")
        };
        Field.input("text", body, prazo);

        var cliente = { class: "",
            description: "Cliente",
            function: "",
            icon: "",
            name: "cliente",
            size: "9",
            type: "TEXT",
            value: valores.cliente
        };
        Field.input("text", body, cliente);

        var cod_cliente = { class: "",
            description: "Cod.",
            function: "",
            icon: "",
            name: "cod_cliente",
            size: "3",
            type: "TEXT",
            value: valores.cod_cliente
        };
        Field.input("label", body, cod_cliente);


        var prioridade = {
            class: "",
            description: "Prioridade",
            function: "",
            icon: "",
            name: "prioridade",
            options: [
                {value: "", description: "", checked: "N"},
                {value: 1, description: "Alta", checked: "N"},
                {value: 2, description: "Normal", checked: "N"},
                {value: 3, description: "Baixa", checked: "N"},
                {value: 4, description: "Quando puder", checked: "N"}
            ],
            checked: "N",
            size: "4",
            type: "SELECT",
            value: valores.cod_prioridade,
        };
        Field.select(body, prioridade);

        var tipo = {
            class: "",
            description: "Tipo de chamado",
            function: "",
            icon: "",
            name: "tipo",
            options: [
                {value: "", description: "", checked: "N"},
                {value: 1, description: "Suporte", checked: "N"},
                {value: 2, description: "Bugfix", checked: "N"},
                {value: 3, description: "Customização", checked: "N"},
                {value: 4, description: "Implantação", checked: "N"},
                {value: 5, description: "Arte/Web", checked: "N"},
                {value: 6, description: "Diversos", checked: "N"}

            ],
            checked: "N",
            size: "4",
            type: "SELECT",
            value: valores.cod_tipo,
        };
        Field.select(body, tipo);

        var responsavel = {
            class: "",
            description: "Responsável",
            function: "",
            icon: "",
            name: "responsavel",
            options: responsaveis,
            checked: "N",
            size: "4",
            type: "SELECT",
            value: valores.cod_responsavel,
        };

        Field.select(body, responsavel);

        var nome_contato = { class: "",
            description: "Nome contato",
            function: "",
            icon: "",
            name: "nome_contato",
            size: "6",
            type: "TEXT",
            value: valores.contato_nome
        };
        Field.input("text", body, nome_contato);

        var email = { class: "",
            description: "E-mail contato",
            function: "",
            icon: "",
            name: "email",
            size: "6",
            type: "TEXT",
            value: valores.contato_email
        };
        Field.input("text", body, email);

        var telefone = { class: "",
            description: "Telefone contato",
            function: "",
            icon: "",
            name: "telefone",
            size: "6",
            type: "TEXT",
            value: valores.contato_telefone
        };
        Field.input("text", body, telefone);

        var aberto = { class: "",
            description: "Aberto por",
            function: "",
            icon: "",
            name: "aberto",
            size: "6",
            type: "TEXT",
            value: valores.reporter
        };
        Field.input("label", body, aberto);

        var area = { class: "",
            description: "Comentários",
            function: "",
            icon: "",
            name: "editor_comentarios",
            size: 12,
            type: "AREA",
            value: ""
        };
        Field.textArea(body, area);

        var versao = { class: "",
            description: "Versão(svn)",
            function: "",
            icon: "",
            name: "versao",
            size: "3",
            type: "TEXT",
            value: ""
        };
        Field.input("text", body, versao);

        var duplicado = { class: "",
            description: "Duplicado",
            function: "",
            icon: "",
            name: "duplicado",
            size: "3",
            type: "TEXT",
            value: valores.duplicado
        };
        Field.input("text", body, duplicado);

        var cc = { class: "",
            description: "Email CC",
            function: "",
            icon: "",
            name: "cc",
            size: "6",
            type: "TEXT",
            value: valores.copia_carbono
        };
        Field.input("text", body, cc);
    }

    static montaDetalhes(obj, val){
        spinner(true);
        var wrapper = document.createElement("div");
        wrapper.className = "col";

        var div1 = document.createElement("div");
        div1.className = "row";

        var img = document.createElement("img");
        img.src = val.avatar;
        img.className = "img-thumbnail float-left";
        img.alt = val.usuario;

        var h5 = document.createElement("h5");
        h5.className = "float-left my-auto ml-2 d-flex";
        $(h5).append(document.createTextNode(val.usuario));

        var temp = val.data.split(" ");

        var data = moment(temp[0]+" "+val.hora);
        var span = document.createElement("span");
        span.className = "ml-3 small text-muted";
        $(span).append(document.createTextNode(data.format("DD MMM YYYY HH:mm")));

        var card = document.createElement("div");
        card.className = "card my-2 card-info";

        var card_b = document.createElement("div");
        card_b.className = "card-body";

        var cloned = $("#wrapper_galeria").clone().removeClass("d-none").attr("referencia", val.handle);

        $.ajax({
            url: "json.php?pagina=galeria",
            dataType: "json",
            type: 'POST',
            data: {pesq_referencia: val.handle}
        }).done(function (valores) {
            Body.renderComponent(cloned, valores.children[0]);

            $(card_b).append(val.comentarios);
            $(card).append(card_b);
            $(div1).append(img);
            $(h5).append(span);
            $(div1).append(h5);
            $(card).append(cloned);
            $(wrapper).append(div1);
            $(wrapper).append(card);
            $(obj).append(wrapper);

            spinner(false);
        }).fail(function () {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Não foi possível recuperar imagens.";
            popup.montaMensagem();
            spinner(false);
        });
    }

    // sempre joga no final
    static lista(name, val){
        var temp = name.split("-");
        var wrapper_lista = document.createElement("div");
        wrapper_lista.className = "col-sm-4 col-md-3 p-2 wrapper-lista";

        // o breakpoint não está no tamanho que eu quero
        if(window.innerWidth > 1500){
            wrapper_lista.className = "col-xl-2 p-2 wrapper-lista";
        }

        wrapper_lista.setAttribute("lista-status", parseInt(temp[0]));

        var card = document.createElement("div");
        card.className = "card m-0 p-2 dark-card";

        var block = document.createElement("div");
        block.className = "card-block cards_sortable ui-sortable";
        if(parseInt(temp[0]) == 1){
            block.className = "card-block ui-sortable";
        }

        var text = document.createElement("h5");
        text.className = "my-1";
        $(text).append(document.createTextNode(name));

        $(card).append(text);
        $(card).append(block);
        $(wrapper_lista).append(card);

        if (name.includes("01")){
            var add = document.createElement("button");
            add.className = "btn btn-success btn-block m-0 mt-2";
            add.type = "button";
            add.onclick = function(){kanban.newCard($(this).closest(".wrapper-lista"))};
            $(add).append(document.createTextNode("adicionar card"));
            $(card).append(add);
        }

        for (var i = 0; i < val.length; i++){
            var after = 0;
            if (i > 0){
                after = val[i-1].handle;
            }
            kanban.card(wrapper_lista, val[i], after);
        }

        $("#gera_kanban").append(wrapper_lista);
    }

    static newCard(lista){
        spinner(true);
        //var after = $("[lista-status='1']").find("[card-id]").last().attr("card-id");
        kanban.send(false, [], true);
        spinner(false);
    }

    static card(lista, val, after){
        var card = document.createElement("div");
        card.className = "card cartao ui-sortable-handle m-0 mt-2";
        card.setAttribute("card-id", val.handle);
        card.setAttribute("card-after", after);

        var content = document.createElement("div");
        content.className = "content p-2 prioridade-"+val.cod_prioridade;

        // cabeçalho
        var col4 = document.createElement("div");
        col4.className = "col-5 header m-0";

        var col5 = document.createElement("div");
        col5.className = "col-5 header m-0";

        var col6 = document.createElement("div");
        col6.className = "col-2 header pr-0";

        var btn3 = document.createElement("button");
        btn3.type = "button";
        btn3.className = "btn btn-info btn-block p-1 m-0";
        if(typeof val.cliente != "undefined") {
            btn3.title = val.cliente;
            $(btn3).append(document.createTextNode(val.cliente.substring(0, 10)));
        }
        else{
            $(btn3).append(document.createTextNode("--"));
        }
        var btn4 = document.createElement("button");
        btn4.type = "button";

        if(typeof val.cod_status != "undefined") {
            if (val.prazo.length > 0 && val.cod_status > 1 && val.cod_status < 6) {
                var today = new Date();
                var prazo = new Date(val.prazo);
                var diff = (prazo.getTime() - today.getTime());
                var dias = Math.ceil(diff / (1000 * 60 * 60 * 24));
                btn4.className = "btn btn-success btn-block p-1 m-0";
                btn4.title = "prazo de " + dias + " dias";

                if (dias == 0) {
                    btn4.className = "btn btn-warning btn-block p-1 m-0";
                    btn4.title = "O prazo esta acabando";
                }
                if (dias < 0) {
                    btn4.className = "btn btn-danger btn-block p-1 m-0";
                    btn4.title = "Atraso de " + Math.abs(dias) + " dias";
                }
                var data = moment(val.prazo);
                $(btn4).append(data.format("DD MMM YYYY"));
            }
            else{
                btn4.className = "btn btn-success btn-block p-1 m-0";
                $(btn4).append(document.createTextNode("--"));
            }
        }
        else{
            btn4.className = "btn btn-success btn-block p-1 m-0";
            $(btn4).append(document.createTextNode("--"));
        }

        var btn5 = document.createElement("button");
        btn5.type = "button";
        btn5.className = "btn btn-white btn-round p-1 m-0 float-right fa fa-pencil abrir_modal_edicao";
        //btn5.title = "Editar";

        $(col4).append(btn3);
        $(col5).append(btn4);
        $(col6).append(btn5);
        $(content).append(col4);
        $(content).append(col5);
        $(content).append(col6);

        // corpo
        var title = document.createElement("h6");
        title.className = "m-2";
        $(title).append(document.createTextNode(val.handle +" - "+ val.assunto));
        $(content).append(title);

        // rodape
        var col1 = document.createElement("div");
        col1.className = "col-3 rodape pt-2";

        var col2 = document.createElement("div");
        col2.className = "col-6 rodape";

        var col3 = document.createElement("div");
        col3.className = "col-3 rodape pr-0";

        var icon = document.createElement("i");
        icon.className = "fa fa-comment-o mr-1";

        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-info btn-block p-1 m-0";
        if(typeof val.contador == "undefined"){
            $(btn).append(document.createTextNode("--"));
        }
        else {
            $(btn).append(document.createTextNode(val.tipo));
        }


        var btn2 = document.createElement("button");
        btn2.type = "button";
        btn2.className = "btn btn-white btn-round p-1 m-0 float-right";

        if(typeof val.responsavel != "undefined") {
            btn2.title = val.responsavel;
            $(btn2).append(document.createTextNode(val.responsavel.substring(0, 2)));
        }
        else{
            $(btn2).append(document.createTextNode("--"));
        }

        $(col2).append(btn);
        $(col3).append(btn2);
        $(col1).append(icon);
        if(typeof val.contador == "undefined"){
            $(col1).append(document.createTextNode(0));
        }
        else{
            $(col1).append(document.createTextNode(val.contador));
        }
        $(content).append(col1);
        $(content).append(col2);
        $(content).append(col3);
        $(card).append(content);
        $(lista).find(".card-block.ui-sortable").append(card);
    }
};