/**
 * Renderiza uma tabela de imputs/selects dinamica
 */
class FieldTables {

    /**
     * trata se carrega uma tabela ou uma lista
     * const TABLE_STATIC = 1;
     * const TABLE_DYNAMIC = 2;
     * const LIST_STATIC = 3;
     * const LIST_DYNAMIC = 4;
     */
    static render(obj, val){
        var separator = document.createElement("div");
        separator.className = "card m-0 mb-2 p-2 p-md-0";
        separator.id = "dynamic_"+val.reference;
        if(val.name.length > 0){
            separator.id = "dynamic_"+val.name;
        }

        var body = document.createElement("div");

        if(val.view == 1 || val.view == 2) {
            body.className = "card-body p-1 px-md-4";
            FieldTables.table(body, val);
        }

        if(val.view == 3 || val.view == 4) {
            body.className = "card-body";
            FieldTables.list(body, val);
        }

        $(separator).append(body);
        $(obj).find("#wrapper_" + val.after).after(separator);
    }

    static list(obj, val){
        // monta linhas da lista
        if (val.rows.length > 0) {
            // itera linhas
            var index;      // preciso disso no final para contar as linhas
            for (var i = 0; i < val.rows.length; i++) {
                index = i;

                var row = document.createElement("div");
                row.className = "row";
                row.setAttribute("num", i);

                var separator = document.createElement("div");
                separator.className = "col-md-12 p-0";

                if (val.view == 4){
                    var btn = document.createElement("div");
                    btn.className = "btn btn-danger fa fa-times btn-sm btn-round btn-list btn-fab";
                    btn.onclick = function() {FieldTables.deleteLinha(this)};
                    btn.title = "Excluir item";

                    if (val.delete_block == 1) {
                        btn.className = "btn btn-disabled btn-sm btn-round btn-list btn-fab fa fa-save";
                        btn.title = "Item bloqueado";
                    }
                    else{
                        if(i == 0) {
                            btn.className = "btn btn-danger fa fa-times btn-list btn-sm btn-round btn-fab remove_item";
                        }
                    }
                    if(i == 0) {

                        row.style = "display: none;";
                        row.setAttribute("num", "templatex");
                    }

                    $(separator).append(btn);
                }

                if (val.rows[i].field.length > 0) {
                    for (var c = 0; c < val.rows[i].field.length; c++) {
                        var campo_atual = val.rows[i].field[c];

                        // edita o tipo para diferenciar no dictionary
                        var index_referencia = "templatex";

                        // adiciona edita informações de template
                        if (val.view == 4) {
                            if (i == 0) {
                                campo_atual.name = campo_atual.name + "_templatex";
                            }
                            else {
                                campo_atual.name = campo_atual.name + "_" + i; // passar numero da linha
                                index_referencia = i;
                            }
                        }

                        else {
                            campo_atual.name = campo_atual.name + "_" + i; // passar numero da linha
                            index_referencia = i;
                        }

                        Form.dictionary(separator, campo_atual, val.reference + "[" + index_referencia + "]");
                    }
                }

                var existe = document.createElement("input");
                existe.type = "hidden";
                existe.name = val.reference + "[" + index_referencia + "][existe_" + index_referencia + "]";
                existe.id = "campo_existe_" + index_referencia;
                existe.value = 1;

                $(separator).prepend(existe);

                $(row).append(separator);
                $(obj).append(row);

                // esse é mais seguro
                $(document).trigger("trigger_dynamic_"+val.reference, [row, i]);
            }
            // adiciona ultima linha caso seja dinamico
            if (val.view == 4) {

                var btn = document.createElement("button");
                btn.type = "button";
                btn.className = "btn btn-info fa fa-plus novo_item";
                btn.title = "Inserir novo item";
                btn.onclick = function() {FieldTables.adicionaLinha(val.reference)};

                // controlador de linhas na tabela
                let cont = document.createElement("input");
                cont.type = "hidden";
                cont.value = index + 1;
                cont.name = "dynamic_rows";
                cont.id = "campo_dynamic_rows";

                $(obj).append(btn);
                $(btn).before(cont);
            }
            
            // esse nome deve vir padão dos arquivos externos
            if(typeof triggerUpdate === "function"){
                triggerUpdate();
            }
        }
    }

