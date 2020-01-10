<?php
spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

include("loader.php");
include("menus.php");

// valida login no front-end
if(!isset($_SESSION["ID"])) {
    include("login.php");
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
    <meta name="author" content="Cleiton Terassi Sorrilha">

    <!-- mobile specific metas
    ================================================== -->
    <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' />
    <meta name="mobile-web-app-capable" content="yes">

    <!-- favicons
    ================================================== -->
    <link rel="shortcut icon" href="<?php asset('img/favicon.png') ?>" >
    <link rel="icon" href="<?php asset('img/favicon.png') ?>" type="image/x-icon">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php asset('css/adm/chartlist.min.css') ?>">
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
    <script src="<?php asset('js/adm/plugins/jasny-bootstrap.min.js') ?>"></script>
    <script src="<?php asset('js/adm/demo.js') ?>"></script>
    <script src="<?php asset('js/adm/plugins/jquery.mask.min.js') ?>"></script>

    <!-- Bundle novo template -->
    <!-- CKeditor -->
    <script src="<?php asset('js/adm/plugins/ckeditor/ckeditor.js') ?>"></script>
    <script src="<?php asset('js/widget/Alert.js') ?>"></script>
    <script src="<?php asset('js/widget/Body.js') ?>"></script>
    <script src="<?php asset('js/widget/Creator.js') ?>"></script>
    <script src="<?php asset('js/widget/CustomChart.js') ?>"></script>
    <script src="<?php asset('js/widget/DevInfo.js') ?>"></script>
    <script src="<?php asset('js/widget/Field.js') ?>"></script>
    <script src="<?php asset('js/widget/FieldTables.js') ?>"></script>
    <script src="<?php asset('js/widget/Form.js') ?>"></script>
    <script src="<?php asset('js/widget/Header.js') ?>"></script>
    <script src="<?php asset('js/widget/Message.js') ?>"></script>
    <script src="<?php asset('js/widget/Tabelas.js') ?>"></script>
    <script src="<?php asset('js/widget/Tools.js') ?>"></script>
    <script src="<?php asset('js/widget/Calendario.js') ?>"></script>
    <script src="<?php asset('js/widget/Modal.js') ?>"></script>
    <script src="<?php asset('js/widget/Gallery.js') ?>"></script>
    <script src="<?php asset('js/adm/js.js') ?>"></script>
</head>
<body>
<!--suppress JSAnnotator -->
<script>
    // carrega alguns valores para o javascript
    const __FILIAL__ = <?=__FILIAL__?> ;
    const __COMISSAO_FATOR__ = <?= (empty(__COMISSAO_FATOR__))? 0 : __COMISSAO_FATOR__; ?> ;
    const __COMISSAO_PERC__ = <?= (empty(__COMISSAO_PERC__))? 0 : __COMISSAO_PERC__; ?> ;
    const __CASAS_DECIMAIS__ = <?= (empty(__CASAS_DECIMAIS__))? 0 : __CASAS_DECIMAIS__; ?> ;
    const __PASTA__ = '<?=_pasta?>' ;
    const __MAX_DESCONTO_VENDA__ = <?= (empty(__MAX_DESCONTO_VENDA__))? 0 : __MAX_DESCONTO_VENDA__; ?>;
    const __MODELO_NF__ = <?= (empty(__MODELO_NF__))? 0 : __MODELO_NF__;?>;

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
<!-- funcionalidades padrão -->
<div id="spinner" class="loader">Loading...</div>
<input type="hidden" id="cont_spinner" value="0">
<div class="dark-overlay overlay-spinner"></div>
<div class="dark-overlay overlay-message"></div>
<form name="debug" id="form_debug" action="actions.php?pagina=toggledebug" method="post" class="d-none"></form>
<form name="ver_empresa" id="form_ver_empresa" action="actions.php?pagina=toggleverempresa" method="post" class="d-none"></form>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top m-0">
    <div class="container-fluid">
        <div class="navbar-wrapper">
            <a href="index.php"><img src="src/public/img/logo-nav.png"></a>
        </div>

        <form class="navbar-form">
            <div class="input-group no-border">
                <input type="text" value="" id="live-search" class="form-control" placeholder="Pesquisar...">
                <button type="submit" class="btn btn-info btn-round btn-just-icon d-none d-sm-block">
                    <i class="material-icons">search</i>
                </button>
            </div>
        </form>

        <div class="my-2 my-md-0" id="navigation">
            <ul class="navbar-nav mt-0">
                <!-- menu principal-->
                <li class="nav-item dropdown main-menu">
                    <a class="nav-link p-2 p-md-3" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">menu</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" id="perfect-sb-menu">
                        <h4 class="dropdown-header">Menu</h4>
                        <form class="accordion" id="accordionExample">
                            <?php getMenu();?>
                        </form>
                    </div>
                </li>

                <?php if($permissoes->libera("Administração")) { ?>
                    <!-- menu da administração-->
                    <li class="nav-item dropdown admin-menu">
                        <a href="#" class="nav-link p-2 p-md-3" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="material-icons">edit</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h4 class="dropdown-header">Administração</h4>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('cadastro&tn=Usuário&tabela=<?=encrypt("K_PD_USUARIOS")?>')">Usuários</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('cadastro&tn=Grupos&tabela=<?=encrypt("K_FN_GRUPOUSUARIO")?>')">Grupos de usuário</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('cadastro&tn=Usuários e filiais&tabela=<?=encrypt("K_FN_USUARIOFILIAL")?>')">Usuários e filiais</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('cadastro&tn=Alçadas&tabela=<?=encrypt("K_PD_ALCADAS")?>')">Alçadas</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('admin_permissoes')">Permissões</h5>
                            </div>
                            <div class="menu-body">
                                <h5 class="menu-header" onclick="destinoMenu('cadastro&tn=Região&tabela=<?=encrypt("K_REGIAO")?>')">Regiões</h5>
                            </div>
                            <?php if($permissoes->libera("Master")) { ?>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('admin_dicionario')">Dicionário</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('admin_auditoria')">Auditoria</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('admin_config')">Editor de config.</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('admin_atualizacoes')">Atualizações</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('admin_upgrade')">Instalar novos módulos</h5>
                                </div>
                                <div class="menu-body">
                                    <h5 class="menu-header" onclick="destinoMenu('admin_backup')">Importar backup</h5>
                                </div>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>

                <!-- mensagens -->
                <li class="nav-item dropdown message-menu">
                    <a class="nav-link p-2 p-md-3" href="#" id="messageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">mail</i>
                        <?php if(count($notificacoes->mensagens) > 0) { ?><span class="notification"><?=count($notificacoes->mensagens)?></span><?php } ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h4 class="dropdown-header">Mensagens</h4>
                        <?php
                        global $notificacoes;
                        $msg = $notificacoes;

                        $i = 0; // onclick="destinoMenu('mensagens&u=<?=urlencode(getUrlRetorno())

                        if(!empty($msg->mensagens)) {
                            foreach ($msg->mensagens as $elemento) {?>
                                <div class="menu-body">
                                    <h5 class="menu-header small" onclick="destinoMenu('mensagens&u=<?=urlencode(getUrlRetorno())?>')">
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
                            <h5 class="menu-header" onclick="destinoMenu('mensagens&u=<?=urlencode(getUrlRetorno())?>')">Ver mais</h5>
                        </div>
                    </div>
                </li>

                <!-- alertas -->
                <li class="nav-item dropdown alert-menu">
                    <a class="nav-link p-2 p-md-3" href="" id="alertDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="material-icons">notifications</i>
                        <?php if(count($notificacoes->alertas) > 0) { ?><span class="notification"><?=count($notificacoes->alertas)?></span><?php } ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h4 class="dropdown-header">Mensagens</h4>
                        <?php
                        if(!empty($notificacoes->alertas)) {
                            foreach ($notificacoes->alertas as $elemento) {?>
                                <a class="dropdown-item py-1 d-block px-2" href="<?=$elemento->link?>">
                                    <p class="row m-0" style="white-space:pre-wrap;">
                                        <?=$elemento->texto?>
                                    </p>
                                    <p class="m-0 mt-2">
                                        <span class="misterio">
                                            <i class="fa fa-calendar-o"></i>&ensp;<?=converteDataSql($elemento->data)?>
                                        </span>
                                    </p>
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
                    <a class="nav-link p-2 p-md-3" href="" id="ususarioDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <h5 onclick="window.location='sair.php'" class="menu-header">Sair</h5>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->

<div id="page-scroll" class="page-scroll">
    <div class="sitemap mt-md-4 mt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="float-left">
                        <?=__SISTEMA__?>
                        <span class="tipsy-n" onclick="destinoMenu('cadastro&amp;tn=Empresa&amp;tabela=VXpFNVIxUnNPVWRUVlhoS1VWVjNQUT09&amp;i=1')" title="Editar cadastro"><i class="fa fa-pencil"></i></span>
                        <span class="ver-filiais tipsy-n" onclick="$('#form_ver_empresa').submit()" title="Ver todas as filiais"><i class="fa fa-eye"></i></span>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="float-right mt-md-0 mt-2 mt-sm-0">
                        <span id="modulo">Modulo</span>
                        <i class="fa fa-angle-right"></i>
                        <span id="pagina">Página</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper">
        <div class="main-panel">
            <div class="content pb-0 pt-2 p-md-3 p-1">
                <div class="container-fluid" id="main-content">
                    <div id="disable_overlay"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- log -->
<div id="message-log">
    <h5 class="p-3 m-0">Mensagens do sistema</h5>
    <div class="messages-wrapper m-0 p-1">
        <ol id="lista-mensagens" class="pl-1 pr-1">

        </ol>
    </div>
</div>

<button type="button" id="togglemenu" onclick="toggleMenu()" class="btn btn-info btn-round float-right fa fa-bars d-none position-fixed"></button>

<footer class="navbar footer p-0 sticky-bottom footer-light footer-shadow content container-fluid m-0">
    <div class="float-left col-sm-12 col-md pl-4">
        <small><?=__NOME_SISTEMA__?></small>
    </div>

    <div class="float-left col">
        <!-- abre em uma popup -->
        <div class="float-left developer-tools">
            <div class="btn btn-white" onclick="window.open(window.location.href,'winname','directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no');">
                <i class="fa fa-expand"></i>
            </div>
        </div>

        <!-- abre em uma popup -->
        <div class="float-left developer-tools">
            <div class="btn btn-white" onclick="toggleMenu()">
                <i class="fa fa-bars"></i>
            </div>
        </div>

        <!-- Ferramentas de log do sistema -->
        <div class="float-left developer-tools">
            <div class="btn btn-white" onclick="Message.toggle(this)">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <div class="container" id="message-log"></div>
        </div>

        <?php if(__DEVELOPER__){?>
            <!-- Ferramentas de desenvolvedor -->
            <div class="float-left developer-tools">
                <div class="btn btn-white" onclick="$('#log-wrapper').toggleClass('active')">
                    <i class="fa fa-laptop"></i>
                </div>
                <div class="log-wrapper container col-md-12" id="log-wrapper"></div>
            </div>
        <?php }?>
    </div>

    <div class="float-right col pr-4 d-flex justify-content-end">
        <div class="btn-group dropup mb-2">
            <button type="button" class="btn btn-sm btn-white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Central de ajuda
            </button>
            <div class="dropdown-menu" style="top:-190px;left:-130px;">
                <h4 class="dropdown-header"><i class="fa fa-envelope-o"></i>&ensp;cleitonsorrilha@gmail.com</h4>
                <a class="dropdown-item" href="#" onclick="destinoMenu('abre_chamado&u=<?=urlencode(getUrlRetorno())?>')">Relatar problema</a>
                <a class="dropdown-item" href="#" onclick="destinoMenu('ajuda')">Documentação online</a>
                <a class="dropdown-item" href="#" onclick="$('#form_debug').submit()">Habilitar <i>debug</i></a>
                <a class="dropdown-item" href="#" onclick="$('.about').fadeIn()">Sobre o NVerine...</a>
            </div>
        </div>

    </div>
</footer>
</body>
</html>