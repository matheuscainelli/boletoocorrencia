<?php
session_start();
ob_start();
require 'config/config.php';
require 'class/html.php';
require 'class/database.php';
require 'class/session.php';
require 'class/form.php';
require 'class/filter.php';
require_once 'class/funcoes.php';

VerificaAcesso();
?>

<!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title><?= nmSistema; ?></title>
            <script src="js/jquery-3.4.1.min.js"></script>
            <script src="js/bootstrap.min.js"></script>
            <script src="datatables/datatables.min.js"></script>
            <script src="js/inspinia.js"></script>
            <script src="js/jquery.metisMenu.js"></script>
            <script src="js/jquery.slimscroll.min.js"></script>
            <script src="summernote/summernote.js"></script>
            <script src="summernote/lang/summernote-pt-BR.js"></script>
            <script src="datepicker/bootstrap-datepicker.js"></script>
            <script src="datepicker/locales/bootstrap-datepicker.pt-BR.min.js"></script>

            <link rel="icon" type="imagem/png" href="img/icon.png" />
            <link rel="stylesheet" href="css/bootstrap.css"/>
            <link rel="stylesheet" href="font-awesome/css/font-awesome.css"/>
            <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css"/>
            <link rel="stylesheet" href="css/estilos.css"/>
            <link rel="stylesheet" href="css/style.css"/>
            <link rel="stylesheet" href="datatables/datatables.min.css"/>
            <link rel="stylesheet" href="summernote/summernote.css">
            <link rel="stylesheet" href="datepicker/datepicker.css ">
        </head>

        <script type="text/javascript">
                $(document).ready(function() {
                    $('#tableCadastro, #tableFilter').DataTable({
                        searching: false,
                        language: {
                            paginate: {
                                previous: '«',
                                next: '»'
                            }
                        }
                    });

                    $('#DSOCORRENCIA').summernote({
                        height: 290,
                        toolbar: [
                            ["style", ["style"]],
                            ["font", ["bold", "underline", "clear"]],
                            ["fontname", ["fontname"]],
                            ["color", ["color"]],
                            ["para", ["ul", "ol", "paragraph"]],
                            ["view", ["codeview"]]
                        ],
                    });
                });
        </script>
        <body>
            <div id="wrapper">
                <nav class="navbar-default navbar-static-side" role="navigation">
                    <div class="sidebar-collapse">
                        <ul class="nav metismenu" id="side-menu">
                            <li class="nav-header">
                                <div class="dropdown profile-element" title="">
                                    <a href="index.php">
                                        <img src="img/icon.png" style='width: 30px;height: 31px;display: block; margin-left:auto; margin-right: auto;' title="Início" class="logo-img"/>
                                    </a>
                                </div>
                                <div class="logo-element" title="">
                                    <img src="img/icon.png" width="30px">
                                </div>
                            </li>
                            <li class=>
                                <a href="index.php">
                                    <i class="fa fa-home"></i><span class="nav-label">Início</span>
                                </a>
                            </li>
                            <?php
                                MenuCria('<i class="fa fa-edit"></i>Cadastros',
                                    MontaMenuPermissao('Área', 'area.php', ValidaPermissao('CAD_AREA')).
                                    MontaMenuPermissao('Campus', 'campus.php', ValidaPermissao('CAD_CAMPUS')).
                                    MontaMenuPermissao('Grupo', 'grupo.php', ValidaPermissao('CAD_GRUPO')).
                                    MontaMenuPermissao('Perfil', 'perfil.php', ValidaPermissao('CAD_PERFIL')).
                                    MontaMenuPermissao('Posto Área', 'postoarea.php', ValidaPermissao('CAD_POSTOAREA')).
                                    MontaMenuPermissao('Posto', 'posto.php', ValidaPermissao('CAD_POSTO')).
                                    MontaMenuPermissao('Tipo Ocorrência', 'tipoocorrencia.php', ValidaPermissao('CAD_TIPOOCORRENCIA')).
                                    MontaMenuPermissao('Usuário', 'usuario.php', ValidaPermissao('CAD_USUARIO')).
                                    MontaMenuPermissao('Vigilante', 'vigilante.php', ValidaPermissao('CAD_VIGILANTE'))
                                );
                                MenuCria('<i class="fa fa-wrench"></i>Tarefas',
                                MontaMenuPermissao('Manutenção de Ocorrências', 'relmanutencao.php', ValidaPermissao('TAR_MANUTENCAO')).
                                    MontaMenuPermissao('Ocorrências', 'index.php', ValidaPermissao('CAD_OCORRENCIA'))
                                );
                                MenuCria('<i class="fa fa-search"></i>Consultas',
                                    MontaMenuPermissao('Relatório Gerencial', 'relatorio.php', ValidaPermissao('REL_GERENCIAL'))
                                );
                            ?>
                        </ul>
                    </div>
                </nav>

                <div id="page-wrapper" class="gray-bg">
                    <div class="row border-bottom">
                        <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                            <div class="navbar-header">
                                <a class="navbar-minimalize" title="Minimizar/maximar menu" href="#">
                                    <span class="minimalize-style"></span>
                                </a>
                            </div>
                            <ul class="nav navbar-top-links navbar-right">
                                <li>
                                    <span class="m-r-sm text-muted welcome-message">.</span>
                                </li>
                                <li>
                                    <a href="login.php?logout=true" title="Sair">
                                        <i class="fa fa-sign-out"></i>&nbsp;Sair
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
<?php 