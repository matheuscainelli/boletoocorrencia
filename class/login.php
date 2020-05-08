<?php
require 'class/html.php';
require 'config/config.php';
require 'class/database.php';
require 'class/session.php';

class Login {
    private $usuario;
    private $idusuario;

    function __construct() {
        $this->SetJS('js/jquery-3.4.1.min');
        $this->SetJS('js/bootstrap.min');
        $this->SetCSS('css/bootstrap');
        $this->SetCSS('font-awesome/css/font-awesome');
        $this->setCSS('css/estilos');
        $this->SetCSS('css/style');
        $this->SetCSS('css/login');
    }

    protected function SetCSS($dsPath, $arrAttr = []) {
        $this->arrCSS[] = ["$dsPath.css?t=".filemtime("$dsPath.css"), $arrAttr];
    }

    protected function SetJS($dsPath, $arrAttr = []) {
        $this->arrJS[] = ["$dsPath.js?t=".filemtime("$dsPath.js"), $arrAttr];
    }

    public function View() {
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <title><?= nmSistema; ?></title>
                <?php foreach ($this->arrCSS as $arr): ?>
                    <link rel="stylesheet" href="<?= $arr[0]; ?>" <?= !empty($arr[1]) ? HTML::MontaAttrHtml($arr[1]) : null; ?>/>
                <?php endforeach; ?>
                <?php foreach ($this->arrJS as $arr): ?>
                    <script src="<?= $arr[0]; ?>" <?= !empty($arr[1]) ? HTML::MontaAttrHtml($arr[1]) : null; ?>></script>
                <?php endforeach; ?>
            </head>
            <body>
                <div id="content" class="animated fadeInDown">
                    <div id="divDescricao" class="col-md-7">
                        <div style='margin-left: 10px'>
                            <h3 class="logo-name">Bem-vindo ao Sistema<br/>
                                <span><?= nmSistema; ?></span>
                            </h3>
                            <h3 class="logo-descricao"></h3>
                            <span style='font-size: 19px'>Sistema de Gerenciamento de Ocorrências UPF</span>
                        </div>
                    </div>
                    <div id="divLogin" class="col-md-5">
                        <div>
                            <h3>Logar no sistema</h3>
                            <form id="formLogin" class="inline-content" action='#' method="POST" class="m-t">
                                <div class="inline-body">
                                    <?= HTML::AddInput('text', 'login', null, ['class'=>'form-control input-sm m-b', 'placeholder'=>'Usuário', 'autofocus'=>true, 'required'=>true]); ?>
                                    <?= HTML::AddInput('password', 'password', null, ['class'=>'form-control input-sm m-b', 'placeholder'=>'Senha', 'required'=>true]); ?>
                                </div>
                                <div class="inline-footer end">
                                    <?= HTML::AddButton('submit', 'btnSubmit', 'Entrar', ['class'=>'btn btn-primary btn-sm']); ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </body>
        </html>
        <?php
    }

    private function ConfiguraSession(){
        Session::Start();
        Session::Set('IDUSUARIO', $this->idusuario);
        Session::Set('NMUSUARIO', $this->usuario);
    }

    public function Logout() {
        Session::Destroy();
    }

    public function Submit() {
        Login::view();

        if (isset($_POST['btnSubmit'])) {
            $user = $_POST['login'];
            $pass = $_POST['password'];

            Database::ConnectaBD();

            $arrBinds = array(':SGLOGIN'=>array($user, 'PARAM_STR'),
                              ':SENHA'=>array($pass, 'PARAM_STR'));
            $sql = "SELECT pa.IDUSUARIO, pa.NMUSUARIO
                    FROM usuario pa
                    WHERE pa.SGLOGIN = :SGLOGIN  AND pa.DSSENHA = :SENHA";
            $result = Database::ExecutaSQLDados($sql, $arrBinds);

            if (array_key_exists(0, $result)) {
                $this->usuario = $result[0]['NMUSUARIO'];
                $this->idusuario = $result[0]['IDUSUARIO'];

                $this->ConfiguraSession();

                header('location: index.php');
            } else {
                echo HTML::AddAlerta('warning', 'Atenção! Usuário não cadastrado');
            }
        }
    }
}