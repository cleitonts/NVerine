class Gallery {
    // monta os campos de upload dentro da div
    static create(obj) {
        //spinner(true);

        var handle = $(obj).attr("referencia");
        var target = $(obj).attr("target");

        this.criaModal(handle, target);
        this.lista(obj);
    }

    static send(handle, target) {
        spinner(true);
        let formData = new FormData();

        $("#modal_galeria").modal("hide");
        let input = $("#modal_galeria #campo_imagem")[0];
        let legenda = $("#modal_galeria #campo_legenda").val();
        let nome = $("#modal_galeria #campo_nome").val();

        // envia somente 1 por vez
        if(typeof input != "undefined"){
            formData.append("file", input.files[0]);
        }
        else{
            formData.append("old_nome", $("#imagem-galeria").attr("data-name"));
            formData.append("deletar", $("#campo_delete_0")[0].checked);
        }
        formData.append("referencia", handle);
        formData.append("target", target);
        formData.append("legenda", legenda);
        formData.append("nome", nome);

        $.ajax({
            url: __PASTA__ + 'actions.php?pagina=gallery',
            type: 'POST',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            success: function (valores) {
                valores = JSON.parse(valores);
                Message.adiciona(valores.messages);

                if (valores.retorno.length > 0) {
                    let url = window.location.href.split("?")
                    Tools.redirect("?"+url[1], false, false);
                }

                if (valores.dev_log.length != 0) {
                    DevInfo.init();
                    DevInfo.renderDump(valores.dev_log);
                }
                spinner(false);
            },
            fail: function () {
                spinner(false);
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Não foi possível enviar os dados, tente atualizar a página";
                popup.montaMensagem();
            }
        });
    }

    static lista(obj){
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-info fa fa-plus novo_item m-0 float-left";
        btn.title = "Inserir novo item";
        btn.setAttribute("onclick", "Gallery.abreModal(this)");

        $(obj).prepend(btn);

    }

    static criaModal(handle, target){
        var modal = new Modal();
        modal.title = "Galeria";
        modal.btnSuccess = "Enviar";
        modal.btnCancel = "Cancelar";
        modal.size = "modal-lg"; // "modal-lg, modal-sm";
        //modal.btnSuccessAct = function() {Gallery.send(handle, target)}; //function() {FieldTables.deleteLinha(modal)};
        //modal.btnCancelAct = ""; //function() {FieldTables.deleteLinha(modal)};
        modal.name = "modal_galeria";
        modal.no_footer = false;

        var rendered = modal.render();
        $("#main-content").append(rendered);
    }

    static abreModal(obj, file_input = true){
        var content = $("#modal_galeria .modal-body");
        $(content).html("");
        var handle = $(obj).closest("[referencia]").attr("referencia");
        var target = $(obj).closest("[target]").attr("target");
        $("#modal_galeria .modal-footer .btn-success").attr("onclick", "Gallery.send("+handle+","+target+")");

        var legenda = {   class: "",
            description: "Legenda",
            function: "",
            icon: "",
            name: "legenda",
            size: "12",
            type: "TEXT",
            value: ""
        };

        var nome = {   class: "",
            description: "Nome",
            function: "",
            icon: "",
            name: "nome",
            size: "12",
            type: "TEXT",
            value: ""
        };

        var file = {   class: "",
            description: "Imagem",
            function: "",
            icon: "",
            name: "imagem",
            size: "12",
            type: "FILE",
            value: ""
        };

        // carrega o botão para upload da imagem
        if(file_input) {
            Field.file("file", content, file);
            Field.input("text", content, nome);
            Field.input("text", content, legenda);

            $("#modal_galeria").modal('show');

            // amarra ação do nome
            $(content).find("#campo_imagem").change(function (e) {
                $(content).find("#campo_nome").val(e.target.files[0].name)
                    .closest(".form-group.bmd-form-group").addClass("is-filled");
            });
        }
        else{
            // obtem o nome e a legenda
            let title = $(obj).find("img").attr("data-name");
            let alt = $(obj).find("img").attr("alt");

            nome.value = title;
            nome.size = 8;
            legenda.value = alt;

            var checkbox = {   class: "",
                description: "Deletar imagem",
                function: "",
                icon: "",
                name: "delete",
                size: "4",
                options: [{value: "1", description: "Deletar", checked: "N"}],
                type: "CHECKBOX",
                value: ""
            };

            Field.input("text", content, nome);
            Field.checkbox(content, checkbox);
            Field.input("text", content, legenda);

            let clone = $(obj).find("img").clone();

            if(typeof $(clone).attr("file") !== "undefined"){
                var link = document.createElement("a");
                link.href = $(clone).attr("file");
                link.className = "btn btn-success";
                link.download = $(clone).attr("data-name");
                link.setAttribute("data-name", $(clone).attr("data-name"));
                link.id = "imagem-galeria";
                $(link).append(document.createTextNode("Download"));
                $(content).prepend(link);
            }
            else{
                $(clone).css("width", "100%");
                $(clone)[0].setAttribute("id", "imagem-galeria") ;
                $(content).prepend(clone);
            }



            $("#modal_galeria").modal('show');
        }
    }
}