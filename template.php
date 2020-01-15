<?php

spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

include("loader.php");

// valida login no front-end
if(!isset($_SESSION["ID"])) {
    include("ui2/login.php");
    return;
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
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <meta name="author" content="Cleiton Terassi Sorrilha">

    <!-- mobile specific metas
    ================================================== -->
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />

    <!-- favicons
    ================================================== -->
    <link rel="shortcut icon" href="<?php asset('adm/img/favicon.png.ico') ?>" >
    <link rel="icon" href="<?php asset('adm/img/favicon.png.ico') ?>" type="image/x-icon">

    <!-- CSS
    ================================================== -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.8.1/css/froala_editor.pkgd.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.8.1/css/froala_style.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php asset('css/adm/perfect-scrollbar.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/material-dashboard.css?v=2.0.1') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/demo.css') ?>">
    <link rel="stylesheet" href="<?php asset('css/adm/theme.css') ?>">
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
    <script src="<?php asset('js/adm/plugins/perfect-scrollbar.jquery.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/bootstrap-datetimepicker.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/chartist.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/arrive.min.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/bootstrap-notify.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/bootstrap-selectpicker.js') ?>"></script>
    <script src="<?php asset('js/adm/material-dashboard.js?v=2.0.0') ?>"></script>
    <script src="<?php asset('js/adm/demo.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/jquery.mask.min.js') ?>"></script>

    <!-- Include Editor JS files. -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.8.1/js/froala_editor.pkgd.min.js"></script>

    <!-- Bundle novo template -->
    <!--script src="<?php asset('js/widget/Alert.js') ?>"></script>
    <script src="<?php asset('js/widget/Body.js') ?>"></script>
    <script src="<?php asset('js/widget/Creator.js') ?>"></script>
    <script src="<?php asset('js/widget/CustomChart.js') ?>"></script>
    <script src="<?php asset('js/widget/DevInfo.js') ?>"></script>
    <script src="<?php asset('js/widget/Field.js') ?>"></script>
    <script src="<?php asset('js/widget/FieldTables.js') ?>"></script>
    <script src="<?php asset('js/widget/Form.js') ?>"></script>
    <script src="<?php asset('js/widget/Header.js') ?>"></script>
    <script src="<?php asset('js/widget/Message.js') ?>"></script>
    <script src="<?php asset('js/widget/TabelasETT.js') ?>"></script>
    <script src="<?php asset('js/widget/Tools.js') ?>"></script>
    <script src="<?php asset('js/adm/js.js') ?>"></script-->

</head>
<body>


<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <div class="navbar-wrapper">
            <a href="index.php"><img src="ui2/img/logo-nav.png"></a>
        </div>

        <form class="navbar-form">
            <div class="input-group no-border">
                <input type="text" value="" id="live-search" class="form-control" placeholder="Pesquisar...">
                <button type="submit" class="btn btn-info btn-round btn-just-icon">
                    <i class="material-icons">search</i>
                </button>
            </div>
        </form>

        <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
                <!-- menu principal-->
                <li class="nav-item dropdown main-menu">
                    <a class="nav-link" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">menu</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" id="perfect-sb-menu">
                        <h4 class="dropdown-header">Menu</h4>
                        <form class="accordion" id="accordionExample">
                            <?php include("menus.php");?>
                        </form>
                    </div>
                </li>

                <?php if($permissoes->libera("Administração")) { ?>
                    <!-- menu da administração-->
                    <li class="nav-item dropdown admin-menu">
                        <a href="#" class="nav-link" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="material-icons">edit</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h4 class="dropdown-header">Administração</h4>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('?pagina=cadastro&tn=Usuário&tabela=<?=encrypt("K_PD_USUARIOS")?>')">Usuários</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('?pagina=cadastro&tn=Grupos&tabela=<?=encrypt("K_FN_GRUPOUSUARIO")?>')">Grupos de usuário</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('?pagina=cadastro&tn=Usuários e filiais&tabela=<?=encrypt("K_FN_USUARIOFILIAL")?>')">Usuários e filiais</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('?pagina=cadastro&tn=Alçadas&tabela=<?=encrypt("K_PD_ALCADAS")?>')">Alçadas</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_permissoes')">Permissões</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('?pagina=cadastro&tn=Região&tabela=<?=encrypt("K_REGIAO")?>')">Regiões</h5>
                            </div>
                            <?php if($permissoes->libera("Master")) { ?>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_dicionario')">Dicionário</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_auditoria')">Auditoria</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_config')">Editor de config.</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_atualizacoes')">Atualizações</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_upgrade')">Instalar novos módulos</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=admin_backup')">Importar backup</h5>
                                </div>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>

                <!-- mensagens -->
                <li class="nav-item dropdown message-menu">
                    <a class="nav-link" href="#" id="messageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">mail</i>
                        <?php if(count($notificacoes->mensagens) > 0) { ?><span class="notification"><?=count($notificacoes->mensagens)?></span><?php } ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h4 class="dropdown-header">Mensagens</h4>
                        <?php
                        $msg = new Notificacoes\Notifier();
                        $msg->fetch();

                        $i = 0; // onclick="destinoMenu('mensagens&u=<?=urlencode(getUrlRetorno())

                        if(!empty($msg->mensagens)) {
                            foreach ($msg->mensagens as $elemento) {?>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('?pagina=mensagens&u=<?=urlencode(getUrlRetorno())?>')">
                                        <p><?=$elemento->texto?></p>
                                        <p><span class="misterio"><i class="fa fa-calendar-o"></i>&ensp;<?=converteDataSql($elemento->data)?></span></p>
                                    </h5>
                                </div>
                                <?php
                                // limita às X últimas mensagens
                                $i++;
                                if($i >= 5) break;
                            }
                        }
                        ?>
                        <div class="dropdown-divider"></div>
                        <div class="menu-body">
                            <h5 class="menu-header" onclick="destinoMenu('?pagina=mensagens&u=<?=urlencode(getUrlRetorno())?>')">Ver mais</h5>
                        </div>
                    </div>
                </li>

                <!-- alertas -->
                <li class="nav-item dropdown alert-menu">
                    <a class="nav-link" href="" id="alertDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">notifications</i>
                        <?php if(count($notificacoes->alertas) > 0) { ?><span class="notification"><?=count($notificacoes->alertas)?></span><?php } ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h4 class="dropdown-header">Mensagens</h4>
                        <?php
                        if(!empty($notificacoes->alertas)) {
                            foreach ($notificacoes->alertas as $elemento) {?>
                                <a class="dropdown-item" href="<?=$elemento->link?>">
                                    <?=$elemento->texto?>
                                    <p><span class="misterio"><i class="fa fa-calendar-o"></i>&ensp;<?=converteDataSql($elemento->data)?></span></p>
                                </a>
                                <?php
                            }
                        }
                        else{
                            ?>
                            <div class="menu-body">
                                <h5 class="menu-header">
                                    <span class="misterio">Sem novas notificações</span>
                                </h5>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </li>

                <!-- alertas -->
                <li class="nav-item dropdown user-menu">
                    <a class="nav-link" href="" id="ususarioDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">person</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h4 class="dropdown-header"><img src="<?=_pasta.$perfil->avatar?>"></h4>
                        <div class="menu-body">
                            <h5 onclick="destinoMenu('perfil&u=<?=urlencode(getUrlRetorno())?>')" class="menu-header">
                                <?=$perfil->nome?>
                                <span class="misterio">
                                    <?=$perfil->grupo?>
                                </span>
                            </h5>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="menu-body">
                            <h5 onclick="destinoMenu('sair.php')" class="menu-header">Sair</h5>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
<script>
    $(document).ready(function(){
        const ps_menu = new PerfectScrollbar('#perfect-sb-menu');

        $(".menu-header").click(function(){
            if($(this)[0] == $(".menu-body.active .menu-header")[0]){
                $(".menu-body.active").toggleClass("active").find(".menu-list").slideUp( "slow", function() {});
            }
            else{
                $(".menu-body.active").toggleClass("active").find(".menu-list").slideUp( "slow", function() {});
                $(this).closest(".menu-body").toggleClass("active").find(".menu-list").slideDown( "slow", function() {});
            }
        });
    });
</script>

<div class="sitemap">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="float-left hidden-sm">
                    Escola Municipal Paulo Barbosa
                    <span class="tipsy-n" onclick="destinoMenu('cadastro&amp;tn=Empresa&amp;tabela=VXpFNVIxUnNPVWRUVlhoS1VWVjNQUT09&amp;i=1')" original-title="Editar cadastro"><i class="fa fa-pencil"></i></span>
                    <span class="ver-filiais tipsy-n" onclick="$('#form_ver_empresa').submit()" original-title="Ver todas as filiais"><i class="fa fa-eye"></i></span>
                    <span class="night-mode tipsy-n" original-title="Modo noturno"><i class="fa fa-moon-o"></i></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="float-right">
                    <span id="modulo">Educacional</span>
                    <i class="fa fa-angle-right"></i>
                    <span id="pagina">Turma</span>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="wrapper">
    <div class="main-panel">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="card">
                        <div class="card-header row card-header-info card-header-primary">
                            <div class="col-md-6">
                                <h4 class="card-title">Edição</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="nav-tabs-navigation pull-right">
                                    <div class="nav-tabs-wrapper">
                                        <ul class="nav nav-tabs" data-tabs="tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" href="#profile" data-toggle="tab">
                                                    <i class="material-icons">bug_report</i>Bugs
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#messages" data-toggle="tab">
                                                    <i class="material-icons">code</i>Website
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#settings" data-toggle="tab">
                                                    <i class="material-icons">cloud</i>Server
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="btn btn-white btn-round btn-just-icon retornar" href="/admin/usuarios/">
                                                    <i class="material-icons">keyboard_arrow_left</i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form name="usuarios" method="post" autocomplete="off">
                                <div id="usuarios">
                                    <div class="row">
                                        <div class="col-md-2 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating" for="usuarios_id">Id</label>
                                                <input type="number" id="usuarios_id" name="usuarios[id]" readonly="readonly" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating" for="usuarios_name">Nome</label>
                                                <input type="text" id="usuarios_name" name="usuarios[name]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating" for="usuarios_username">Username</label>
                                                <input type="text" id="usuarios_username" name="usuarios[username]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="label-select bmd-label-floating required" for="usuarios_nivel">Nivel</label>
                                                <div class="btn-group bootstrap-select">
                                                    <select class="selectpicker" id="usuarios_nivel" name="usuarios[nivel]" tabindex="-98">
                                                        <option value="1">administração</option>
                                                        <option value="1">administração</option>
                                                        <option value="1">administração</option>
                                                        <option value="1">administração</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="wrapper_tipo"><div class="form-group bmd-form-group"><label class="bmd-label-floating label-select" for="campo_tipo">Tipo</label><div class="btn-group bootstrap-select"><select class="selectpicker" name="tipo" id="campo_tipo"><option label="Pessoa física" value="F" selected="selected">Pessoa física</option><option label="Pessoa jurídica" value="J">Pessoa jurídica</option></select></div></div></div>


                                        <div class="col-md-4 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating" for="usuarios_email">E-mail</label>
                                                <input type="email" id="usuarios_email" name="usuarios[email]" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating" for="data">data(date)</label>
                                                <input type="text" id="data" name="usuarios[data]" class="form-control datepicker-date">
                                            </div>
                                        </div>

                                        <div class="col-md-4 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating" for="dataehora">Data e hora(datetime)</label>
                                                <input type="text" id="dataehora" name="usuarios[dataehora]" class="form-control datepicker-datetime">
                                            </div>
                                        </div>

                                        <div class="col-md-4 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating required" for="hora">hora(time)</label>
                                                <input type="text" id="hora" name="usuarios[hora]" class="form-control datepicker-time">
                                            </div>
                                        </div>

                                        <script>
                                            $(document).ready(function(){
                                                moment.updateLocale('pt-br', {
                                                    weekdaysMin : ["D", "S", "T", "Q", "Q", "S", "S"],
                                                    weekdaysShort: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"]
                                                });

                                                var dateicons = {
                                                    time: "fa fa-clock-o",
                                                        date: "fa fa-calendar",
                                                        up: "fa fa-chevron-up",
                                                        down: "fa fa-chevron-down",
                                                        previous: 'fa fa-chevron-left',
                                                        next: 'fa fa-chevron-right',
                                                        today: 'fa fa-screenshot',
                                                        clear: 'fa fa-trash',
                                                        close: 'fa fa-remove'
                                                };

                                                $(".datepicker-datetime").datetimepicker({
                                                    format: 'DD-MM-YYYY LT',
                                                    icons: dateicons,
                                                    locale: 'pt-br'
                                                }).blur(function(){
                                                    $(this).closest(".form-group").addClass("is-filled");
                                                });
                                                $(".datepicker-time").datetimepicker({
                                                    format: 'LT',
                                                    icons: dateicons,
                                                    locale: 'pt-br'
                                                }).blur(function(){
                                                    $(this).closest(".form-group").addClass("is-filled");
                                                });
                                                $(".datepicker-date").datetimepicker({
                                                    format: 'DD-MM-YYYY',
                                                    icons: dateicons,
                                                    locale: 'pt-br'
                                                }).blur(function(){
                                                    $(this).closest(".form-group").addClass("is-filled");
                                                });
                                            });
                                        </script>

                                        <div class="col-md-2 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating required" for="usuarios_password_first">Password</label>
                                                <input type="password" id="usuarios_password_first" name="usuarios[password][first]" required="required" class="form-control"></div>
                                        </div>
                                        <div class="col-md-2 ">
                                            <div class="form-group bmd-form-group">
                                                <label class="bmd-label-floating required" for="usuarios_password_second">Repeat Password</label>
                                                <input type="password" id="usuarios_password_second" name="usuarios[password][second]" required="required" class="form-control"></div>
                                        </div>
                                        <div class="form-group col-md-12 no-margin ">
                                            <button type="submit" id="usuarios_send" name="usuarios[send]" class="btn-success pull-right btn">Salvar</button>
                                        </div>
                                        <input type="hidden" id="usuarios__token" name="usuarios[_token]" value="ZPgKHJIhFw8jYb_ot_Y9UnhnXe3E7zvpAE84Ahw5tZA">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header row card-header-info card-header-success">
                            <div class="col-md-6">
                                <h4 class="card-title">Cores</h4>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn-success pull-right btn">Success</button>
                                    <button type="button" class="btn-danger pull-right btn">Danger</button>
                                    <button type="button" class="btn-warning pull-right btn">Warning</button>
                                    <button type="button" class="btn-rose pull-right btn">Rose</button>
                                    <button type="button" class="btn-info pull-right btn">Info</button>
                                    <button type="button" class="btn-primary pull-right btn">Primary</button>
                                    <a href="https://demos.creative-tim.com/material-dashboard-pro/examples/dashboard.html" class="btn btn-info">Referencia</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>