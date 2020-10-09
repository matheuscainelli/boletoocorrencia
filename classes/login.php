<?php

require 'classes/html.php';
require 'config/config.php';
require 'classes/database.php';
require 'classes/session.php';

class Login
{
    private $arrDadosUsuario = [];

    /* Declaração variaveis para os testes  */
    const BASE_URL = 'http://localhost/boletoocorrencia';



    function __construct()
    {
        Database::ConnectaBD();
        $this->SetJS('js/jquery-3.4.1.min');
        $this->SetJS('js/bootstrap.min');
        $this->SetCSS('css/bootstrap');
        $this->SetCSS('font-awesome/css/font-awesome');
        $this->setCSS('css/estilos');
        $this->SetCSS('css/style');
        $this->SetCSS('css/login');
    }

    protected function SetCSS($dsPath, $arrAttr = [])
    {
        $this->arrCSS[] = ["$dsPath.css?t=" . filemtime("$dsPath.css"), $arrAttr];
    }
    /*deleta */
    public function isvalidado($val)
    {
        return true;
    }

    protected function SetJS($dsPath, $arrAttr = [])
    {
        $this->arrJS[] = ["$dsPath.js?t=" . filemtime("$dsPath.js"), $arrAttr];
    }

    private function View()
    {
?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <link rel="icon" type="imagem/png" href="img/icon.png" />
            <title><?= nmSistema; ?></title>
            <?php foreach ($this->arrCSS as $arr) : ?>
                <link rel="stylesheet" href="<?= $arr[0]; ?>" <?= !empty($arr[1]) ? HTML::MontaAttrHtml($arr[1]) : null; ?> />
            <?php endforeach; ?>
            <?php foreach ($this->arrJS as $arr) : ?>
                <script src="<?= $arr[0]; ?>" <?= !empty($arr[1]) ? HTML::MontaAttrHtml($arr[1]) : null; ?>></script>
            <?php endforeach; ?>
        </head>

        <body>
            <div id="content" class="animated fadeInDown">
                <div id="divDescricao" class="col-md-7">
                    <div style='margin-left: 10px'>
                        <h3 class="logo-name">Bem-vindo ao Sistema<br />
                            <span><?= nmSistema; ?></span>
                        </h3>
                        <h3 class="logo-descricao"></h3>
                        <span style='font-size: 19px'>Sistema de Gerenciamento de Ocorrências UPF</span>
                    </div>
                </div>
                <div id="divLogin" class="col-md-5">
                    <div>
                        <h3>Logar no sistema</h3>
                        <form id="formLogin" class="inline-content" action='' method="POST" class="m-t">
                            <div class="inline-body">
                                <?= HTML::AddInput('text', 'login', null, ['class' => 'form-control input-sm m-b', 'placeholder' => 'Usuário', 'autofocus' => true, 'required' => true]); ?>
                                <?= HTML::AddInput('password', 'password', null, ['class' => 'form-control input-sm m-b', 'placeholder' => 'Senha', 'required' => true]); ?>
                            </div>
                            <div class="inline-footer end">
                                <a class="btn btn-primary btn-outline" style='margin-right: 10px' href="alterasenha.php">Altera Senha</a>
                                <?= HTML::AddButton('submit', 'btnSubmit', 'Entrar', ['class' => 'btn btn-primary btn-sm']); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </body>

        </html>
    <?php
    }

    private function ConfiguraSession()
    {
        session_start();
        foreach ($this->arrDadosUsuario as $key => $val) {
            Session::Set($key, $val);
        }
    }

    public function Logout()
    {
        Session::Destroy();
    }