    static table(obj, val) {
        var row = document.createElement("div");
        row.className = "row p-2";

        let wrapper = document.createElement("div");
        wrapper.className = "table-responsive";

        let table = document.createElement("table");
        table.className = "table table-striped mb-0";

        let thead = document.createElement("thead");
        let thead_row = document.createElement("tr");

        // adiciona head case seja dinamico
        if (val.view == 2) {
            var th = document.createElement("th");
            th.className = "thead";
            th.appendChild(document.createTextNode("#"));
            $(thead_row).append(th)
        }

        // itera todos os campo
        for (var i = 0; i < val.rows[0].field.length; i++) {
            // cria o node
            if (val.rows[0].field[i].type != "HIDDEN") {
                var th = document.createElement("th");
                th.appendChild(document.createTextNode(val.rows[0].field[i].description));
                $(thead_row).append(th)
            }
        }
        $(thead).append(thead_row);
        $(table).append(thead);

        let tbody = document.createElement("tbody");

        // itera linhas
        var index;      // preciso disso no final para contar as linhas
        var c;
        for (let i = 0; i < val.rows.length; i++) {
            index = i;

            var tr = document.createElement("tr");
            tr.setAttribute("num", i);

            if (val.view == 2 && i == 0) {
                tr.style = "display: none;";
                tr.setAttribute("num", "templatex");
            }

            // itera campos
            for (c = 0; c < val.rows[i].field.length; c++) {
                var campo_atual = val.rows[i].field[c];

                // edita o tipo para diferenciar no dictionary
                campo_atual.type = "table-" + campo_atual.type;
                var index_referencia = "templatex";

                // adiciona edita informações de template
                if (val.view == 2) {
                    if(i == 0) {
                        //campo_atual.value = "";
                        campo_atual.name = campo_atual.name + "_templatex";
                    }
                    else{
                        campo_atual.name = campo_atual.name + "_" + i; // passar numero da linha
                        index_referencia = i;
                    }
                }
                else{
                    campo_atual.name = campo_atual.name + "_" + i; // passar numero da linha
                    index_referencia = i;
                }

                Form.dictionary(tr, campo_atual, val.reference + "[" + index_referencia + "]");
            }

            // adiciona botão de controle
            if (val.view == 2) {
                // adiciona botão de nova linha
                var td = document.createElement("td");
                td.className = "tabc";

                var btn_div = document.createElement("div");

                if(val.delete_block == 1){
                    btn_div.className = "btn btn-disabled fa fa-save btn-sm btn-round btn-fab";
                    btn_div.title = "Item bloqueado";
                }
                else{
                    btn_div.className = "btn btn-danger btn-sm btn-round btn-fab fa fa-times";
                    btn_div.onclick = function() {FieldTables.deleteLinha(this)};
                    btn_div.title = "Excluir item";
                }

                // conserta par ao template
                if(i == 0){
                    btn_div.className = "btn btn-danger btn-sm btn-round btn-fab fa fa-times remove_item";
                    btn_div.onclick = function() {FieldTables.deleteLinha(this)};
                    btn_div.title = "Excluir item";
                }

                var existe = document.createElement("input");
                existe.type = "hidden";
                existe.name = val.reference + "[" + index_referencia + "][existe_"+index_referencia+"]";
                existe.id = "campo_existe_"+index_referencia;
                existe.value = 1;

                $(td).append(btn_div);
                $(tr).prepend(td);
                $(tr).prepend(existe);
            }

            FieldTables.normalizeRow(tr);
            $(tbody).append(tr);

            // esse é mais seguro
            $(document).trigger("trigger_dynamic_"+val.reference, [tr, i]);
        }

        // adiciona ultima linha caso seja dinamico
        if (val.view == 2) {
            // adiciona botão de nova linha
            var tr = document.createElement("tr");
            tr.className = "tabdiv";
            tr.id = "ultima_linha";

            var td = document.createElement("td");
            td.className = "tabc";

            var div = document.createElement("div");
            div.className = "btn btn-success btn-sm btn-round btn-fab novo_item fa fa-plus";
            div.title = "Inserir novo item";

            if(val.name.length > 0){
                div.onclick = function() {FieldTables.adicionaLinha(val.name)};
            }
            else{
                div.onclick = function() {FieldTables.adicionaLinha(val.reference)};
            }

            $(td).append(div);
            $(tr).append(td);

            var td = document.createElement("td");
            td.className = "tabc";
            td.colSpan = c;

            $(tr).append(td);
            $(tbody).append(tr);
        }
        // controlador de linhas na tabela
        let cont = document.createElement("input");
        cont.type = "hidden";
        cont.value = index + 1;
        cont.name = "dynamic_rows";
        cont.id = "campo_dynamic_rows";
        $(table).append(cont);

        $(table).append(tbody);
        $(wrapper).append(table);
        $(row).append(wrapper);
        $(obj).append(row);
    }

