/**
 * monta um formulario(wrapper)
 */
class Form{
    /**
     *
     * @param obj ponto de inserção
     * @param val valores a serem processados
     */
    static create(obj, val){
        let html = document.createElement("form");
        html.action = val.action;
        html.name = val.name;
        html.id = val.name;
        html.method = val.method;

        for(var i = 0; i < val.field.length; i++){
            Form.dictionary(html, val.field[i], val.prefix);
        }

        for(var i = 0; i < val.table.length; i++){
            FieldTables.render(html, val.table[i]);
        }

        // joga tudo dentro do objeto
        $(obj).append(html);
    }

    /**
     *
     * @param obj
     * @param val
     * @param prefix
     * @returns {string}
     */
    static dictionary(obj, val, prefix){
        switch(val.type.toLowerCase()) {
            case "password":
                Field.input("password", obj, val, prefix);
                break;
            case "text":
                Field.input("text", obj, val, prefix);
                break;
            case "label":
                val.class += " form-disabled";
                val.type = "text";
                Field.input("label", obj, val);
                break;
            case "select":
                Field.select(obj, val, prefix);
                break;
            case "disabled_select":
                Field.select(obj, val, prefix);
                break;
            case "hidden":
                Field.input("hidden", obj, val, prefix);
                break;
            case "area":
                Field.textArea(obj, val, prefix);
                break;
            case "checkbox":
                Field.checkbox(obj, val, prefix);
                break;
            case "radio":
                Field.radioButton(obj, val, prefix);
                break;
            case "button":
                Field.button("button", obj, val, prefix);
                break;
            case "submit":
                val.class += " btn-success";
                Field.button("submit", obj, val, prefix);
                break;
            case "table-hidden":
                Field.tableInput("hidden", obj, val, prefix);
                break;
            case "table-text":
                Field.tableInput("text", obj, val, prefix);
                break;
            case "table-label":
                Field.tableLabel(obj, val);
                break;
            case "table-select":
                Field.tableSelect(obj, val, prefix);
                break;
            default:
                return "burrice";
        }
    }

    /**
     *
     * @param form
     */
    static send(form) {
        // antes de mais nada printa o spinner
        spinner(true);

        // atualiza o editor de texto
        for (var instance in CKEDITOR.instances ) {
            CKEDITOR.instances[instance].updateElement();
        }

        // retira os not a number
        $("input").filter(function () {
            return this.value == 'NaN'
        }).val("");

        // esse nome deve vir padão dos arquivos externos
        if (typeof formUpdate === "function") {
            // alguns forms precisam ser atualizados antes de enviador
            formUpdate();
        }

        const myform = $("[id=" + form.name + "]");

        // Find disabled inputs, and remove the "disabled" attribute
        let disabled = myform.find(':disabled').removeAttr('disabled');

        // envia todos os forms como o mesmo nome
        let submit = myform.serializeArray();

        // re-disabled the set of inputs that you previously enabled
        disabled.attr('disabled','disabled');

        var form_name = $(form).attr("name");
        submit.push({name: "form_name", value: form_name});
        $.ajax({
            url: $(form).attr("action"),
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

    static excluir(form){
        $("#modal_confirmacao").remove();

        var modal = new Modal();
        modal.title = "Excluir";
        modal.btnSuccess = "Excluir";
        modal.btnCancel = "Cancelar";
        modal.size = "modal-lg"; // "modal-lg, modal-sm";
        modal.btnSuccessAct = function() {
            $("#modal_confirmacao").modal("hide");
            $("#campo_excluir").val(1);
            Form.send($(form)[0]);
        }; //function() {FieldTables.deleteLinha(modal)};
        modal.btnCancelAct = function() {$("#campo_excluir").val(1)};
        modal.name = "modal_confirmacao";
        modal.no_footer = false;
        modal.text = "Deseja excluir este item?";

        var rendered = modal.render();
        $("#main-content").append(rendered);

        $("#modal_confirmacao").modal("show");
    }
}
