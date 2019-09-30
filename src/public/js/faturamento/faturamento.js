var notas_selecionadas = "";

function pageUpdate(){
    createModal();
}

function createModal(){
    var modal = new Modal();
    modal.title = "Processar";
    modal.btnSuccess = "Processar";
    modal.btnCancel = "Cancelar";
    modal.size = "modal-lg"; // "modal-lg, modal-sm";
    //modal.btnSuccessAct = function() {Gallery.send(handle, target)}; //function() {FieldTables.deleteLinha(modal)};
    //modal.btnCancelAct = ""; //function() {FieldTables.deleteLinha(modal)};
    modal.name = "modal_processamento";
    modal.no_footer = false;

    var rendered = modal.render();
    $("#main-content").append(rendered);
}

function validaSelecao(multiplo) {
    spinner(true);
    var num = 0;
    var interrompe = false; // conserta alertas repetidos com mais de duas notas
    notas_selecionadas = ""; // limpa string global

    $("[name^='sel_']:checked").each(function(){
        if(interrompe) {
            // ...
        }
        else if(num > 0 && !multiplo) {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Esta ação requer a seleção de uma única nota.";
            popup.montaMensagem();
            interrompe = true;
        }
        else {
            // guarda número da nota atual
            num = $(this).attr("num");

            // insere na string de notas múltiplas
            if(notas_selecionadas != "") notas_selecionadas += ",";
            notas_selecionadas += num;
        }
    });

    if(interrompe) {
        spinner(false);
        return false;
    }

    if(num == 0) {
        spinner(false);
        var popup = new Alert();
        popup.typo = "danger";
        popup.texto = "Por gentileza, selecione uma nota na lista.";
        popup.montaMensagem();
        return false;
    }

    spinner(false);
    return num;
}

function processa() {
    /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * ENQUANTO FOR POSSÍVEL PROCESSAMENTO DE NOTA FISCAL POR AQUI,
     * NÃO PERMITIR QUE MAIS DE UMA NOTA SEJAM ENVIADAS PARA VALIDAÇÃO
     * -> para garantir a consistência do fluxo se a primeira for aprovada
     * e a próxima for negada, causando um rollback e a perda do protocolo
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     */
    var num = validaSelecao(false); // bloqueando múltiplo temporariamente

    var body = $("#modal_processamento .modal-body");
    body.innerHTML = "";

    var footer = $("#modal_processamento .modal-footer");
    $(footer).find(".table-responsive").innerHTML = "";
    $(footer).css("flex-wrap", "wrap");

    // fez seleção válida?
    if(num) {
        spinner(true);

        // carrega dados das duplicatas para prévia
        $.ajax({
            url: "json.php?pagina=duplicatas",
            dataType: "json",
            data: {notas: notas_selecionadas}
        })
            .done(function(valores){
                let emiteNF = 'N';
                if(__MODELO_NF__ == 55 || __MODELO_NF__ == 65){
                    emiteNF = 'S';
                }

                let row = document.createElement("div");
                row.className = "row";

                let col = document.createElement("div");
                col.className = "col-4";

                let opcoes = {
                    "type": "CHECKBOX",
                    "value": "",
                    "name": "acao",
                    "description": "Com as notas selecionadas:\n",
                    "size": "12",
                    "icon": "",
                    "property": "",
                    "function": "",
                    "options": [
                        {
                            "value": "1",
                            "description": "Faturar (exportar financeiro)",
                            "checked": "S"
                        },
                        {
                            "value": "2",
                            "description": "Processar estoque",
                            "checked": "S"
                        },
                        {
                            "value": "3",
                            "description": "Emitir nota fiscal",
                            "checked": emiteNF
                        },
                        {
                            "value": "4",
                            "description": " Enviar nota fiscal por e-mail",
                            "checked": "N"
                        },
                    ],
                    "class": ""
                };
                Field.checkbox(col, opcoes);

                let col2 = document.createElement("div");
                col2.className = "col-8";

                let email = {
                    "type": "TEXT",
                    "value": "",
                    "name": "email",
                    "description": "E-mail para:",
                    "size": "12",
                    "icon": "",
                    "property": "",
                    "function": "",
                    "class": ""
                };
                Field.input("text", col2, email);

                let cc = {
                    "type": "TEXT",
                    "value": "",
                    "name": "cc",
                    "description": "CC para:",
                    "size": "12",
                    "icon": "",
                    "property": "",
                    "function": "",
                    "class": ""
                };
                Field.input("text", col2, cc);

                $(row).append(col);
                $(row).append(col2);
                $(body).append(row);

                let size = 12;
                if(valores[0].finalidade != 4 && valores[0].tipo == "E"){
                    let doc_fornecedor = {
                        "type": "TEXT",
                        "value": valores[0].doc_fornecedor,
                        "name": "doc_fornecedor",
                        "description": "Doc. fornecedor",
                        "size": "4",
                        "icon": "",
                        "property": "",
                        "function": "",
                        "class": ""
                    };
                    Field.input("text", body, doc_fornecedor);

                    size = 8;
                }

                let fisco = {
                    "type": "TEXT",
                    "value": "",
                    "name": "cc",
                    "description": "Informações adicionais/Histórico",
                    "size": size,
                    "icon": "",
                    "property": "",
                    "function": "",
                    "class": ""
                };
                Field.input("text", body, fisco);

                $("#modal_processamento").modal("show");
                spinner(false);

                let div = document.createElement("div");
                div.className = "table-responsive";

                let table = document.createElement("table");
                table.className = "table table-striped mb-1 mt-2";

                let thead = document.createElement("thead");

                var th = document.createElement("th");
                $(th).append(document.createTextNode("Nota"));
                $(thead).append(th);

                th = document.createElement("th");
                $(th).append(document.createTextNode("Parcela"));
                $(thead).append(th);

                th = document.createElement("th");
                $(th).append(document.createTextNode("Vencimento"));
                $(thead).append(th);

                th = document.createElement("th");
                $(th).append(document.createTextNode("Valor"));
                $(thead).append(th);

                th = document.createElement("th");
                $(th).append(document.createTextNode("Forma pagto."));
                $(thead).append(th);

                var tbody = document.createElement("tbody");

                for(var item in valores) {
                    var tr = document.createElement("tr");

                    var td = document.createElement("td");
                    $(td).append(document.createTextNode(valores[item].nota));
                    $(tr).append(td);

                    var td = document.createElement("td");
                    $(td).append(document.createTextNode(valores[item].numero));
                    $(tr).append(td);

                    var td = document.createElement("td");
                    $(td).append(document.createTextNode(valores[item].dataVencimento));
                    $(tr).append(td);

                    var td = document.createElement("td");
                    $(td).append(document.createTextNode(valores[item].valor));
                    $(tr).append(td);

                    var td = document.createElement("td");
                    $(td).append(document.createTextNode(valores[item].formaPagamento));
                    $(tr).append(td);

                    $(tbody).append(tr);
                    //valor_total += parseFloat(valores[item].valor);
                }

                $(table).append(thead);
                $(table).append(tbody);
                $(div).append(table);
                $(footer).append(div);
            })
            .fail(function(){
                alert("Conexão com o banco de dados perdida");
            });
    }
}