    public function AltereSenha()
    {
    ?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <link rel="icon" type="imagem/png" href="img/icon.png" />
            <title><?= nmSistema; ?></title>
            <?php foreach ($this->arrCSS as $arr) : ?>
                <link rel="stylesheet" href="<?= $arr[0]; ?>" <?= !empty($arr[1]) ? HTML::MontaAttrHtml($arr[1]) : null; ?> />
            <?php endforeach; ?>
            <?php foreach ($this->arrJS as $arr) : ?>
                <script src="<?= $arr[0]; ?>" <?= !empty($arr[1]) ? HTML::MontaAttrHtml($arr[1]) : null; ?>></script>
            <?php endforeach; ?>
        </head>

        <body>
            <div id="content" class="animated fadeInDown">
                <div id="divDescricao" class="col-md-7">
                    <div style='margin-left: 10px'>
                        <h3 class="logo-name">Bem-vindo ao Sistema<br />
                            <span><?= nmSistema; ?></span>
                        </h3>
                        <h3 class="logo-descricao"></h3>
                        <span style='font-size: 19px'>Olá, <b><?= Session::Get('NMUSUARIO') ?></b>. Como este é seu primeiro acesso, é necessário redefinir sua senha.</span>
                        <br><span style='font-size: 19px'>Para prosseguir, preencha os campos ao lado!!!</span>
                    </div>
                </div>
                <div id="divLogin" class="col-md-5">
                    <div class='alterasenha'>
                        <h3>Alterar Senha</h3>
                        <form id="formLogin" class="inline-content" action='' method="POST" class="m-t">
                            <div class="inline-body">
                                <?= HTML::AddInput('text', 'login', null, ['class' => 'form-control input-sm m-b', 'placeholder' => 'Usuário', 'autofocus' => true, 'required' => true]); ?>
                                <?= HTML::AddInput('password', 'password', null, ['class' => 'form-control input-sm m-b', 'placeholder' => 'Senha Atual', 'required' => true]); ?>
                                <?= HTML::AddInput('password', 'passwordnew', null, ['class' => 'form-control input-sm m-b', 'placeholder' => 'Nova Senha', 'required' => true]); ?>
                            </div>
                            <div class="inline-footer end">
                                <a class="btn btn-primary btn-outline" style='margin-right: 10px' href='login.php'>Voltar</a>
                                <?= HTML::AddButton('submit', 'btnAlteraSenha', 'Alterar', ['class' => 'btn btn-primary btn-sm']); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </body>

        </html>
<?php

        if (isset($_POST['btnAlteraSenha'])) {

            if ($this->alteraSenhaDB()) {
                echo HTML::AddAlerta('warning', 'Atenção! Senha Atual incorreta!!!');
            } else {
                header('location: login.php');
                exit;
            }
        }
    }

    public function Submit()
    {
        if (array_key_exists('logout', $_GET)) {
            $this->Logout();
            header('location: login.php');
            exit;
        }

        if (array_key_exists('SGUSUARIO', Session::GetAll())) {
            header('location: index.php');
            exit;
        }
        $this->View();

        if (isset($_POST['btnSubmit'])) {

            if ($this->login()) {
                $this->arrDadosUsuario = $this->arrDadosUsuario[0];

                $this->ConfiguraSession();

                if ($this->arrDadosUsuario['FLPRIMEIROACESSO'] === 'S') {
                    header('location: alterasenha.php');
                    exit;
                }

                header('location: index.php');
                exit;
            } else {
                echo HTML::AddAlerta('warning', 'Atenção! Usuário não cadastrado');
            }
        }
    }

    public function login()
    {
        $user = $_POST['login'];
        $pass = $_POST['password'];
        $arrBinds = array(
            ':SGUSUARIO' => array($user, 'PARAM_STR'),
            ':PWUSUARIO' => array($pass, 'PARAM_STR')
        );

        $sql = "SELECT pa.IDUSUARIO, pa.NMUSUARIO, pa.SGUSUARIO, pa.IDPERFIL, pa.IDCAMPUS, pa.FLPRIMEIROACESSO
                    FROM usuario pa
                    WHERE pa.SGUSUARIO = :SGUSUARIO  AND pa.PWUSUARIO = :PWUSUARIO AND pa.FLATIVO = 'S'";
        $this->arrDadosUsuario = Database::ExecutaSQLDados($sql, $arrBinds);

        if (array_key_exists(0, $this->arrDadosUsuario)) {
            return true;
        } else {
            return false;
        }
    }

    public function alteraSenhaDB()
    {
        $user = $_POST['login'];
        $pass = $_POST['password'];
        $passNew = $_POST['passwordnew'];

        if( $user=='' or $pass =='' or $passNew ==''){
            return false;
        }

        $arrBinds = array(
            ':SGUSUARIO' => array($user, 'PARAM_STR'),
            ':PWUSUARIO' => array($pass, 'PARAM_STR')
        );

        $sql = "SELECT pa.PWUSUARIO
                    FROM usuario pa
                    WHERE pa.SGUSUARIO = :SGUSUARIO  AND pa.PWUSUARIO = :PWUSUARIO AND pa.FLATIVO = 'S'";
        $senha = Database::ExecutaSQLDados($sql, $arrBinds);
        $senha = $senha[0]['PWUSUARIO'] ?? NULL;

        if (md5($pass) <> $senha) {
            return false;
        } else {
            Database::Edita('usuario', array('PWUSUARIO' => $passNew, 'FLPRIMEIROACESSO' => 'N'), array('SGUSUARIO' => $user));
            return true;
        }
    }
}
