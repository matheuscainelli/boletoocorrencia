<?php
require_once 'class/database.php';
require_once 'class/session.php';
Database::ConnectaBD();

function DownloadArquivo($idAnexo) {
    $arrBinds = array(':IDANEXO'=>array($idAnexo, 'PARAM_INT'));
    $sql = "SELECT pa.*
            FROM ocorrenciaanexo pa
            WHERE pa.IDOCORRENCIAANEXO = :IDANEXO";
    $result = Database::ExecutaSQLDados($sql, $arrBinds);

    if ($result[0]['DSARQUIVO'] != '') {
        $nomeArquivo = $result[0]['NMARQUIVO'];
        $tparquivo = $result[0]['TPANEXO'];
        $file = fopen($nomeArquivo,"a+");

        if ($result[0]['NRTAMANHOARQUIVO'] != '')
            fwrite($file, gzuncompress(base64_decode($result[0]['DSARQUIVO'])), $result[0]['NRTAMANHOARQUIVO']);
        else
            fwrite($file, gzuncompress(base64_decode($result[0]['DSARQUIVO'])));

        fclose($file);

        if(substr($nomeArquivo, -4) == '.csv') {
            $tparquivo = 'text/csv';
        }

        header("Content-type: $tparquivo");
        header("Content-Disposition: filename=".$nomeArquivo);

        if ($result[0]['NRTAMANHOARQUIVO'] != '') {
            header("Content-Length: ".$result[0]['NRTAMANHOARQUIVO']);
        }

        readfile($nomeArquivo);
        unlink($nomeArquivo);
    } else
        return false;
}

function DeletaArquivo($idAnexo) {
    Database::Deleta('ocorrenciaanexo', array('IDOCORRENCIAANEXO'=>$idAnexo));

    return 0;
}
function SalvaAnalise($post) {
    $ocorrencia = $post['IDOCORRENCIA'];
    $parecer = $post['DSPARECER'];

    Database::Deleta('manutencao', array('IDOCORRENCIA'=>$ocorrencia));
    Database::Edita('ocorrencia', array('TPSTATUS'=>'R'), array('IDOCORRENCIA'=>$ocorrencia));

    Database::Insere('manutencao', array('IDMANUTENCAO'=>Database::BuscaMaxSequencia('manutencao'),
                                         'IDOCORRENCIA'=>$ocorrencia,
                                         'DSPARECER'=>$parecer));
}

if (array_key_exists('funcao', $_POST)) {
    if ($_POST['funcao'] === 'DeletaArquivo')
        echo DeletaArquivo($_POST['IDARQUIVO']);
    if ($_POST['funcao'] === 'SalvaAnalise')
        echo SalvaAnalise($_POST);
}

if (array_key_exists('funcao', $_GET)) {
    if ($_GET['funcao'] === 'DownloadArquivo')
        echo DownloadArquivo($_GET['IDANEXO']);
}
?>