    /**
     * adiciona nova linha na tabela
     * @param name tabela
     */
    static adicionaLinha(name){
        // salva e atualiza o numero
        var num = parseInt($("#dynamic_"+name+" #campo_dynamic_rows").val()) + 1;
        $("#dynamic_"+name+" #campo_dynamic_rows").val(num);

        // copia o node
        var linha = $("#dynamic_"+name).find("[num='templatex']")[0];
        var clone = linha.cloneNode(true);
        clone.removeAttribute("style");
        clone.setAttribute("num", num - 1);

        // faz o replace nos ids
        $(clone).find("[id*='templatex']").each(function(){
            var id = $(this).attr("id").replace("templatex", num -1);

            var name;
            if(typeof $(this).attr("name") != "undefined") {
                name = $(this).attr("name").replace(/templatex/gi, num - 1);
            }
            this.id = id;
            this.name = name;
        });

        FieldTables.normalizeRow(clone);

        if($("#dynamic_"+name+" #ultima_linha").length > 0){
            $("#dynamic_"+name+" #ultima_linha").before(clone);
        }
        else{
            $("#dynamic_"+name+" .novo_item").before(clone);
        }

        // esse nome deve vir padão dos arquivos externos
        if(typeof triggerUpdate === "function"){
            triggerUpdate(clone);
        }

        // esse é mais seguro
        $(document).trigger("trigger_dynamic_"+name, [clone, (num - 1)]);

        return clone;
    }

    static deleteLinha(obj){
        var excluir = $(obj).closest("[num]").find("[id^='campo_existe_']").val();

        if(excluir == 1){
            $(obj).closest("[num]").find("[id^='campo_existe_']").val(0);
            obj.setAttribute("data-original-title", "Inserir novo item");
            $(obj).toggleClass("btn-success btn-danger").toggleClass("fa-times fa-plus");
        }
        else{
            $(obj).closest("[num]").find("[id^='campo_existe_']").val(1);
            obj.setAttribute("data-original-title", "Excluir item");
            $(obj).toggleClass("btn-success btn-danger").toggleClass("fa-times fa-plus");
        }

        // esse é mais seguro
        //$(document).trigger("trigger_dynamic_"+val.reference, [row, - 1]);
        
        // esse nome deve vir padão dos arquivos externos
        if(typeof triggerUpdate === "function"){
            triggerUpdate();
        }
    }

    /**
     * remove uma linha da tabela
     * @param num linha
     * @param name tabela
     */
    static removeLinha(linha){
        $(linha).find('[title]').tooltip('hide');
        linha.outerHTML = "";
        
        if(typeof triggerUpdate === "function"){
            triggerUpdate();
        }
    }

    /**
     * manter funções recorrentes nas linhas aqui
     * @param linha
     */
    static normalizeRow(linha){
        $(linha).find(".datepicker-datetime").not("[id*='templatex']").datetimepicker({
            format: 'DD-MM-YYYY LT',
            icons: dateicons,
            locale: 'pt-br'
        });

        $(linha).find(".datepicker-time").not("[id*='templatex']").datetimepicker({
            format: 'LT',
            icons: dateicons,
            locale: 'pt-br'
        });

        $(linha).find(".datepicker-date").not("[id*='templatex']").datetimepicker({
            format: 'DD-MM-YYYY',
            icons: dateicons,
            locale: 'pt-br'
        });

        $(linha).find(".selectpicker").not("[id*='templatex']").each( function () {
            $(this).selectpicker();

        });

        $(linha).find(".remove_item").click(function() {
            FieldTables.removeLinha(linha);
        });

        // atualiza toolstips
        $(linha).find('[title]:not(".dropdown-toggle")').tooltip();

        $(linha).find("[id*='valor']").each(function(){
            $(this).mask("#.##0,00", {reverse: true});
        });
        $(linha).find("[id*='perc']").each(function(){
            const list = this.classList;
            var str = "000,00%";

            for (var i = 0; i < list.length; i++ ){
                if(list[i].includes("precision") === true){
                    let cont = list[i].split("-");
                    str = "";
                    while (cont[1] > 0){
                        str = str + "0";
                        cont[1]--;
                    }
                    str = "00,"+str+"%";
                }
            }
            $(this).mask(str);
        });
    }
}
