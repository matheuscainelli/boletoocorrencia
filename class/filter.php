<?php

use Symfony\Component\Debug\Debug;

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
    protected $post = [];
    protected $tipoRelatorio = NULL;
    private $arrGroup = [];

    function __construct($permissao, $legend) {
        if (!$permissao) {
            $this->MostraMensagemPermissao();
        }

        Database::ConnectaBD();
        $this->permissao = $permissao;
        $this->legend = $legend;
        $this->setAction($_SERVER['PHP_SELF']);
        $this->post = $_POST;
        $this->tipoRelatorio = $this->post['TPRELATORIO'] ?? NULL;
    }

    private function MostraMensagemPermissao() {
        $str = '';
        $str .= "<div class='wrapper wrapper-content'>";
            $str .= "<div class='error-permissao middle-box text-center animated fadeInRightBig'>";
                $str .= "<h3 class='font-bold'>Acesso negado</h3>";
                $str .= "<div class='error-desc'>";
                    $str .= "Voce não possui permissão para acessar essa página.";
                    $str .= "<br><a href='index.php' class='btn btn-primary m-t'>Voltar</a>";
                $str .= "</div>";
            $str .= "</div>";
        $str .= "</div>";
 
        die($str);
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
    
    public function AddTable($name, $caption, $attrTh = [], $attrTd = []) {
        $this->arrTable[] = [
            'name'=>$name,
            'arg'=>[
                'caption'=>$caption,
                'attrTh'=>$attrTh,
                'attrTd'=>$attrTd
            ]
        ];
    }

    private function AjustaFieldGroupBySQL(&$arrGroupBy) {
        $arrGroupBy = [];

        if (strpos($this->sql, '{{GROUP BY}}') !== false) {
            foreach ($this->arrGroup as $nmCampo => $arr) {
                if (!empty($this->post["FL$nmCampo"])) {
                    $prefix = $arr['prefix'] ?? null;

                    $arrGroupBy[] = isset($prefix) ? "$prefix.$nmCampo" : $nmCampo;
                }
            }
        }
    }

    private function AjustaWhereHavingSQL(&$arrWhere, &$arrHaving) {
        $arrWhere = [];
        $arrHaving = [];

        if (strpos($this->sql, '{{WHERE}}') !== false ||
            strpos($this->sql, '{{HAVING}}') !== false) {
            $arrBinds = [];
            $arrPadrao = [
                'prefix'=>'pa',
                'operator'=>'=',
                'having'=>false
            ];

            foreach ($this->arrCamposSQL as $key => $value) {
                if (is_numeric($key)) {
                    $nmCampo = $value;
                    $arr = $arrPadrao;
                } else {
                    $nmCampo = $key;
                    $arr = array_merge($arrPadrao, $value);
                }

                if (!empty($this->post[$nmCampo])) {
                    $field = $arr['field'] ?? $nmCampo;
                    $prefix = $arr['prefix'];
                    $operator = $arr['operator'];

                    switch ($operator) {
                        case '=':
                        case '!=':
                        case '>=':
                        case '<=':
                            $comparacao = "$prefix.$field $operator :$nmCampo";

                            $arrBinds[":$nmCampo"] = $this->post[$nmCampo];
                            break;
                        case 'in':
                        case 'not in':
                            break;
                        case 'between':
                            break;
                        case 'like':
                            break;
                    }

                    if (!$arr['having']) {
                        $arrWhere[] = $comparacao;
                    } else {
                        $arrHaving[] = $comparacao;
                    }
                }
            }

            if (!empty($arrBinds)) {
                $this->SetArrBinds($arrBinds);
            }
        }
    }

    private function AjustaLimitOrderBySQL(&$arrOrderBy, &$arrLimit) {
        $arrOrderBy = [];
        $arrLimit = [];

        if (strpos($this->sql, '{{LIMIT}}') !== false && strpos($this->sql, '{{ORDER BY}}') !== false) {
            if (isset($this->post['order'])) {
                $nmCampo = $this->arrTable[$this->post['order'][0]['column']];
                $dir = strtoupper($this->post['order'][0]['dir']);

                $arrOrderBy[] = "$nmCampo $dir";
            }

            if (isset($this->post['start']) && isset($this->post['length']) && $this->post['length'] != '-1') {
                $arrLimit[] = "LIMIT ".$this->post['start'].", ".$this->post['length'];
            }
        }
    }

    private function AjustaSQL() {
        $where = null;
        $having = null;
        $groupBy = null;
        $orderBy = null;
        $limit = null;

        $this->AjustaFieldGroupBySQL($arrGroupBy);
        $this->AjustaWhereHavingSQL($arrWhere, $arrHaving);
        $this->AjustaLimitOrderBySQL($arrOrderBy, $arrLimit);

        if (!empty($arrWhere)) {
            $where = 'AND '.implode(' AND ', $arrWhere);
        }

        if (!empty($arrHaving)) {
            $having = 'AND '.implode(' AND ', $arrHaving);
        }

        if (!empty($arrGroupBy)) {
            $groupBy = ', '.implode(', ', $arrGroupBy);
        }

        if (!empty($arrOrderBy)) {
            $orderBy = ', '.implode(', ', $arrOrderBy);
        }

        if (!empty($arrLimit)) {
            $limit = implode('', $arrLimit);
        }

        return str_replace(['{{WHERE}}', '{{HAVING}}', '{{GROUP BY}}', '{{ORDER BY}}', '{{LIMIT}}'], [$where, $having, $groupBy, $orderBy, $limit], $this->sql);
    }

    public function SetSQL($sql) {
        $this->sql = $sql;
    }

    public function SetArrBinds($arrBinds) {
        $this->arrBinds = array_merge($this->arrBinds, $arrBinds);
    }

    public function GetSQL() {
        return Database::Debug($this->AjustaSQL(), $this->arrBinds);
    }

    public function GetDados() {
        return Database::ExecutaSqlDados($this->AjustaSQL(), $this->arrBinds);
    }

    public function GetCountDados() {
        $sql = "SELECT FOUND_ROWS() QTDADOS";
        $result = Database::ExecutaSqlDados($sql);

        return $result[0]['QTDADOS'];
    }

    private function Relatorio() {
        switch($this->tipoRelatorio) {
            case 'A':
                $this->RelatorioAreasAbertas();
            break;
            case 'D':
                $this->RelatorioDiario();
            break;
            case 'M':
                $this->RelatorioMensal();
            break;
        }
    }

    private function RelatorioAreasAbertas() {
        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));

        $sql = "SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA, CONCAT(po.NMPOSTO, ' - ', a.NMAREA) NMPOSTOAREA, tp.NMOCORRENCIA
                FROM ocorrencia pa
                LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                JOIN postoarea pta on pta.IDPOSTOAREA = pa.IDPOSTOAREA
                JOIN posto po on po.IDPOSTO = pta.IDPOSTO
                JOIN area a on a.IDAREA = pta.IDAREA 
                WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                ORDER BY NMPOSTOAREA, NRDIA";
        $arrDados = Database::MontaArrayNCampos($sql, $arrBinds, 'NMOCORRENCIA', array('NRDIA'=>'TOTAL'));

        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrDados as $nmOcorrencia => $dia) {
                if (!array_key_exists($i, $dia)) {
                    $arrDados[$nmOcorrencia][$i] = 0;
                    ksort($arrDados[$nmOcorrencia]);
                }
            }
        }
        printr($arrDados);
        ?>
        <div class="inline-content ibox">
            <div class="inline-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; width:16%">Eventos</th>
                            <th colspan="31" style="text-align: center; width: 62%;">Dias</th>
                            <th colspan="1" style="width: 5%;"></th>
                        </tr>
                    </thead>
                </table>
                <table  id="tableFilter" class="table table-striped table-bordered table-hover">
                    <thead>
                        <th colspan="1" style="width:8.8%">Prédio</th>
                        <th colspan="1" style="width:8.5%">Ocorrência</th>
                        <?php
                            for ($i = 1; $i <= 31; $i++): ?>
                               <th colspan='31' style='width: 2%;'><?=$i; ?></th>
                            <?php
                            endfor;
                        ?>
                        <th colspan="1" style="width: 5.4%;">Sub-Total</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($arrDados as $ocorrencia => $total): ?>
                            <tr>
                                <td><?= $ocorrencia?></td>
                                <?php
                                    foreach ($total as $dia => $vlOcorrencia): ?>
                                        <td colspan="31" ><?= $vlOcorrencia;?></td>
                                    <?php
                                        endforeach;
                                    ?>
                                        <td><?= array_sum($total)?></td>
                                    <?php
                                endforeach;
                            ?>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; width:16%">Sub-Total por Dia</th>
                            <th colspan="31" style="text-align: center; width: 62%;"></th>
                            <th colspan="1" style="width: 5%;"></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <?php
    }

    private function RelatorioDiario() {

    }

    private function RelatorioMensal() {
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
        } else if (array_key_exists('btnSubmit', $_POST)) {
            $this->Relatorio();
        }
     }
}

?>