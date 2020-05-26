<?php

class Filter {
    public $permissao;
    private $legend;
    public $sql;
    public $arrDados = [];
    protected $GetDados = [];
    protected $arrCamposSQL = [];
    private $arrBinds = [];
    protected $arrForm = [];
    protected $arrTable = [];

    function __construct($permissao, $legend) {
        if (!$permissao)
            die("Sem permissão");

        Database::ConnectaBD();
        $this->permissao = $permissao;
        $this->legend = $legend;
        $this->setAction($_SERVER['PHP_SELF']);
    }

    private function setAction($action) {
        $this->action = $action;
    }

    public function AddInput($type, $name, $caption = null, $attrInput = [], $attrDiv = [], $attrLabel = []) {
        if (!isset($attrInput['value'])) {
            $attrInput['value'] = $this->post[$name] ?? null;
        }

        $this->arrForm[] = [
            'method'=>'AddInput',
            'arg'=>[$type, $name, $caption, $attrInput, $attrDiv, $attrLabel]
        ];
    }

    public function AddSelect($name, $caption = null, $arrOption = [], $attrSelect = [], $attrDiv = [], $attrLabel = []) {
        $selected = $this->post[$name] ?? null;

        $this->arrForm[] = [
            'method'=>'AddSelect',
            'arg'=>[$name, $caption, $selected, $arrOption, $attrSelect, $attrDiv, $attrLabel]
        ];
    }

    private function GeraPDF() {

    }

    public function Filtros() {
        ?>
        <div class="ibox">
            <div class="ibox-content">
                <div class="ibox-header">
                    <span class="ibox-title"><?= $this->legend; ?></span>
                </div>
                <div class="ibox-body">
                    <form name="form" id="formFilter" class="inline-content" action="<?= $this->action?>" method="POST">
                        <div class="inline-body">
                            <?php
                            foreach ($this->arrForm as $arr):
                                echo forward_static_call_array(["HTML", $arr['method']], $arr['arg']);
                            endforeach;
                            ?>
                            <div class="ibox-buttons">
                                <?= HTML::AddButton('submit', 'btnSubmit', "Filtrar", ['class'=>'btn btn-primary']); ?>
                                <?= HTML::AddButton('submit', 'btnPDF', "Exportar PDF", ['class'=>'btn btn-primary']); ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function Show() {
        $this->Filtros();

        if (array_key_exists('btnPDF', $_POST)) {
            $this->GeraPDF();
        }
     }
}

?>