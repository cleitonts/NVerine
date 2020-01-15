/**
 * monta a body do widget
 */
// precisa ser publico
var tabelas;
class Body{
    /**
     *
     * @param obj ponto de inserção
     * @param val valores a serem processados
     */
    static create(obj, val) {
        let i = 0;
        let key;
        let tab_content = document.createElement("div");
        tab_content.className = "tab-content tab-space";

        // itera cada item da arvore
        for (key in val.body.tabs) {
            i++;
            let atual = val.body.tabs[key];

            let pane = document.createElement("div");

            // cria node
            let html = document.createElement("div");
            html.className = "card-body";

            Body.content(html, atual, val);

            // essa parte não é paginada
            if(key.length > 1){
                pane.className = "tab-pane";
                pane.id = "sd" + i;

                // cria abas
                val.header.tabs.push({icon: atual.icon, description: key, target: html.id, function: atual.function});
            }
            else{
                i--;
            }

            if(i == 1){
                pane.className = pane.className+" active";
            }


            // joga cada item no final do objeto
            $(pane).append(html);
            $(tab_content).append(pane);
        }

        $(obj).append(tab_content);
    }

    static renderComponent(obj, atual){
        let component = document.createElement(atual.tag);

        // roda objetos e cria os atributos
        for (var lin in atual.attr) {
            component.setAttribute(lin, atual.attr[lin]);
        }

        // monta formulario
        if (atual.field.length > 0) {
            for (var i = 0; i < atual.field.length; i++) {
                Form.dictionary(component, atual.field[i], atual.prefix);
            }
        }

        // monta novo component
        if (atual.children.length > 0) {
            for (var i = 0; i < atual.children.length; i++) {
                Body.renderComponent(component, atual.children[i]);
            }
        }

        // printa texto
        if (atual.text.length > 0) {
            var texto = document.createElement("p");
            $(texto).append(document.createTextNode(atual.text));
            $(component).append(texto);
        }

        $(obj).append(component);
    }

    static content(obj, atual){
        // instancia um novo component ex: um calendario
        if(atual.children.length > 0){
            for (var c = 0; c < atual.children.length; c++) {
                Body.renderComponent(obj, atual.children[c]);
            }
        }

        else {
            if (typeof atual.form.name != "undefined") {
                // cria wrapper e um form
                var wrapper = document.createElement("div");
                wrapper.className = "row";

                var form = document.createElement("form");
                form.action = atual.form.action;
                form.name = atual.form.name;
                form.id = atual.form.name;
                form.method = atual.form.method;
                form.className = "col-md-12 col-md-12 pl-md-3 pr-md-3 p-0";

                if (atual.form.children.length > 0) {
                    for (var i = 0; i < atual.form.children.length; i++) {
                        Body.renderComponent(form, atual.form.children[i]);
                    }
                }

                if (atual.form.field.length > 0) {
                    for (var i = 0; i < atual.form.field.length; i++) {
                        Form.dictionary(form, atual.form.field[i], atual.form.prefix);
                    }
                }
                for (var i = 0; i < atual.form.table.length; i++) {
                    FieldTables.render(form, atual.form.table[i]);
                }

                $(wrapper).append(form);
                $(obj).append(wrapper);
            }

            // monta tabela
            if (atual.table.name.length > 0) {
                // cria wrappers e uma table
                let wrapper = document.createElement("div");
                wrapper.className = "mt-4 row";

                // instancia as tabelas
                tabelas = new Tabelas(wrapper, atual.table);
                tabelas.getDados(obj);

                $(obj).append(wrapper);
            }

            // estudar se pode ser retirado para usar somente o component
            if(atual.charts.length > 0){
                let container = document.createElement("div");
                container.id = "container-chats";

                for (var c = 0; c < atual.charts.length; c++){
                    var cc = new CustomChart();
                    cc.btnSend(atual.charts[c], obj);
                    cc.create(atual.charts[c], container);
                }
                $(obj).append(container);
            }
        }
        // joga cada item no final do objeto
        //$(obj).append(html);
    }
}
