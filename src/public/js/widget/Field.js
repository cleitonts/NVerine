/**
 * Renderiza os imput e selects com os dados vindos do json
 */
class Field{

    /**
     *
     * @param obj ponto de inserção
     * @param val valores a serem processados
     * @returns {HTMLDivElement}
     */
    static common(obj, val){
        let wrapper = document.createElement("div");
        wrapper.className = "mt-3 col-md-" + val.size;
        wrapper.id = "wrapper_"+val.name;

        let control = document.createElement("div");
        control.className = "control";    // não pode ter por padrão
        
        if(val.icon.length > 0){
            //control.className = "input-group form-control-lg";
            
            let icon = `<div class="input-group-prepend">
                            <span class="input-group-text px-1 pb-2">
                                <i class="${val.icon}"></i>
                            </span>
                        </div>`;
            //$(control).append(icon);
        }
        
        let group = document.createElement("div");
        group.className = "form-group bmd-form-group";

        if(val.required == 1){
            group.className += " required";
        }

        let title = document.createElement("label");
        title.className = "bmd-label-floating";
        title.setAttribute("for", "campo_"+val.name);
        $(title).append(document.createTextNode(val.description));

        if(val.type === "AREA"){
            let cont = document.createElement("span");
            cont.className = "contador_area ml-2";

            $(cont).append(document.createTextNode(val.value.length));
            $(title).append(cont);
        }

        // adiciona o node ao wrapper
        $(group).append(title);
        $(control).append(group);
        $(wrapper).append(control);
        return wrapper;
    }

    /**
     *
     * @param type do input
     * @param obj ponto de inserção
     * @param val valores a serem processados
     * @param prefix do campo, ex: pesq_
     */
    static input(type, obj, val, prefix = ""){
        let wrapper;

        // monta o input propriamente dito
        let input = document.createElement("input");
        if(type === "label"){
            input.readOnly = true;
        }
        input.type = val.type;
        input.className = "form-control "+val.class;
        input.name = val.name;

        if(val.required == 1){
            input.required = true;
        }

        if(prefix === "pesq_"){
            input.name = prefix + val.name;
        }
        else if(prefix.length > 0){
            input.name = prefix + "["+val.name+"]";
        }
        input.id = "campo_"+val.name;
        input.value = val.value;

        if(type !== "hidden") {
            wrapper = Field.common(obj, val);
        }
        else{
            wrapper = Field.common(obj, val);
            $(wrapper).hide();
        }
            
        $(wrapper).find(".form-group").append(input);               // junta tudo em um unico campo

        if (val.class.includes("calcular_perc")){
            $(wrapper).find(".control").addClass("input-group form-control-lg");
            
            let perc = `<button type="BUTTON" id="perc_${val.name}" class="btn btn-sm btn-info">%</button>`;
            $(wrapper).find(".form-group").addClass("btn-group").append(perc);
        }
        
        $(obj).append(wrapper);                     // joga na posição correta do form
    }

    static checkbox(obj, val, prefix){
        // gera a parte comum
        let wrapper = document.createElement("div");
        wrapper.className = "col-md-" + val.size + " "+val.class;
        wrapper.id = "wrapper_"+val.name;

        var label_group = document.createElement("label");
        label_group.className = "m-0";
        $(label_group).append(document.createTextNode(val.description));

        let wrapper_inputs = document.createElement("div");

        var i = 0;
        if(val.options.length > 0){
            for(i = 0; i < val.options.length; i++){
                var group = document.createElement("div");
                group.className = "form-check";

                var label = document.createElement("label");
                label.className = "form-check-label";
                $(label).append(document.createTextNode(val.options[i].description));

                var input = document.createElement("input");
                input.name = val.name + "[" + val.options[i].value + "]";
                input.id = "campo_"+val.name+"_"+i;

                if(prefix === "pesq_"){
                    input.name = prefix + val.name + "[" + val.options[i].value + "]";
                }
                if(val.options[i].checked == 'S'){
                    input.checked = true;
                }
                //input.id = "campo_" + input.name;
                input.type = "checkbox";
                input.className = "form-check-input";

                var span = document.createElement("span");
                span.className = "form-check-sign";

                var span_check = document.createElement("span");
                span_check.className = "check";

                $(span).append(span_check);
                $(label).append(input);
                $(label).append(span);
                $(group).append(label);
                $(wrapper_inputs).append(group);

            }
        }

        if( i > 3){
            var group = document.createElement("div");
            group.className = "form-check mt-0";

            var label = document.createElement("label");
            label.className = "form-check-label";
            label.appendChild(document.createTextNode("Todos"));

            var input = document.createElement("input");
            //input.name = val.name + "[" + i + "]";
            input.id = "campo_" + val.name + "[" + i+1 + "]";
            input.type = "checkbox";
            input.className = "form-check-input seleciona_todos";
            input.setAttribute("data-group", val.name);

            var span = document.createElement("span");
            span.className = "form-check-sign";

            var span_check = document.createElement("span");
            span_check.className = "check";

            $(span).append(span_check);
            $(label).append(input);
            $(label).append(span);
            $(group).append(label);
            $(wrapper_inputs).prepend(group);
        }

        $(wrapper_inputs).prepend(label_group);
        $(wrapper).append(wrapper_inputs);
        $(obj).append(wrapper);
    }

