<?php

use function PHPSTORM_META\exitPoint;

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
    public $exportacao = true;

    function __construct($permissao, $legend, $sql = NULL) {
        if (!$permissao) {
            $this->MostraMensagemPermissao();
        }

        Database::ConnectaBD();
        $this->permissao = $permissao;
        $this->legend = $legend;
        $this->setAction($_SERVER['PHP_SELF']);
        $this->post = $_POST;
        $this->tipoRelatorio = $this->post['TPRELATORIO'] ?? NULL;
        $this->sql = $sql;
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
    
    public function SetCamposSQL($arrCamposSQL) {
        $this->arrCamposSQL = $arrCamposSQL;
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

    public function HabilitaExportacao($exportacao) {
        $this->exportacao = $exportacao;
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

        $sql = "SELECT COALESCE(sub.TOTAL, 0) AS TOTAL, COALESCE(sub.NRDIA, 1) AS NRDIA, t.NMOCORRENCIA 
                FROM tipoocorrencia t
                LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA,
                                tP.IDTIPOOCORRENCIA
                            FROM ocorrencia pa
                            LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                            WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                            GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                            ORDER BY NRDIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                WHERE t.FLAREAABERTA = 'S'";
        $arrDados = Database::MontaArrayChaveComposta($sql, $arrBinds, 'NMOCORRENCIA', 'NRDIA', array('TOTAL'));
        $arrTotal = [];

        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrDados as $nmOcorrencia => $dia) {
                $arrTotal[$i] = 0;
                if (!array_key_exists($i, $dia)) {
                    $arrDados[$nmOcorrencia][$i]['TOTAL'] = 0;
                    ksort($arrDados[$nmOcorrencia]);
                }
            }
        }

        ?>

        <div class='inline-content ibox'>
            <div class='inline-body table-responsive'>
                <table class='table table-striped table-bordered table-hover'>
                    <thead>
                        <tr>
                            <th colspan='1' style='text-align: center; width:16%'>Eventos</th>
                            <th colspan='31' style='text-align: center; width: 62%;'>Dias</th>
                            <th colspan='1' style='width: 5%;'></th>
                        </tr>
                    </thead>
                </table>
                <table  id='tableFilter1' class='table table-striped table-bordered table-hover'>
                    <thead>
                        <tr>
                            <th colspan='1' style='width:17.8%'>Ocorrência</th>
                            <?php
                                for ($i = 1; $i <= 31; $i++): ?>
                                <th colspan='31' style='width: 2%;'><?=$i; ?></th>
                                <?php
                                endfor;
                            ?>
                            <th colspan='1' style='width: 5.4%;'>Sub-Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($arrDados as $ocorrencia => $total): 
                            $ocorrencias = 0;?>
                            <tr>
                                <td><?= $ocorrencia?></td>
                                <?php
                                    foreach ($total as $dia => $vlOcorrencia):
                                        $ocorrencias += $vlOcorrencia['TOTAL']; 
                                        $arrTotal[$dia] += $vlOcorrencia['TOTAL']; 
                                    ?>
                                    <td colspan='31' ><?= $vlOcorrencia['TOTAL'];?></td>
                                    <?php endforeach; ?>
                                    <td><?= $ocorrencias ?></td>
                                    <?php
                                endforeach;
                            ?>
                        </tr>

                        <tr style='font-weight: bold'>
                            <td colspan='1' style='width:15.8%'>Sub-Total por Dia</td>
                            <?php
                                foreach ($arrTotal as $dia => $totalDia): ?>
                                    <td colspan='31' style='width: 2%;'><?= $totalDia ?></td>
                            <?php endforeach; ?>
                            <td colspan='1' style='width: 5%'><?= array_sum($arrTotal); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function RelatorioAreasAbertasPDF(&$titulo) {
        $titulo = "Áreas Abertas ".$this->post['DAMESREFERENCIA'];
        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));

        $sql = "SELECT COALESCE(sub.TOTAL, 0) AS TOTAL, COALESCE(sub.NRDIA, 1) AS NRDIA, t.NMOCORRENCIA 
                FROM tipoocorrencia t
                LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA,
                                tP.IDTIPOOCORRENCIA 
                            FROM ocorrencia pa
                            LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                            WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                            GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                            ORDER BY NRDIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                WHERE t.FLAREAABERTA = 'S'";
        $arrDados = Database::MontaArrayChaveComposta($sql, $arrBinds, 'NMOCORRENCIA', 'NRDIA', array('TOTAL'));
        $arrTotal = [];

        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrDados as $nmOcorrencia => $dia) {
                $arrTotal[$i] = 0;
                if (!array_key_exists($i, $dia)) {
                    $arrDados[$nmOcorrencia][$i]['TOTAL'] = 0;
                    ksort($arrDados[$nmOcorrencia]);
                }
            }
        }

        $str = "";
        $str .= "<div>";
            $str .= "<table>";
                $str .= "<tr>";
                    $str .= "<td><img src='./img/logo.png' style='width: 120px;'></td>";
                    $str .= "<td><p>Universidade de Passo Fundo<br>Divisão de Serviços Gerais<br>Segurança Patrimonial</p><td>";
                $str .= "</tr>";
            $str .= "</table>";
        
            $str .= "<p style='text-align: center;'>Controle Diário de Ocorrências de Risco Área Aberta<br>".$this->post['DAMESREFERENCIA']."</p>";

            $str .= "<div>";
                $str .= "<table border='1' cellpading='0' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
                    $str .= "<thead>";
                        $str .= "<tr>";
                            $str .= "<th colspan='1' style='text-align: center; width:110px'>Eventos</th>";
                            $str .= "<th colspan='3' style='text-align: center; width: 2px;'>Dias</th>";
                            $str .= "<th colspan='1' style='width: 50px;'></th>";
                        $str .= "</tr>";
                    $str .= "</thead>";
               $str .= " </table>";
                $str .= "<table border='1' cellpading='0' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
                    $str .= "<thead>";
                            $str .= "<tr>";
                                $str .= "<th colspan='1' style='text-align: center; width:330px; font-size: 30px'>Ocorrência</th>";
                                    for ($i = 1; $i <= 31; $i++):
                                        $str .= "<th colspan='1' style='width: 30px; font-size: 30px; text-align: right;'>$i</th>";
                                    endfor;
                                $str .= "<th colspan='1' style='width: 30px; font-size: 30px'>Sub-Total</th>";
                        $str .= "</tr>";
                    $str .= "</thead>";
                    $str .= "<tbody>";
                        foreach ($arrDados as $ocorrencia => $total):
                            $ocorrencias = 0;
                            $str .= "<tr>";
                                $str .= "<td style='width: 320px; font-size: 30px'>$ocorrencia</td>";
                                foreach ($total as $dia => $vlOcorrencia):
                                    $ocorrencias += $vlOcorrencia['TOTAL']; 
                                    $arrTotal[$dia] += $vlOcorrencia['TOTAL']; 
                                    $str .= "<td colspan='1' style='width: 2px;font-size: 30px; text-align: right;'>".$vlOcorrencia['TOTAL']."</td>";
                                endforeach;
                                $str .= "<td style='width: 30px;font-size: 30px; text-align: right;'>$ocorrencias</td>";
                            $str .= "</tr>";
                        endforeach;
                        $str .= "<tr>";
                            $str .= "<td colspan='1' style='width: 110px; font-size: 30px; font-weight bold'>Total Dia</td>";
                                foreach ($arrTotal as $dia => $totalDia):
                                    $str .=  "<td colspan='1' style='width: 50px; font-size: 30px; text-align: right;'>$totalDia</td>";
                                endforeach;
                            $str .= "<td colspan='1' style='width: 30px; font-size: 30px; text-align: right;'>".array_sum($arrTotal)."</td>";
                        $str .= "</tr>";
                    $str .= "</tbody>";
                $str .= "</table>";
            $str .= "</div>";
        $str .= "</div>";

        return $str;
    }

    private function RelatorioAreasAbertasCSV() {
        $titulo = "Áreas Abertas ".$this->post['DAMESREFERENCIA'];
        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));

        $sql = "SELECT COALESCE(sub.TOTAL, 0) AS TOTAL, COALESCE(sub.NRDIA, 1) AS NRDIA, t.NMOCORRENCIA 
                FROM tipoocorrencia t
                LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA,
                                tP.IDTIPOOCORRENCIA 
                            FROM ocorrencia pa
                            LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                            WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                            GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                            ORDER BY NRDIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                WHERE t.FLAREAABERTA = 'S'";
        $arrDados = Database::MontaArrayChaveComposta($sql, $arrBinds, 'NMOCORRENCIA', 'NRDIA', array('TOTAL'));
        $arrTotal = [];

        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrDados as $nmOcorrencia => $dia) {
                $arrTotal[$i] = 0;
                if (!array_key_exists($i, $dia)) {
                    $arrDados[$nmOcorrencia][$i]['TOTAL'] = 0;
                    ksort($arrDados[$nmOcorrencia]);
                }
            }
        }

        ob_end_clean();
        header("content-type:application/csv;charset=UTF-8");
        header("Content-Disposition: attachment; filename=" . str_replace(" ", "_", trim(str_replace(",", " ", strtolower($titulo)))) . ".csv");
        header("Pragma: no-cache");

        echo iconv('UTF-8', 'Windows-1252', 'Ocorrência;');
        for ($i = 1; $i <= 31; $i++) {
            echo iconv('UTF-8', 'Windows-1252', $i.';');
        }
        echo iconv('UTF-8', 'Windows-1252', 'Sub-Total;');
        print "\n";

        foreach ($arrDados as $ocorrencia => $total):
            $ocorrencias = 0;
            echo iconv('UTF-8', 'Windows-1252', $ocorrencia.';');
            foreach ($total as $dia => $vlOcorrencia):
                $ocorrencias += $vlOcorrencia['TOTAL']; 
                $arrTotal[$dia] += $vlOcorrencia['TOTAL'];
                echo iconv('UTF-8', 'Windows-1252', $vlOcorrencia['TOTAL'].';');
            endforeach;
                echo iconv('UTF-8', 'Windows-1252', $ocorrencias.';');
                print "\n";
        endforeach;

        echo iconv('UTF-8', 'Windows-1252', 'Sub-Total por Dia;');
        foreach ($arrTotal as $dia => $totalDia):
            echo iconv('UTF-8', 'Windows-1252', $totalDia.';');
        endforeach;
        echo iconv('UTF-8', 'Windows-1252', array_sum($arrTotal).';');

        die('');
    }

    private function RelatorioDiarioCSV() {
        $titulo = "Diário ".$this->post['DAMESREFERENCIA'];

        $arrBinds = array(':IDGRUPO'=>array($this->post['IDGRUPO'], 'PARAM_STR'));
        $sql = "SELECT p.IDPOSTOAREA
                FROM postoarea p 
                JOIN area a on a.IDAREA = p.IDAREA 
                WHERE a.IDGRUPO = :IDGRUPO";
        $arrGrupo = Database::ExecutaSQLDados($sql, $arrBinds);
        $total = count($arrGrupo);

        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));
        $sql = "";

        for ($i = 0; $i < $total; $i++) {
            $idarea = $arrGrupo[$i]['IDPOSTOAREA'];

            $arrBinds[':IDPOSTOAREA_'. $idarea] = array( $idarea, 'PARAM_INT');
            $sql .= "(SELECT (SELECT concat(p.NMPOSTO, ' - ', a.NMAREA) DSUNIDADE 
                              FROM postoarea pa
                              JOIN area a on a.IDAREA = pa.IDAREA 
                              JOIN posto p on p.IDPOSTO = pa.IDPOSTO
                              WHERE pa.IDPOSTOAREA = :IDPOSTOAREA_". $idarea."
                              limit 1) DSUNIDADE, t.NMOCORRENCIA, COALESCE(sub.TOTAL, 0) TOTAL, sub.NRDIA
                    FROM tipoocorrencia t
                    LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA, tP.IDTIPOOCORRENCIA 
                                FROM ocorrencia pa
                                LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                                WHERE tp.FLAREAABERTA = 'N' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                                AND pa.IDPOSTOAREA = :IDPOSTOAREA_". $idarea."
                                GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                                ORDER BY NRDIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                                where t.FLAREAABERTA  = 'N')";

            if ($i < $total - 1)
                $sql .= "\n\nUNION ALL\n\n";
        }

        $arrOcorrenciasDiarias = Database::MontaArrayChaveComposta3($sql, $arrBinds, 'DSUNIDADE', 'NMOCORRENCIA', 'NRDIA', array('TOTAL'));
        $arrTotal = array();
        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrOcorrenciasDiarias as $unidade => $arrOcorrencia) {
                foreach ($arrOcorrencia as $nmOcorrencia => $dia) {
                    $arrTotal[$i] = 0;
                    if (!array_key_exists($i, $dia)) {
                        $arrOcorrenciasDiarias[$unidade][$nmOcorrencia][$i]['TOTAL'] = 0;
                        ksort($arrOcorrenciasDiarias[$unidade][$nmOcorrencia]);
                    }
                }
            }
        }

        ob_end_clean();
        header("content-type:application/csv;charset=UTF-8");
        header("Content-Disposition: attachment; filename=" . str_replace(" ", "_", trim(str_replace(",", " ", strtolower($titulo)))) . ".csv");
        header("Pragma: no-cache");

        echo iconv('UTF-8', 'Windows-1252', 'Unidade;Ocorrência;');

        for ($i = 1; $i <= 31; $i++) {
            echo iconv('UTF-8', 'Windows-1252', $i.';');
        }
        echo iconv('UTF-8', 'Windows-1252', 'Sub-Total');
        print "\n";

        foreach ($arrOcorrenciasDiarias as $unidade => $arrDadosOcorrencia):
            echo iconv('UTF-8', 'Windows-1252', $unidade);

            foreach ($arrDadosOcorrencia as $ocorrencia => $arrDia): 
                $ocorrencias = 0;
                echo iconv('UTF-8', 'Windows-1252', ';'.$ocorrencia.';');
                foreach ($arrDia as $dia => $total): 
                    if ($dia <> NULL):
                        $ocorrencias += $total['TOTAL']; 
                        $arrTotal[$dia] += $total['TOTAL'];
                        echo iconv('UTF-8', 'Windows-1252', $total['TOTAL'].';');
                    endif;
                endforeach;
                echo iconv('UTF-8', 'Windows-1252', $ocorrencias.';');
                print "\n";
            endforeach;
        endforeach;

        echo iconv('UTF-8', 'Windows-1252', 'Sub-Total por Dia;;');
        foreach ($arrTotal as $dia => $totalDia):
            echo iconv('UTF-8', 'Windows-1252', $totalDia.';');
        endforeach;
        echo iconv('UTF-8', 'Windows-1252', array_sum($arrTotal).';');

        die('');
    }

    private function RelatorioMensalCSV() {
        $titulo = "Mensal ".$this->post['DAMESREFERENCIA'];
        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));

        $sql = "SELECT pa.IDPOSTOAREA, CONCAT(p.NMPOSTO, ' - ', a.NMAREA) as DSUNIDADE,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 23 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSPORTAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 24 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSFECHADURAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 25 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSCADEADOALTERADO,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 26 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSJANELAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 27 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSPAREDEALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 28 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSCERCAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 29 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSLUZESACESSAS
                FROM postoarea pa
                JOIN posto p ON p.IDPOSTO = pa.IDPOSTO
                JOIN area a ON a.IDAREA = pa.IDAREA
                ORDER BY pa.IDPOSTOAREA ASC";

        $arrOcorrencias = DataBase::MontaArrayNCampos($sql, $arrBinds, 'DSUNIDADE', array('DSPORTAALTERADA', 'DSFECHADURAALTERADA', 'DSCADEADOALTERADO', 'DSJANELAALTERADA', 'DSPAREDEALTERADA', 'DSCERCAALTERADA', 'DSLUZESACESSAS'));
        $arrTotal = array('DSPORTAALTERADA'=>0, 'DSFECHADURAALTERADA'=>0, 'DSCADEADOALTERADO'=>0, 'DSJANELAALTERADA'=>0, 'DSPAREDEALTERADA'=>0, 'DSCERCAALTERADA'=>0, 'DSLUZESACESSAS'=>0);

        foreach ($arrOcorrencias as $unidade => $valor) {
            $arrTotal['DSPORTAALTERADA'] += $valor['DSPORTAALTERADA'];
            $arrTotal['DSFECHADURAALTERADA'] += $valor['DSFECHADURAALTERADA'];
            $arrTotal['DSCADEADOALTERADO'] += $valor['DSCADEADOALTERADO'];
            $arrTotal['DSJANELAALTERADA'] += $valor['DSJANELAALTERADA'];
            $arrTotal['DSPAREDEALTERADA'] += $valor['DSPAREDEALTERADA'];
            $arrTotal['DSCERCAALTERADA'] += $valor['DSCERCAALTERADA'];
            $arrTotal['DSLUZESACESSAS'] += $valor['DSLUZESACESSAS'];
        }

        $subTotal = array_sum($arrTotal);
        $sql = "SELECT COALESCE(sub.TOTAL, 0) AS TOTAL, t.NMOCORRENCIA 
                FROM tipoocorrencia t
                LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL,
                                 tP.IDTIPOOCORRENCIA 
                           FROM ocorrencia pa
                           LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                           WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                           GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                WHERE t.FLAREAABERTA = 'S'";

        $arrAreaAberta = DataBase::MontaArraySelect($sql, $arrBinds, 'NMOCORRENCIA', 'TOTAL');
        $subTotalAreas = array_sum($arrAreaAberta);
        $subTotal += $subTotalAreas;

        $sql = "SELECT pa.NMOCORRENCIA
                FROM tipoocorrencia pa
                ORDER BY pa.IDTIPOOCORRENCIA";
        $arrNmOcorrencia = Database::MontaArraySelect($sql, array(), 'NMOCORRENCIA', 'NMOCORRENCIA');
    
        ob_end_clean();
        header("content-type:application/csv;charset=UTF-8");
        header("Content-Disposition: attachment; filename=" . str_replace(" ", "_", trim(str_replace(",", " ", strtolower($titulo)))) . ".csv");
        header("Pragma: no-cache");

        echo iconv('UTF-8', 'Windows-1252', 'Unidade;Total Mês;');

        foreach ($arrNmOcorrencia as $nome) {
            echo iconv('UTF-8', 'Windows-1252', $nome.';');
        }
        print "\n";

        foreach ($arrOcorrencias as $unidade => $totalOcorrencias): 
            $totalUnidade = array_sum($totalOcorrencias);
            echo iconv('UTF-8', 'Windows-1252', $unidade.';'.$totalUnidade.';');
            echo iconv('UTF-8', 'Windows-1252', $totalOcorrencias['DSPORTAALTERADA'].';'.$totalOcorrencias['DSFECHADURAALTERADA'].';');
            echo iconv('UTF-8', 'Windows-1252', $totalOcorrencias['DSCADEADOALTERADO'].';'.$totalOcorrencias['DSJANELAALTERADA'].';');
            echo iconv('UTF-8', 'Windows-1252', $totalOcorrencias['DSPAREDEALTERADA'].';'.$totalOcorrencias['DSCERCAALTERADA'].';');
            echo iconv('UTF-8', 'Windows-1252', $totalOcorrencias['DSLUZESACESSAS'].';');
            for($i = 0; $i < 22; $i++):
                echo iconv('UTF-8', 'Windows-1252', ';');
            endfor;
            print "\n";
        endforeach;

        echo iconv('UTF-8', 'Windows-1252', 'Áreas Abertas;'.$subTotalAreas.';');
        for($i = 1; $i < 8; $i++):
            echo iconv('UTF-8', 'Windows-1252', ';');
        endfor;
        foreach ($arrAreaAberta as $areas => $total):
            echo iconv('UTF-8', 'Windows-1252', $total.';');
        endforeach;

        print "\n";
        echo iconv('UTF-8', 'Windows-1252', 'Sub-Total Mês;'.$subTotal.';');
        echo iconv('UTF-8', 'Windows-1252', $arrTotal['DSPORTAALTERADA'].';'.$arrTotal['DSFECHADURAALTERADA'].';');
        echo iconv('UTF-8', 'Windows-1252', $arrTotal['DSCADEADOALTERADO'].';'.$arrTotal['DSJANELAALTERADA'].';');
        echo iconv('UTF-8', 'Windows-1252', $arrTotal['DSPAREDEALTERADA'].';'.$arrTotal['DSCERCAALTERADA'].';');
        echo iconv('UTF-8', 'Windows-1252', $arrTotal['DSLUZESACESSAS'].';');

        foreach ($arrAreaAberta as $areas => $total):
            echo iconv('UTF-8', 'Windows-1252', $total.';');
        endforeach;

        die('');
    }

    private function RelatorioDiario() {
        $arrBinds = array(':IDGRUPO'=>array($this->post['IDGRUPO'], 'PARAM_STR'));
        $sql = "SELECT p.IDPOSTOAREA
                FROM postoarea p 
                JOIN area a on a.IDAREA = p.IDAREA 
                WHERE a.IDGRUPO = :IDGRUPO";
        $arrGrupo = Database::ExecutaSQLDados($sql, $arrBinds);
        $total = count($arrGrupo);

        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));
        $sql = "";

        for ($i = 0; $i < $total; $i++) {
            $idarea = $arrGrupo[$i]['IDPOSTOAREA'];

            $arrBinds[':IDPOSTOAREA_'. $idarea] = array( $idarea, 'PARAM_INT');
            $sql .= "(SELECT (SELECT concat(p.NMPOSTO, ' - ', a.NMAREA) DSUNIDADE 
                              FROM postoarea pa
                              JOIN area a on a.IDAREA = pa.IDAREA 
                              JOIN posto p on p.IDPOSTO = pa.IDPOSTO
                              WHERE pa.IDPOSTOAREA = :IDPOSTOAREA_". $idarea."
                              limit 1) DSUNIDADE, t.NMOCORRENCIA, COALESCE(sub.TOTAL, 0) TOTAL, sub.NRDIA
                    FROM tipoocorrencia t
                    LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA, tP.IDTIPOOCORRENCIA 
                                FROM ocorrencia pa
                                LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                                WHERE tp.FLAREAABERTA = 'N' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                                AND pa.IDPOSTOAREA = :IDPOSTOAREA_". $idarea."
                                GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                                ORDER BY NRDIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                                where t.FLAREAABERTA  = 'N')";

            if ($i < $total - 1)
                $sql .= "\n\nUNION ALL\n\n";
        }
        $arrOcorrenciasDiarias = Database::MontaArrayChaveComposta3($sql, $arrBinds, 'DSUNIDADE', 'NMOCORRENCIA', 'NRDIA', array('TOTAL'));
        $arrTotal = array();

        $sql =  "SELECT (COUNT(pa.IDTIPOOCORRENCIA) + 1 )TOTAL
                 FROM tipoocorrencia pa
                 WHERE pa.FLAREAABERTA = 'N'";
        $total = Database::ExecutaSQLDados($sql, array());
        $rowSpan = $total[0]['TOTAL'];

        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrOcorrenciasDiarias as $unidade => $arrOcorrencia) {
                foreach ($arrOcorrencia as $nmOcorrencia => $dia) {
                    $arrTotal[$i] = 0;
                    if (!array_key_exists($i, $dia)) {
                        $arrOcorrenciasDiarias[$unidade][$nmOcorrencia][$i]['TOTAL'] = 0;
                        ksort($arrOcorrenciasDiarias[$unidade][$nmOcorrencia]);
                    }
                }
            }
        }
        ?>
        
        <div class='inline-content ibox'>
            <div class='inline-body table-responsive'>
                <table id='tableFilter1' class='table table-striped table-bordered table-hover'>
                    <tbody>
                        <tr style='font-weight: bold;'>
                            <td style="width:5%;">Unidade</td>
                            <td style="width:11%">Ocorrência</td>
                            <?php
                                for ($i = 1; $i <= 31; $i++): ?>
                                    <td style='width: 2%;'><?=$i; ?></td>
                                <?php
                                endfor;
                            ?>
                            <td style="width:5%">Sub-Total</td>
                        </tr>
                        <?php
                            foreach ($arrOcorrenciasDiarias as $unidade => $arrDadosOcorrencia): ?>
                                <tr>
                                    <td colspan="1" rowspan="<?=$rowSpan;?>" style="width:5%; text-align: center; vertical-align: middle;"><?= $unidade; ?></td>
                                    <?php
                                        foreach ($arrDadosOcorrencia as $ocorrencia => $arrDia): 
                                            $ocorrencias = 0;?>
                                            <tr>
                                            <td style="width:11%"><?= $ocorrencia; ?></td>
                                            <?php
                                                foreach ($arrDia as $dia => $total): 
                                                    if ($dia <> NULL):
                                                        $ocorrencias += $total['TOTAL']; 
                                                        $arrTotal[$dia] += $total['TOTAL']; ?>
                                                        <td style="width:2%"><?=$total['TOTAL']?></td>
                                                <?php
                                                endif;
                                                endforeach;
                                            ?>
                                            <td style="width:2%"><?= $ocorrencias?></td>
                                        </tr>
                                        <?php
                                        endforeach;
                                    ?>
                                </tr>
                            <?php
                            endforeach;
                            foreach ($arrTotal as $dia => $totalDia)
                                
                        ?>
                        <tr style='font-weight: bold'>
                            <td colspan='2' style='width:15.8%; text-align: center;'>Sub-Total por Dia</td>
                                <?php
                                    foreach ($arrTotal as $dia => $totalDia): ?>
                                        <td colspan='1' style='width: 2%;'><?= $totalDia ?></td>
                                <?php endforeach; ?>
                                <td colspan='1' style='width: 5%'><?= array_sum($arrTotal); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function RelatorioMensal() {
        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));

        $sql = "SELECT pa.IDPOSTOAREA, CONCAT(p.NMPOSTO, ' - ', a.NMAREA) as DSUNIDADE,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 23 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSPORTAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 24 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSFECHADURAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 25 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSCADEADOALTERADO,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 26 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSJANELAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 27 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSPAREDEALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 28 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSCERCAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 29 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSLUZESACESSAS
                FROM postoarea pa
                JOIN posto p ON p.IDPOSTO = pa.IDPOSTO
                JOIN area a ON a.IDAREA = pa.IDAREA
                ORDER BY pa.IDPOSTOAREA ASC";
        $arrOcorrencias = DataBase::MontaArrayNCampos($sql, $arrBinds, 'DSUNIDADE', array('DSPORTAALTERADA', 'DSFECHADURAALTERADA', 'DSCADEADOALTERADO', 'DSJANELAALTERADA', 'DSPAREDEALTERADA', 'DSCERCAALTERADA', 'DSLUZESACESSAS'));
        $arrTotal = array('DSPORTAALTERADA'=>0, 'DSFECHADURAALTERADA'=>0, 'DSCADEADOALTERADO'=>0, 'DSJANELAALTERADA'=>0, 'DSPAREDEALTERADA'=>0, 'DSCERCAALTERADA'=>0, 'DSLUZESACESSAS'=>0);

        foreach ($arrOcorrencias as $unidade => $valor) {
            $arrTotal['DSPORTAALTERADA'] += $valor['DSPORTAALTERADA'];
            $arrTotal['DSFECHADURAALTERADA'] += $valor['DSFECHADURAALTERADA'];
            $arrTotal['DSCADEADOALTERADO'] += $valor['DSCADEADOALTERADO'];
            $arrTotal['DSJANELAALTERADA'] += $valor['DSJANELAALTERADA'];
            $arrTotal['DSPAREDEALTERADA'] += $valor['DSPAREDEALTERADA'];
            $arrTotal['DSCERCAALTERADA'] += $valor['DSCERCAALTERADA'];
            $arrTotal['DSLUZESACESSAS'] += $valor['DSLUZESACESSAS'];
        }

        $subTotal = array_sum($arrTotal);
        $sql = "SELECT COALESCE(sub.TOTAL, 0) AS TOTAL, t.NMOCORRENCIA 
                FROM tipoocorrencia t
                LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL,
                                 tP.IDTIPOOCORRENCIA 
                           FROM ocorrencia pa
                           LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                           WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                           GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                WHERE t.FLAREAABERTA = 'S'";
        $arrAreaAberta = DataBase::MontaArraySelect($sql, $arrBinds, 'NMOCORRENCIA', 'TOTAL');
        $subTotalAreas = array_sum($arrAreaAberta);
        $subTotal += $subTotalAreas;

        ?>
        <div class='inline-content ibox'>
            <div class='inline-body table-responsive'>
                <table id='tableFilter1' class='table table-striped table-bordered table-hover'>
                    <thead>
                        <tr>
                            <th style='width: 1%'>Unidade</th>
                            <th style='width: 10px'>Total Mês</th>
                            <th style='width: 10px'>Porta(ão) Alt.</th>
                            <th style='width: 10px'>Fechadura Alt.</th>
                            <th style='width: 10px'>Cadeado Alt.</th>
                            <th style='width: 10px'>Vidraça Alt.</th>
                            <th style='width: 10px'>Telhado Alt.</th>
                            <th style='width: 10px'>Cerca/Grade Alt.</th>
                            <th style='width: 10px'>Luzes Acesas</th>

                            <th style='width: 10px'>Comp. Curioso</th>
                            <th style='width: 10px'>Acomp. Visual</th>
                            <th style='width: 10px'>Contato p/ Informação</th>
                            <th style='width: 10px'>Disparo Alarme</th>
                            <th style='width: 10px'>Averiguação</th>
                            <th style='width: 10px'>Obj. Encontado</th>
                            <th style='width: 10px'>Obj. Desaparecidos</th>
                            <th style='width: 10px'>Flagrante</th>
                            <th style='width: 10px'>Ameaça</th>
                            <th style='width: 10px'>Agressão</th>
                            <th style='width: 10px'>Apoio</th>
                            <th style='width: 10px'>Acidente</th>
                            <th style='width: 10px'>Primeiros Socorros</th>
                            <th style='width: 10px'>Transp. Emerg. Int</th>
                            <th style='width: 10px'>Transp. Emerg. Ext</th>
                            <th style='width: 10px'>Carga/Descarga</th>
                            <th style='width: 10px'>Carga de Risco</th>
                            <th style='width: 10px'>Veículo Oficial</th>
                            <th style='width: 10px'>Veículo Funerário</th>
                            <th style='width: 10px'>Veículo em Situação de Risco</th>
                            <th style='width: 10px'>Un. não solicitou Abertura/Fechamento</th>
                            <th style='width: 10px'>Un. não aguardou Abertura/Fechamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($arrOcorrencias as $unidade => $totalOcorrencias): 
                                $totalUnidade = array_sum($totalOcorrencias); ?>
                                <tr>
                                    <td style='font-weight: bold;'><?=$unidade?></td>
                                    <td><?=$totalUnidade?></td>
                                    <td><?=$totalOcorrencias['DSPORTAALTERADA']?></td>
                                    <td><?=$totalOcorrencias['DSFECHADURAALTERADA']?></td>
                                    <td><?=$totalOcorrencias['DSCADEADOALTERADO']?></td>
                                    <td><?=$totalOcorrencias['DSJANELAALTERADA']?></td>
                                    <td><?=$totalOcorrencias['DSPAREDEALTERADA']?></td>
                                    <td><?=$totalOcorrencias['DSCERCAALTERADA']?></td>
                                    <td><?=$totalOcorrencias['DSLUZESACESSAS']?></td>
                                    <?php
                                        for($i = 0; $i < 22; $i++): ?>
                                            <td></td>
                                        <?php
                                        endfor;
                                    ?>
                                </tr>
                            <?php
                            endforeach;
                        ?>

                        <tr>
                            <td style='font-weight: bolder;'>Áreas Abertas</td>
                            <td><?=$subTotalAreas?></td>

                            <?php
                                for($i = 1; $i < 8; $i++): ?>
                                    <td></td>
                                <?php
                                endfor;

                                foreach ($arrAreaAberta as $areas => $total): ?>
                                    <td><?=$total?></td>
                                <?php
                                endforeach;
                            ?>
                        </tr>

                        <tr style='font-weight: bolder;'>
                            <td>Sub-Total Mês</td>
                            <td><?=$subTotal?></td>
                            <td><?=$arrTotal['DSPORTAALTERADA']?></td>
                            <td><?=$arrTotal['DSFECHADURAALTERADA']?></td>
                            <td><?=$arrTotal['DSCADEADOALTERADO']?></td>
                            <td><?=$arrTotal['DSJANELAALTERADA']?></td>
                            <td><?=$arrTotal['DSPAREDEALTERADA']?></td>
                            <td><?=$arrTotal['DSCERCAALTERADA']?></td>
                            <td><?=$arrTotal['DSLUZESACESSAS']?></td>

                            <?php
                                foreach ($arrAreaAberta as $areas => $total): ?>
                                    <td><?=$total?></td>
                                <?php
                                endforeach;
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function RelatorioMensalPDF(&$titulo) {
        $titulo = "Mensal ".$this->post['DAMESREFERENCIA'];
        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));

        
        $sql = "SELECT pa.IDPOSTOAREA, CONCAT(p.NMPOSTO, ' - ', a.NMAREA) as DSUNIDADE,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 23 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSPORTAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 24 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSFECHADURAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 25 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSCADEADOALTERADO,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 26 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSJANELAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 27 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSPAREDEALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 28 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSCERCAALTERADA,
                      (SELECT COUNT(o.IDOCORRENCIA) FROM ocorrencia o WHERE o.IDPOSTOAREA = pa.IDPOSTOAREA && o.IDTIPOOCORRENCIA = 29 AND DATE_FORMAT(o.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA) AS DSLUZESACESSAS
                FROM postoarea pa
                JOIN posto p ON p.IDPOSTO = pa.IDPOSTO
                JOIN area a ON a.IDAREA = pa.IDAREA
                ORDER BY pa.IDPOSTOAREA ASC";

        $arrOcorrencias = DataBase::MontaArrayNCampos($sql, $arrBinds, 'DSUNIDADE', array('DSPORTAALTERADA', 'DSFECHADURAALTERADA', 'DSCADEADOALTERADO', 'DSJANELAALTERADA', 'DSPAREDEALTERADA', 'DSCERCAALTERADA', 'DSLUZESACESSAS'));
        $arrTotal = array('DSPORTAALTERADA'=>0, 'DSFECHADURAALTERADA'=>0, 'DSCADEADOALTERADO'=>0, 'DSJANELAALTERADA'=>0, 'DSPAREDEALTERADA'=>0, 'DSCERCAALTERADA'=>0, 'DSLUZESACESSAS'=>0);

        foreach ($arrOcorrencias as $unidade => $valor) {
            $arrTotal['DSPORTAALTERADA'] += $valor['DSPORTAALTERADA'];
            $arrTotal['DSFECHADURAALTERADA'] += $valor['DSFECHADURAALTERADA'];
            $arrTotal['DSCADEADOALTERADO'] += $valor['DSCADEADOALTERADO'];
            $arrTotal['DSJANELAALTERADA'] += $valor['DSJANELAALTERADA'];
            $arrTotal['DSPAREDEALTERADA'] += $valor['DSPAREDEALTERADA'];
            $arrTotal['DSCERCAALTERADA'] += $valor['DSCERCAALTERADA'];
            $arrTotal['DSLUZESACESSAS'] += $valor['DSLUZESACESSAS'];
        }

        $subTotal = array_sum($arrTotal);

        $sql = "SELECT COALESCE(sub.TOTAL, 0) AS TOTAL, t.NMOCORRENCIA 
                FROM tipoocorrencia t
                LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL,
                                 tP.IDTIPOOCORRENCIA 
                           FROM ocorrencia pa
                           LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                           WHERE tp.FLAREAABERTA = 'S' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                           GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                WHERE t.FLAREAABERTA = 'S'";

        $arrAreaAberta = DataBase::MontaArraySelect($sql, $arrBinds, 'NMOCORRENCIA', 'TOTAL');
        $subTotalAreas = array_sum($arrAreaAberta);

        $subTotal += $subTotalAreas;
        $str = "";

        $str .= "<div>";
        $str .= "<table>";
            $str .= "<tr>";
                $str .= "<td><img src='./img/logo.png' style='width: 120px;'></td>";
                $str .= "<td><p>Universidade de Passo Fundo<br>Divisão de Serviços Gerais<br>Segurança Patrimonial</p><td>";
            $str .= "</tr>";
        $str .= "</table>";
    
        $str .= "<p style='text-align: center;'>Controle Mensal de Ocorrências<br>".$this->post['DAMESREFERENCIA']."</p>";

        $str.= "<div class='inline-content ibox'>";
            $str.= "<div class='inline-body table-responsive'>";
                $str.= "<table  border='1' cellpading='0' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
                    $str.= "<thead>";
                        $str.= "<tr style='text-rotate: 90'>";
                            $str.= "<th style='width: 1%;'>Unidade</th>";
                            $str.= "<th style='width: 10px'>Total Mês</th>";
                            $str.= "<th style='width: 10px'>Porta(ão) Alt.</th>";
                            $str.= "<th style='width: 10px'>Fechadura Alt.</th>";
                            $str.= "<th style='width: 10px'>Cadeado Alt.</th>";
                            $str.= "<th style='width: 10px'>Vidraça Alt.</th>";
                            $str.= "<th style='width: 10px'>Telhado Alt.</th>";
                            $str.= "<th style='width: 10px'>Cerca/Grade Alt.</th>";
                            $str.= "<th style='width: 10px'>Luzes Acesas</th>";

                            $str.= "<th style='width: 10px'>Comp. Curioso</th>";
                            $str.= "<th style='width: 10px'>Acomp. Visual</th>";
                            $str.= "<th style='width: 10px'>Contato p/ Informação</th>";
                            $str.= "<th style='width: 10px'>Disparo Alarme</th>";
                            $str.= "<th style='width: 10px'>Averiguação</th>";
                            $str.= "<th style='width: 10px'>Obj. Encontado</th>";
                            $str.= "<th style='width: 10px'>Obj. Desaparecidos</th>";
                            $str.= "<th style='width: 10px'>Flagrante</th>";
                            $str.= "<th style='width: 10px'>Ameaça</th>";
                            $str.= "<th style='width: 10px'>Agressão</th>";
                            $str.= "<th style='width: 10px'>Apoio</th>";
                            $str.= "<th style='width: 10px'>Acidente</th>";
                            $str.= "<th style='width: 10px'>Primeiros Socorros</th>";
                            $str.= "<th style='width: 10px'>Transp. Emerg. Int</th>";
                            $str.= "<th style='width: 10px'>Transp. Emerg. Ext</th>";
                            $str.= "<th style='width: 10px'>Carga/Descarga</th>";
                            $str.= "<th style='width: 10px'>Carga de Risco</th>";
                            $str.= "<th style='width: 10px'>Veículo Oficial</th>";
                            $str.= "<th style='width: 10px'>Veículo Funerário</th>";
                            $str.= "<th style='width: 10px'>Veículo em Situação de Risco</th>";
                            $str.= "<th style='width: 10px'>Un. não solicitou Abertura/Fechamento</th>";
                            $str.= "<th style='width: 10px'>Un. não aguardou Abertura/Fechamento</th>";
                        $str.= "</tr>";
                    $str.= "</thead>";
                    $str.= "<tbody>";
                            foreach ($arrOcorrencias as $unidade => $totalOcorrencias): 
                                $totalUnidade = array_sum($totalOcorrencias);
                                $str.= "<tr>";
                                    $str.= "<td style='font-weight: bold;'>".$unidade."</td>";
                                    $str.= "<td>".$totalUnidade."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSPORTAALTERADA']."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSFECHADURAALTERADA']."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSCADEADOALTERADO']."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSJANELAALTERADA']."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSPAREDEALTERADA']."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSCERCAALTERADA']."</td>";
                                    $str.= "<td>".$totalOcorrencias['DSLUZESACESSAS']."</td>";
                                    for($i = 0; $i < 22; $i++):
                                        $str.= "<td></td>";
                                    endfor;
                                $str.= "</tr>";
                            endforeach;

                        $str.= "<tr>";
                            $str.= "<td style='font-weight: bold;'>Áreas Abertas</td>";
                            $str.= "<td>".$subTotalAreas."</td>";
                                for($i = 1; $i < 8; $i++):
                                    $str.= "<td></td>";
                                endfor;

                                foreach ($arrAreaAberta as $areas => $total):
                                    $str.= "<td>".$total."</td>";
                                endforeach;
                        $str.=  "</tr>";

                        $str.= "<tr>";
                            $str.= "<td style='font-weight: bold;'>Sub-Total Mês</td>";
                            $str.= "<td style='font-weight: bold;'>".$subTotal."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSPORTAALTERADA']."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSFECHADURAALTERADA']."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSCADEADOALTERADO']."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSJANELAALTERADA']."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSPAREDEALTERADA']."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSCERCAALTERADA']."</td>";
                            $str.= "<td style='font-weight: bold;'>".$arrTotal['DSLUZESACESSAS']."</td>";
                            foreach ($arrAreaAberta as $areas => $total):
                                $str.= "<td style='font-weight: bold;'>".$total."</td>";
                            endforeach;
                        $str.= "</tr>";
                    $str.= "</tbody>";
                $str.= "</table>";
            $str.= "</div>";
        $str.= "</div>";

        return $str;
    }

    private function RelatorioDiarioPDF(&$titulo) {
        $titulo = "Diário".$this->post['DAMESREFERENCIA'];
        $arrBinds = array(':IDGRUPO'=>array($this->post['IDGRUPO'], 'PARAM_STR'));
        $sql = "SELECT p.IDPOSTOAREA
                FROM postoarea p 
                JOIN area a on a.IDAREA = p.IDAREA 
                WHERE a.IDGRUPO = :IDGRUPO";
        $arrGrupo = Database::ExecutaSQLDados($sql, $arrBinds);
        $total = count($arrGrupo);

        $arrBinds = array(':DSMESREFERENCIA'=>array($this->post['DAMESREFERENCIA'], 'PARAM_STR'));
        $sql = "";

        for ($i = 0; $i < $total; $i++) {
            $idarea = $arrGrupo[$i]['IDPOSTOAREA'];

            $arrBinds[':IDPOSTOAREA_'. $idarea] = array( $idarea, 'PARAM_INT');
            $sql .= "(SELECT (SELECT concat(p.NMPOSTO, ' - ', a.NMAREA) DSUNIDADE 
                              FROM postoarea pa
                              JOIN area a on a.IDAREA = pa.IDAREA 
                              JOIN posto p on p.IDPOSTO = pa.IDPOSTO
                              WHERE pa.IDPOSTOAREA = :IDPOSTOAREA_". $idarea."
                              limit 1) DSUNIDADE, t.NMOCORRENCIA, COALESCE(sub.TOTAL, 0) TOTAL, sub.NRDIA
                    FROM tipoocorrencia t
                    LEFT JOIN (SELECT COUNT(pa.IDTIPOOCORRENCIA) TOTAL, EXTRACT(DAY FROM pa.DTOCORRENCIA) NRDIA, tP.IDTIPOOCORRENCIA 
                                FROM ocorrencia pa
                                LEFT JOIN tipoocorrencia tp on tp.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                                WHERE tp.FLAREAABERTA = 'N' AND DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = :DSMESREFERENCIA
                                AND pa.IDPOSTOAREA = :IDPOSTOAREA_". $idarea."
                                GROUP BY pa.IDPOSTOAREA, tp.IDTIPOOCORRENCIA, EXTRACT(DAY FROM pa.DTOCORRENCIA)
                                ORDER BY NRDIA) sub on sub.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
                                where t.FLAREAABERTA  = 'N')";

            if ($i < $total - 1)
                $sql .= "\n\nUNION ALL\n\n";
        }

        $arrOcorrenciasDiarias = Database::MontaArrayChaveComposta3($sql, $arrBinds, 'DSUNIDADE', 'NMOCORRENCIA', 'NRDIA', array('TOTAL'));
        $arrTotal = array();
        for ($i = 1; $i <= 31; $i++) {
            foreach ($arrOcorrenciasDiarias as $unidade => $arrOcorrencia) {
                foreach ($arrOcorrencia as $nmOcorrencia => $dia) {
                    $arrTotal[$i] = 0;
                    if (!array_key_exists($i, $dia)) {
                        $arrOcorrenciasDiarias[$unidade][$nmOcorrencia][$i]['TOTAL'] = 0;
                        ksort($arrOcorrenciasDiarias[$unidade][$nmOcorrencia]);
                    }
                }
            }
        }

        $str = "";
        $str .= "<div>";
            $str .= "<table>";
                $str .= "<tr>";
                    $str .= "<td><img src='./img/logo.png' style='width: 120px;'></td>";
                    $str .= "<td><p>Universidade de Passo Fundo<br>Divisão de Serviços Gerais<br>Segurança Patrimonial</p><td>";
                $str .= "</tr>";
            $str .= "</table>";
        $str .= "</div>";

        $str .= "<p style='text-align: center;'>Controle Diário<br>".$this->post['DAMESREFERENCIA']."</p>";

        $str .= "<div class='inline-content ibox'>";
            $str .= "<div class='inline-body table-responsive'>";
                $str .= "<table border='1' cellpading='0' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
                    $str .= "<tbody>";
                        $str .= "<tr style='font-weight: bold;'>";
                            $str .= "<td style='width:5%;'>Unidade</td>";
                            $str .= "<td style='width:11%'>Ocorrência</td>";
                            for ($i = 1; $i <= 31; $i++):
                                $str .= "<td style='width: 2%;'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</td>";

                            endfor;
                            $str .= "<td style='width:5%'>Sub-Total</td>";
                        $str .= "</tr>";
                            foreach ($arrOcorrenciasDiarias as $unidade => $arrDadosOcorrencia):
                                $str .= "<tr>";
                                    $str .= "<td colspan='1' rowspan='8' style='width:5%; text-align: center; vertical-align: middle; text-rotate: 90'>".$unidade."</td>";
                                        foreach ($arrDadosOcorrencia as $ocorrencia => $arrDia): 
                                            $ocorrencias = 0;
                                            $str .= "<tr>";
                                            $str .= "<td style='width:11%'>".$ocorrencia."</td>";
                                                foreach ($arrDia as $dia => $total):
                                                    if ($dia <> NULL):
                                                        $ocorrencias += $total['TOTAL']; 
                                                        $arrTotal[$dia] += $total['TOTAL'];
                                                        $str .= "<td style='width:2%'>".$total['TOTAL']."</td>";
                                                endif;
                                                endforeach;
                                            $str .= "<td style='width:2%'>".$ocorrencias."</td>";
                                        $str .= "</tr>";
                                        endforeach;
                                $str .= "</tr>";
                            endforeach;
                            foreach ($arrTotal as $dia => $totalDia)
                        $str .= "<tr style='font-weight: bold'>";
                            $str .= "<td colspan='2' style='width:15.8%; text-align: center;'>Sub-Total por Dia</td>";
                                foreach ($arrTotal as $dia => $totalDia):
                                    $str .= "<td colspan='1' style='width: 2%;'>".$totalDia."</td>";
                                endforeach;
                            $str .= "<td colspan='1' style='width: 5%'>".array_sum($arrTotal)."</td>";
                        $str .= "</tr>";
                    $str .= "</tbody>";
                $str .= "</table>";
            $str .= "</div>";
        $str .= "</div>";

        return $str;
    }

    private function GeraPDF() {
        require_once MPDF_PATH.'mpdf.php';
        ob_clean();

        $mpdf = new mPDF('utf-8', 'A4-P');
        switch($this->tipoRelatorio) {
            case 'A':
                $mpdf->WriteHTML($this->RelatorioAreasAbertasPDF($titulo));
            break;
            case 'D':
                $mpdf->WriteHTML($this->RelatorioDiarioPDF($titulo));
            break;
            case 'M':
                $mpdf->WriteHTML($this->RelatorioMensalPDF($titulo));
            break;
        }

        $mpdf->setTitle($titulo);
        $mpdf->Output($titulo.'.pdf', "D");

        exit();
    }

    private function GeraCSV() {
        ob_clean();
        switch($this->tipoRelatorio) {
            case 'A':
                $this->RelatorioAreasAbertasCSV();
            break;
            case 'D':
               $this->RelatorioDiarioCSV();
            break;
            case 'M':
                $this->RelatorioMensalCSV();
            break;
        }
    
        exit;
    }

    private function Table() {
        $arrDados = $this->getDados();
        ?>
        <div class="ibox-body">
            <div class="inline-content">
                <div class="inline-body">
                    <div class="table-responsive">
                        <table id="tableFilter" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <?php foreach ($this->arrTable as $arr): ?>
                                        <th <?= HTML::MontaAttrHtml($arr['arg']['attrTh']); ?> ><?= $arr['arg']['caption']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arrDados as $arrVal): ?>
                                    <tr>
                                        <?php foreach ($this->arrTable as $arr): ?>
                                            <td <?= forward_static_call(["HTML", 'MontaAttrHtml'], $arr['arg']['attrTd']); ?>>
                                                <?= array_key_exists($arr['name'], $arrVal) ? FormataValorUsuario(substr($arr['name'], 0, 2), $arrVal[$arr['name']]) : forward_static_call_array(["HTML", 'AjustaHTMLDados'], [$arr['name'], $arrVal]); ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
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
                                <?php
                                    if ($this->exportacao):
                                        echo HTML::AddButton('submit', 'btnSubmit', "Filtrar", ['class'=>'btn btn-primary']);
                                        echo HTML::AddButton('submit', 'btnPDF', "Exportar PDF", ['class'=>'btn btn-primary']);
                                        echo HTML::AddButton('submit', 'btnCSV', "Exportar CSV", ['class'=>'btn btn-primary']);

                                    else:
                                        echo HTML::AddButton('submit', 'btnFiltrar', "Filtrar", ['class'=>'btn btn-primary']);
                                    endif;
                                ?>
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
        } else if (array_key_exists('btnCSV', $_POST)){
            $this->GeraCSV();
        } else if (array_key_exists('btnSubmit', $_POST)) {
            $this->Relatorio();
        } else if (array_key_exists('btnFiltrar', $_POST)) {
            $this->Table();
        }
     }
}

?>