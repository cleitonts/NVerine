/**
 * monta a Header do widget
 */
class Header{
    /**
     *
     * @param obj ponto de inserção
     * @param header valores a serem processados
     * cria node da Header
     */
    static create(obj, header){
        let html = document.createElement("div");
        html.className = "card-header row card-header-info card-header-primary " + header.class;

        var grid1 = document.createElement("div");
        grid1.className = "col-md-4";

        var grid2 = document.createElement("div");
        grid2.className = "col-md-8 pl-md-3 pr-md-3 p-0";

        // cria submenu
        if(header.menu.length > 0){
            var wrapper= document.createElement("div");
            wrapper.className = "dropdown float-left mr-3";

            var btn = document.createElement("a");
            btn.className = "btn btn-white btn-sm btn-round btn-fab fa fa-ellipsis-v";
            btn.id = "dropdownMenuButton";
            btn.href = "#";
            btn.title = "Mais ações";
            btn.setAttribute("data-toggle", "dropdown");
            btn.setAttribute("rolw", "button");
            btn.setAttribute("aria-haspopup", "true");
            btn.setAttribute("aria-expanded", "false");

            var dropdown = document.createElement("div");
            dropdown.className = "dropdown-menu";
            dropdown.setAttribute("aria-labelledby", "dropdownMenuButton");

            for(var i = 0; i < header.menu.length; i++){
                var item = document.createElement("a");
                item.href = "#";
                item.className = "dropdown-item ";
                $(item).append(document.createTextNode(header.menu[i].description));
                $(item).attr("onclick", header.menu[i].function); // chama função a partir do valor do objeto

                var icon = document.createElement("i");
                icon.className = "mr-2 "+header.menu[i].icon;
                $(item).prepend(icon);

                if(header.menu[i].description.length == 0){
                    item = document.createElement("div");
                    item.href = "#";
                    item.className = "dropdown-divider";
                }
                $(dropdown).append(item);
            }

            $(wrapper).append(btn);
            $(wrapper).append(dropdown);
            $(grid1).append(wrapper);
        }

        // cria node do titulo
        if(header.title.length > 0){
            let title = document.createElement("h4");
            title.className = "card-title";

            // cria node de icon
            if(header.icon.length > 0){
                let icon = document.createElement("i");
                icon.className ="mr-2 " + header.icon;
                title.appendChild(icon);
            }

            // cria o texto e salva na var principal
            title.appendChild(document.createTextNode(header.title));
            grid1.appendChild(title);
            html.appendChild(grid1);
        }
        if(header.tabs.length > 0){

            var options = document.createElement("div");
            options.className = "nav-tabs-navigation pull-right";

            var tabs_wrapper = document.createElement("div");
            tabs_wrapper.className = "nav-tabs-wrapper";

            var tabs =  document.createElement("ul");
            tabs.className = "nav nav-tabs mt-0";
            tabs.setAttribute("role", "tablist");

            // itera cada item da tab
            for(var i = 0; i < header.tabs.length; i++){
                // cria a tab
                var tab = document.createElement("li");
                tab.className = "nav-item";

                var tab_link = document.createElement("a");
                tab_link.className = "nav-link";
                tab_link.setAttribute("data-toggle", "tab");
                tab_link.setAttribute("role", "tablist");
                tab_link.href = "#sd"+(i+1);

                if(header.tabs[i].function.length > 0) {
                    $(tab_link).attr("onclick", header.tabs[i].function); // chama função a partir do valor do objeto
                }

                if(i == 0){
                    tab_link.className = tab_link.className + " active";
                }

                // cria o icon
                var icon_tab = document.createElement("i");
                icon_tab.className = "material-icons "+header.tabs[i].icon;
                $(tab_link).append(icon_tab);

                // cria texto
                var span = document.createElement("span");

                $(span).append(document.createTextNode(Tools.capitalize(header.tabs[i].description)));
                $(tab_link).append(span);
                $(tab).prepend(tab_link);
                $(tabs).append(tab);
            }

            $(tabs_wrapper).append(tabs);
            $(options).append(tabs_wrapper);
            $(grid2).append(options);
            $(html).append(grid2);
        }
        $(obj).prepend(html);
    }
}
