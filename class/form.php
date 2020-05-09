<?php
class Form {
    private $permissao;
    private $action;
    private $acao;
    private $id;
    private $sql;
    private $chave;
    private $row;

    function __construct($permissao, $legend) {
        $this->permissao = $permissao;
        $this->legend = $legend;
        $this->method = 'POST';
        $this->setAction($_SERVER['PHP_SELF']);
        // $this->acao = $this->VerificaParam('acao');
        // $this->id = $this->VerificaParam('id');
    }

    public function setAction($action) {
        $this->action = $action;
        $this->redirectSubmit = $action;
    }

    private function VerificaParam($param) {
        $vlparam = $_POST[$param] <> NULL ? ($_POST[$param]) : ($_GET[$param]);

        return ($vlparam);
    }

    public function SetDB($sql, $chave) {
        $this->sql = $sql;
        $this->chave = $chave;
    }

    private function GetDados() {
        Database::ConnectaBD();
        $this->row = Database::ExecutaSqlDados($this->sql, array());

        $this->row = $this->row[0];
    }

    public function Show() {
        $lnkInclui = "<a href='$this->action' id='tableInsert' class='btn btn-primary btn-sm' style='margin-top:10px !important;margin-bottom:10px !important;margin-right:2px !important' title='Incluir'><i class=''></i>&nbsp Novo registro</a>";
        $row = $this->GetDados();
        ?>
        <div class='wrapper wrapper-content animated fadeInRight'>
            <div class='ibox float-e-margins' style='margin: 2%'>
                <div class='ibox-title' style='padding: 3px 15px 3px'>
                    <h5 style='padding-top: 17px !important' id='descricao-tabela'><?= $this->legend ?></h5>
                    <span style='float:right;align: right'><?=  $lnkInclui ?></span>
                </div>
                <div class='ibox-content'>
                    <div class='table-responsive'>
                        <table id='registroscadastrados' class='registros'>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
}
?>