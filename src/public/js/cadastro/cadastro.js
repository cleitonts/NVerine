function pageUpdate() {
    $("#campo_pessoa_vinculada").autocomplete({
        source: "json.php?pagina=pessoas",
        select: function(event, ui) {
            $("#campo_codigo_pessoa").val(ui.item.id).closest(".form-group").addClass("is-filled");
        }
    });
}

function makeid(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    $("#campo_senha").val(result);
    prompt("Nova senha", result);
}