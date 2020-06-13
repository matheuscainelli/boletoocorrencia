<?php
require_once 'header.php';

$sql =  "SELECT COUNT(pa.IDOCORRENCIA) AS TOTAL, pa.TPSTATUS
         FROM ocorrencia pa
         WHERE DATE_FORMAT(pa.DTOCORRENCIA , '%m/%Y') = DATE_FORMAT(CURRENT_TIMESTAMP() , '%m/%Y')
         GROUP BY pa.TPSTATUS";
$arrTotalStatus = Database::MontaArraySelect($sql, array(), 'TPSTATUS', 'TOTAL');

$arrStatus = array('E', 'R', 'A');

foreach ($arrStatus as $status) {
    if (!array_key_exists($status, $arrTotalStatus)) {
        $arrTotalStatus[$status] = 0;
    }
}

?><div id='divModulos'> <?php
    foreach ($arrTotalStatus as $status => $valores):
        $href = "";
        $class = "";
        $icon = "";

        if ($status === 'E') {
            $class .= " encaminhada";
            $dsstatus = "Encaminhada";
            $icon = "fa fa-gear";
            $href = "href=\"relmanutencao.php?TPSTATUS=E"."\"";
        } else if ($status === 'A') {
            $class .= " emanalise";
            $dsstatus = "Em Análise";
            $icon = "fas fa-hourglass-half";
            $href = "href=\"relmanutencao.php?TPSTATUS=A"."\"";
        } else if ($status === 'R') {
            $class .= "resolvida";
            $dsstatus = "Resolvida";
            $icon = "fa fa-check";
            $href = "href=\"relmanutencao.php?TPSTATUS=R"."\"";
        }
    ?>
    <div class='<?= $class ?>'>
        <a <?= $href ?>>
            <div class='modulo-icon'>
                <i class='<?= $icon ?>'></i>
            </div>
            <div class='modulo-content'>
                <h3 class='modulo-title'><?= $dsstatus  ?></h3>
                <div>
                    <span class='modulo-count'><?= $valores?> </span>
                        <span class="modulo-andamento"></span>
                </div>
                <a class='modulo-link'>Relatório
                    <i class='fa fa-arrow-circle-right'></i>
                </a>
            </div>
        </a>
    </div>

<?php
endforeach;
?>
</div>
<?php
require 'footer.php';
?>