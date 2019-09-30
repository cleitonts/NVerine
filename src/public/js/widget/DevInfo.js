/**
 * Mostra as informações pertinentes aos desenvolvedores
 */
class DevInfo {
    static init(limpar = true){
        if(limpar) {
            $("#log-wrapper").html("");
        }
    }

    static renderDump(val){
        var obj = $("#log-wrapper");

        let key;
        for (key in val) {
            var wrapper = document.createElement("div");
            wrapper.className = "col-md-12 log-item";

            var file = document.createElement("span");
            file.className = "log-file";
            file.appendChild(document.createTextNode(val[key]["back_file"]));

            var line = document.createElement("span");
            line.className = "log-line";
            line.appendChild(document.createTextNode("line: " + val[key]["back_line"]));

            $(wrapper).append(file);
            $(wrapper).append(line);

            var wrapper_dump = document.createElement("ul");
            wrapper_dump.className = "col-md-12 log-dump m-0 p-1";

            DevInfo.renderSingle(val[key]["dump"], wrapper_dump);

            $(wrapper).append(wrapper_dump);
            $(obj).append(wrapper);
        }
    }

    static renderSingle(val, obj) {
        if (typeof val == "string") {
            var wrapper_item = document.createElement("li");
            wrapper_item.className = "col-md-12 item-lines";

            var paragrafo = document.createElement("p");
            paragrafo.className = "m-0";

            var dump_val = document.createElement("span");
            dump_val.className = "log-vals";
            dump_val.appendChild(document.createTextNode('"' + val + '"'));
            $(dump_val).css("color", "green");

            $(paragrafo).append(dump_val);
            $(wrapper_item).append(paragrafo);
            $(obj).append(wrapper_item);
        }
        else {
            let key;
            for (key in val) {
                var wrapper_item = document.createElement("li");
                wrapper_item.className = "col-md-12 item-lines";

                var paragrafo = document.createElement("p");
                paragrafo.className = "m-0";
                paragrafo.style = "line-height: 19px;";

                var dump_key = document.createElement("span");
                dump_key.className = "log-keys";
                dump_key.appendChild(document.createTextNode(key + ": "));

                var dump_val = document.createElement("span");
                dump_val.className = "log-vals";

                if (val[key].length == 0) {
                    dump_val.appendChild(document.createTextNode("null \n"));
                    $(dump_val).css("color", "orange");
                }
                else if (typeof val[key] == "object") {
                    dump_val.appendChild(document.createTextNode("Objeto/array \n"));
                    $(dump_val).css("color", "blue");

                    var wrapper_dump = document.createElement("ul");
                    wrapper_dump.className = "col-md-12 log-dump child m-0 p-1";

                    // monta esquema de dropdown
                    $(dump_val).addClass("show_dump_item");

                    dump_val.onclick = function (){
                        $(this).closest("li").toggleClass("active");
                    };

                    DevInfo.renderSingle(val[key], wrapper_dump);

                    $(wrapper_item).append(wrapper_dump);
                }
                else if (typeof val[key] == "string") {
                    dump_val.appendChild(document.createTextNode('"' + val[key] + '" \n'));
                    $(dump_val).css("color", "green");
                }

                $(paragrafo).prepend(dump_val);
                $(paragrafo).prepend(dump_key);
                $(wrapper_item).prepend(paragrafo);
                $(obj).append(wrapper_item);
            }
        }
    }
}
