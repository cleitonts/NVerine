class Modal{
    constructor(){
        this.title = "Modal title";
        this.btnSuccess = "Salvar";
        this.btnCancel = "Cancelar";
        this.size = ""; // "modal-lg, modal-sm";
        this.btnSuccessAct = ""; //function() {FieldTables.deleteLinha(this)};
        this.btnCancelAct = ""; //function() {FieldTables.deleteLinha(this)};
        this.name = "myModal";
        this.no_footer = false;
        this.text = "";
    }

    /**
     * append on .modal-body
     * @returns {HTMLDivElement}
     */
    render(){
        var modal = document.createElement("div");
        modal.className = "modal fade";
        modal.id = this.name;
        modal.setAttribute("tabindex", "-1");
        modal.setAttribute("role", "dialog");
        modal.setAttribute("aria-labelledby", this.name+"Label");
        modal.setAttribute("aria-hidden", "true");

        var dialog = document.createElement("div");
        dialog.className = "modal-dialog "+this.size;
        dialog.setAttribute("role", "document");

        var content = document.createElement("div");
        content.className = "modal-content";

        var header = document.createElement("div");
        header.className = "modal-header";

        var title = document.createElement("h5");
        title.className = "modal-title";
        title.id = this.name+"Label";
        title.appendChild(document.createTextNode(this.title));

        var btn_close = document.createElement("button");
        btn_close.type = "button";
        btn_close.className = "close";
        btn_close.setAttribute("data-dismiss", "modal");
        btn_close.setAttribute("aria-label", "Close");

        var span = document.createElement("span");
        span.setAttribute("aria-hidden", "true");
        span.appendChild(document.createTextNode("\u00D7"));

        var body = document.createElement("div");
        body.className = "modal-body";

        $(body).append(document.createTextNode(this.text));

        var footer = document.createElement("div");
        footer.className = "modal-footer";

        var btn_cancel = document.createElement("button");
        btn_cancel.type = "button";
        btn_cancel.className = "btn btn-danger mr-2";
        btn_cancel.setAttribute("data-dismiss", "modal");
        btn_cancel.onclick = this.btnCancelAct;
        btn_cancel.appendChild(document.createTextNode(this.btnCancel));


        var btn_success = document.createElement("button");
        btn_success.type = "button";
        btn_success.className = "btn btn-success";
        btn_success.onclick = this.btnSuccessAct;
        btn_success.appendChild(document.createTextNode(this.btnSuccess));

        $(footer).prepend(btn_success);
        $(footer).prepend(btn_cancel);
        if(this.no_footer === false){
            $(content).prepend(footer);
        }
        $(content).prepend(body);
        $(btn_close).prepend(span);
        $(header).prepend(btn_close);
        $(header).prepend(title);
        $(content).prepend(header);
        $(dialog).prepend(content);
        $(modal).prepend(dialog);

        return modal;
    }
}