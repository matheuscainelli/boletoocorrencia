<?php
session_start();
header("Content-Type: charset=TUTF-8");
require_once 'config/config.php';
require_once 'class/funcoes.php';
require_once 'class/database.php';
Database::ConnectaBD();
$idOcorrencia = $_GET['IDOCORRENCIA'];
$arrBinds = array(':IDOCORRENCIA'=>array($idOcorrencia, 'PARAM_INT'));

$sql = "SELECT pa.IDOCORRENCIA, pa.DTOCORRENCIA, t.NMOCORRENCIA, v.NMVIGILANTE, CONCAT(p.NMPOSTO, ' - ', a.NMAREA) DSUNIDADE, pa.DSOCORRENCIA
        FROM ocorrencia pa
        JOIN tipoocorrencia t ON t.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
        JOIN vigilante v ON v.IDVIGILANTE = pa.IDVIGILANTE
        JOIN postoarea poa ON poa.IDPOSTOAREA = pa.IDPOSTOAREA
        JOIN area a ON a.IDAREA = poa.IDAREA
        JOIN posto p ON p.IDPOSTO = poa.IDPOSTO
        WHERE pa.IDOCORRENCIA = :IDOCORRENCIA";
$arrOcorrencia = Database::MontaArrayNCampos($sql, $arrBinds, 'IDOCORRENCIA', array('DSOCORRENCIA', 'DTOCORRENCIA', 'NMOCORRENCIA', 'NMVIGILANTE', 'DSUNIDADE'));

$sql = "SELECT pa.IDOCORRENCIAANEXO, pa.DSARQUIVO
        FROM ocorrenciaanexo pa
        WHERE pa.IDOCORRENCIA = :IDOCORRENCIA";
$arrAnexo = Database::MontaArraySelect($sql, $arrBinds, 'IDOCORRENCIAANEXO', 'DSARQUIVO');

$str = "";
$str .= "<div>";
    $str .= "<table>";
        $str .= "<tr>";
            $str .= "<td><img src='./img/logo.png' style='width: 120px;'></td>";
            $str .= "<td><p>Universidade de Passo Fundo<br>Divisão de Serviços Gerais<br>Segurança Patrimonial</p><td>";
        $str .= "</tr>";
    $str .= "</table>";

    $str .= "<p style='text-align: center;'>Ocorrência ".$idOcorrencia."</p>";

    $str .= "<div class='ibox'>";
        $str .= "<div class='ibox-content'>";
            $str .= "<div class='ibox-header'>";
                $str .= "<span class='ibox-title'></span>";
            $str .= "</div>";
            $str .= "<div class='container'>";
                $str .= "<div id='resumo'>";
                    $str .= "<h3 id='ocorrencia'>Ocorrência:</h3><span style='font-size: 16px'>". $arrOcorrencia[$idOcorrencia]['NMOCORRENCIA']."</span>";
                    $str .= "<h3 id='vigilante'>Vigilante:</h3><span style='font-size: 16px'>". $arrOcorrencia[$idOcorrencia]['NMVIGILANTE']."</span>";
                    $str .= "<h3 id='unidade'>Unidade:</h3><span style='font-size: 16px'>". $arrOcorrencia[$idOcorrencia]['DSUNIDADE']."</span>";
                    $str .= "<h3 id='hora'>Data/Hora:</h3><span style='font-size: 16px'>". FormataDataHoraUsuario($arrOcorrencia[$idOcorrencia]['DTOCORRENCIA'])."</span>";
                    $str .= "<h3 id='hora'>Descrição:</h3><span style='font-size: 16px; white-space: nowrap;'>". $arrOcorrencia[$idOcorrencia]['DSOCORRENCIA']."</span>";
                    if (!empty($arrAnexo)):
                        $str .= "<h3 id='hora'>Anexos:</h3>";
                        foreach ($arrAnexo as $idAnexo => $nome):
                            $str .= "<img src='data:image/base64, ".gzuncompress(base64_decode($nome))."'>";
                        endforeach;
                    endif;
                $str .= "</div>";
            $str .= "</div>";
        $str .= "</div>";
    $str .= "</div>";
$str .= "</div>";
die($str);
require_once MPDF_PATH.'mpdf.php';
ob_clean();

$mpdf = new mPDF('utf-8', 'A4-P');
$mpdf->WriteHTML($str);
$mpdf->setTitle("Ocorrência ".$idOcorrencia);
$mpdf->Output("Ocorrência ".$idOcorrencia.'.pdf', "D");

exit();
?>