    static file(type, obj, val){
        let col = document.createElement("div");
        col.className = "col-md-" + val.size;

        let div1 = document.createElement("div");
        div1.className = "fileinput col-md-12 fileinput-new text-center";
        div1.setAttribute("data-provides", "fileinput");

        let div2 = document.createElement("div");
        div2.className = "fileinput-new thumbnail";

        let div3 = document.createElement("div");
        div3.className = "fileinput-preview fileinput-exists thumbnail";

        let div4 = document.createElement("div");

        let img = document.createElement("img");
        img.src = "";
        img.alt = "";

        let btn = document.createElement("span");
        btn.className = "btn btn-success btn-round btn-file";

        let span = document.createElement("span");
        span.className = "fileinput-new";
        $(span).append(document.createTextNode("Selecionar arquivo"));

        let span2 = document.createElement("span");
        span2.className = "fileinput-exists";
        $(span2).append(document.createTextNode("Trocar arquivo"));

        let input = document.createElement("input");
        input.type = "file";
        input.name = val.name;
        input.id = "campo_"+val.name;

        let remove = document.createElement("a");
        remove.className = "btn btn-danger btn-round fileinput-exists";
        remove.setAttribute("data-dismiss", "fileinput");
        remove.href = "#";

        $(remove).append(document.createTextNode("Remover"));

        $(div2).append(img);
        $(div1).append(div2);
        $(div1).append(div3);
        $(btn).append(span);
        $(btn).append(span2);
        $(btn).append(input);
        $(div4).append(btn);
        $(div4).append(remove);
        $(div1).append(div4);
        $(col).append(div1);
        $(obj).append(col);
    }

    static radioButton(obj, val, prefix = ""){
        // gera a parte comum
        let wrapper = document.createElement("div");
        wrapper.className = "col-md-" + val.size + " "+val.class;
        wrapper.id = "wrapper_"+val.name;

        var label_group = document.createElement("label");
        label_group.className = "m-0";
        $(label_group).append(document.createTextNode(val.description));

        let wrapper_inputs = document.createElement("div");

        var i = 0;
        if(val.options.length > 0){
            for(i = 0; i < val.options.length; i++){
                var group = document.createElement("div");
                group.className = "form-check mt-0";

                var label = document.createElement("label");
                label.className = "form-check-label";
                $(label).append(document.createTextNode(val.options[i].description));

                var input = document.createElement("input");
                input.name = val.name;
                if(prefix == "pesq_"){
                    input.name = prefix + val.name;
                }
                //input.id = "campo_" + input.name;
                input.type = "radio";
                input.value = i;
                input.className = "form-check-input";

                var span = document.createElement("span");
                span.className = "circle";

                var span_check = document.createElement("span");
                span_check.className = "check";

                $(span).append(span_check);
                $(label).append(input);
                $(label).append(span);
                $(group).append(label);
                $(wrapper_inputs).append(group);
            }
        }

        $(wrapper).append(label_group);
        $(wrapper).append(wrapper_inputs);
        $(obj).append(wrapper);
    }

    /**
     *
     * @param obj ponto de inserção
     * @param val valores a serem processados
     * @param prefix do campo, ex: pesq_
     */
    static textArea(obj, val, prefix = ""){

        // gera a parte comum
        let wrapper = Field.common(obj, val);

        // monta o select propriamente dito
        let wrapper_input = document.createElement("div"); // o input espera essa div
        let input = document.createElement("textarea");
        input.className = "form-control "+val.class;
        input.name = val.name;
        
        if(prefix == "pesq_"){
            input.name = prefix + val.name;
        }
        else if(prefix.length > 0){
            input.name = prefix + "["+val.name+"]";
        }
        if(val.required == 1){
            input.required = true;
        }
        input.id = "campo_"+val.name;
        input.value = val.value;
        input.onkeyup = function() {
            $(this).closest("[id^='wrapper_']").find(".contador_area").text(this.value.length);
        };
        // se for textarea normal faz diferente
        if(! (val.name.search(/editor/i) == -1)){
            $(wrapper_input).append(input);             // coloca o input dentro do seu wrapper
            $(wrapper).append(wrapper_input);           // junta tudo em um unico campo
        }
        else{
            input.rows = 5;
            $(wrapper).find(".form-group").append(input);
        }

        $(obj).append(wrapper);

        $(wrapper).find(".bmd-label-floating").toggleClass("label-select");           // junta tudo em um unico campo
        $(wrapper).find(".bmd-form-group").append(input);
        $(obj).append(wrapper);
    }

