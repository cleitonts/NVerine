<?php
spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

if(is_dir("uploads/")){
    header('Location: index.php');
}

// instancia mensagens globais
$mensagens = new \src\services\SysMessages\Messages();

// executa a instalação
if(!empty($_REQUEST["name"])){
    $mensagens->retorno = "refresh";
    $instalador = new \src\entity\SuporteDiagETT();
    $instalador->segmento = "empresarial";
    $instalador->db_host = $_REQUEST["host"];
    $instalador->db_name = $_REQUEST["name"];
    $instalador->db_user = $_REQUEST["user"];
    $instalador->db_pass = $_REQUEST["pass"];
    $instalador->nome = $_REQUEST["nome"];
    $instalador->cadastra();
    $mensagens->pronto(); // só isso!
    die();
}
?>

<!doctype html>
<html lang="pt-br">

<head>
    <!--- basic page needs
    ================================================== -->
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#00acc1">
    <meta name="msapplication-TileColor" content="#00acc1">
    <meta name="robots" content="noindex, nofollow">

    <title><?=__NOME_SISTEMA__?></title>
    <link rel="shortcut icon" href="<?php asset('img/favicon.png') ?>" type="image/x-icon">
    <meta name="author" content="Cleiton Terassi Sorrilha">

    <!-- mobile specific metas
    ================================================== -->
    <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' />
    <meta name="mobile-web-app-capable" content="yes">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php asset('css/adm/material-dashboard.css?v=2.0.1') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/demo.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/theme.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/spinner.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/calendario.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/dumper.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/mensagens.css') ?>">

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,700,300|Material+Icons' rel='stylesheet' type='text/css'>
    <script src="<?php asset('js/adm/core/jquery.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/jquery-1.12.4.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/jquery-ui.js') ?>"></script>

    <!-- script
    ================================================== -->
    <script src="<?php asset('js/adm/core/popper.min.js') ?>"></script>
    <script src="<?php asset('js/adm/bootstrap-material-design.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/moment.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/bootstrap-datetimepicker.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/chartist.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/arrive.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/bootstrap-notify.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/bootstrap-selectpicker.js') ?>"></script>
    <script src="<?php asset('js/adm/material-dashboard.js?v=2.0.0') ?>"></script>
    <script src="<?php asset('js/adm/demo.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/jquery.mask.min.js') ?>"></script>
    <script src="<?php asset('js/widget/Alert.js') ?>"></script>
    <script>
        // fix para smartphones
        // ================================================================================================================
        // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
        let vh = window.innerHeight * 0.01;
        // Then we set the value in the --vh custom property to the root of the document
        document.documentElement.style.setProperty('--vh', `${vh}px`);

        // We listen to the resize event
        window.addEventListener('resize', () => {
            // We execute the same script as before
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        });
    </script>
</head>
<body class="off-canvas-sidebar">

<!-- funcionalidades padrão -->
<div id="spinner" class="loader">Loading...</div>
<input type="hidden" id="cont_spinner" value="0">
<div class="dark-overlay overlay-spinner"></div>
<div class="dark-overlay overlay-message"></div>

<div class="wrapper wrapper-full-page">
    <div class="page-header login-page header-filter" filter-color="black" style="background-image: url('src/public/img/login.jpg'); background-size: cover; background-position: top center;">
        <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
        <div class="container py-0">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-8 ml-auto mr-auto">
                    <form id="form_cadastro" class="form">
                        <input type="hidden" name="url_retorno" value="<?=$retorno;?>">
                        <div class="card">
                            <div class="card-header card-header-success text-center">
                                <!--div class="img-wrapper" style="overflow: hidden;">
                                    <img src="<?php asset('img/logo.png') ?>" alt="" style="width: 100%;margin-bottom: -23px;">
                                </div-->
                                <h2 class="card-title">NVerine</h2>
                            </div>
                            <div class="card-body ">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group bmd-form-group">
                                            <label class="bmd-label-floating" for="host">DB Host</label>
                                            <input type="text" id="host" value="localhost" name="host" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group bmd-form-group">
                                            <label class="bmd-label-floating" for="name">DB Name</label>
                                            <input type="text" id="name" name="name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group bmd-form-group">
                                            <label class="bmd-label-floating" for="user">DB User</label>
                                            <input type="text" id="user" name="user" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group bmd-form-group">
                                            <label class="bmd-label-floating" for="pass">DB Pass</label>
                                            <input type="password" id="pass" name="pass" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group bmd-form-group">
                                            <label class="bmd-label-floating" for="nome">Nome da instituição</label>
                                            <input type="text" id="nome" name="nome" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer justify-content-center">
                                <button type="submit" class="btn btn-success btn-block btn-lg">Instalar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <div class="copyright">
                    © 2020 NVerine.
                </div>
            </div>
            <script>
                $(document).ready(function(){
                    $("#form_cadastro").submit(function(e){
                        e.preventDefault();
                        submit();
                    })
                });

                function submit() {
                    spinner(true);

                    $.ajax({
                        url: "index.php",
                        dataType: "json",
                        type: 'POST',
                        data: $("#form_cadastro").serialize()
                    }).done(function (valores) {
                        if (valores.retorno === "refresh") {
                            window.location.href = 'index.php';
                        }
                        if (valores.messages.length > 0) {
                            for (var i = 0; i < valores.messages.length; i++){
                                var popup = new Alert();
                                popup.typo = valores.messages[i].typo;
                                popup.texto = valores.messages[i].text;
                                popup.act_close = valores.messages[i].onclose;
                                // instancia popup de erro
                                if(valores.messages[i].typo !== "danger"){
                                    popup.tempo = 5000;
                                }

                                popup.montaMensagem();
                            }
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

                // exibe ou esconde o overlay de carregamento de paginas via ajax
                function spinner(tipo = true) {
                    //console.trace();
                    var val = parseInt($("#cont_spinner").val());
                    if(tipo == true) {
                        val++;
                        if(val > 0){
                            $("#spinner, .dark-overlay.overlay-spinner").fadeIn();
                        }
                        $("#cont_spinner").val(val);

                    }
                    else {
                        val--;
                        if(val < 0){
                            console.log("spinner não foi aberto mais foi fechado");
                        }
                        if(val == 0){
                            $("#spinner, .dark-overlay.overlay-spinner").fadeOut();
                        }
                        $("#cont_spinner").val(val);
                    }
                }
            </script>
        </footer>
    </div>
</div>
</body>
</html>