    /**
     *
     * @param obj ponto de inserção
     * @param val valores a serem processados
     * @param prefix do campo, ex: pesq_
     */
    static select(obj, val, prefix = ""){
        // gera a parte comum
        var wrapper = Field.common(obj, val);

        var input = document.createElement("select");
        input.className = "selectpicker "+val.class;
        input.name = val.name;
        input.setAttribute("data-live-search", true);

        if(val.required == 1){
            input.required = true;
        }
        if(prefix == "pesq_"){
            input.name = prefix + val.name;
        }
        else if(prefix.length > 0){
            input.name = prefix + "["+val.name+"]";
        }
        if(val.type.toLowerCase() == "disabled_select"){
            input.disabled = true;
        }
        input.id = "campo_"+val.name;

        if(val.function.length > 0) {
            $(input).attr("onchange", val.function); // chama função a partir do valor do objeto
        }

        // cria as options
        for(var i = 0; i < val.options.length; i++){
            let option = document.createElement("option");
            option.label = val.options[i].description;
            option.setAttribute("data-tokens", val.options[i].description)

            var text = val.options[i].description;
            if(text.length == 0){
                text = "\u00A0";
            }

            option.value = val.options[i].value;
            if(option.value.toLowerCase() == val.value.toLowerCase()){
                option.setAttribute("selected", "selected");
            }
            option.appendChild(document.createTextNode(text));
            $(input).append(option);
        }

        $(wrapper).find(".bmd-label-floating").toggleClass("label-select");           // junta tudo em um unico campo
        $(wrapper).find(".bmd-form-group").append(input);
        $(obj).append(wrapper);
    }

    /**
     *
     * @param type do input
     * @param obj ponto de inserção
     * @param val valores a serem processados
     * @param prefix do campo, ex: pesq_
     */
    static button(type, obj, val, prefix = ""){
        // cria wrapper
        let html = document.createElement("div");
        html.className = "col-md-"+val.size;

        // cria botão
        let button = document.createElement("button");
        button.type = type;
        button.className = `btn btn-block ${val.class}`;
        button.name = val.name;
        if(prefix === "pesq_"){
            button.name = prefix + val.name;
        }
        else if(prefix.length > 0){
            button.name = prefix + "["+val.name+"]";
        }
        button.id = "campo_"+val.name;

        if(val.function.length > 0) {
            $(button).attr("onclick", val.function); // chama função a partir do valor do objeto
        }

        // adiciona o node de texto ao button
        $(button).append(document.createTextNode(val.description));
        $(html).append(button);             // coloca o button dentro do seu wrapper
        $(obj).append(html);
    }

    /**
     *
     * @param type
     * @param obj
     * @param val
     * @param prefix
     */
    static tableInput(type, obj, val, prefix = ""){

        // monta o input propriamente dito
        let input = document.createElement("input");
        input.type = type;
        
        input.className = "form-control "+val.class;
        input.name = val.name;
        if(val.required == 1){
            input.required = true;
        }
        if(prefix == "pesq_"){
            input.name = prefix + val.name;
        }
        else if(prefix.length > 0){
            input.name = prefix + "["+val.name+"]";
        }
        input.id = "campo_"+val.name;
        input.value = val.value;

        if(type != "hidden"){
            let wrapper_input = document.createElement("td");   // o input espera essa div
            $(wrapper_input).append(input);                     // coloca o input dentro do seu wrapper
            $(obj).append(wrapper_input);                       // joga na posição correta da tabela
        }
        else{
            $(obj).append(input);                               // joga na posição correta da tabela
        }
    }

    /**
     *
     * @param obj
     * @param val
     */
    static tableLabel(obj, val){
        let wrapper = document.createElement("td"); // o input espera essa div
        wrapper.className = "tabc";
        wrapper.id = "label_"+val.name;
        wrapper.appendChild(document.createTextNode(val.value));

        $(obj).append(wrapper);                     // joga na posição correta da tabela
    }

    /**
     *
     * @param obj
     * @param val
     * @param prefix
     */
    static tableSelect(obj, val, prefix = ""){

        // monta o select propriamente dito
        let wrapper_input = document.createElement("td"); // o input espera essa div
        let input = document.createElement("select");
        input.className = "selectpicker "+val.class;
        //input.setAttribute('data-container', 'body');
        input.name = val.name;
        if(val.required == 1){
            input.required = true;
        }
        if(prefix == "pesq_"){
            input.name = prefix + val.name;
        }
        else if(prefix.length > 0){
            input.name = prefix + "["+val.name+"]";
        }
        input.id = "campo_"+val.name;

        // cria as options
        for(var i = 0; i < val.options.length; i++){
            let option = document.createElement("option");
            option.label = val.options[i].description;
            option.value = val.options[i].value;

            var text = val.options[i].description;
            if(text.length == 0){
                text = "\u00A0";
            }

            // define valor
            if(option.value == val.value){
                option.setAttribute("selected", "selected");
            }
            option.appendChild(document.createTextNode(text));
            $(input).append(option);
        }

        $(wrapper_input).append(input);             // coloca o input dentro do seu wrapper
        $(obj).append(wrapper_input);
    }
}
