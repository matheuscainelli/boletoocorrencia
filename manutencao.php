<?php
require 'header.php';

if (!array_key_exists('IDOCORRENCIA', $_GET)) {
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

$idOcorrencia = $_GET['IDOCORRENCIA'];

$arrBinds = array(':IDOCORRENCIA'=>array($idOcorrencia, 'PARAM_STR'));
$sql = "SELECT pa.TPSTATUS
        FROM ocorrencia pa
        WHERE pa.IDOCORRENCIA = :IDOCORRENCIA";
$arrOcorrenciaStatus = Database::ExecutaSQLDados($sql, $arrBinds);

if ($arrOcorrenciaStatus[0]['TPSTATUS'] === 'E') {
    Database::Edita('ocorrencia', array('TPSTATUS'=>'A'), array('IDOCORRENCIA'=>$idOcorrencia));
}

$sql = "SELECT pa.IDOCORRENCIA, pa.DSOCORRENCIA, pa.DTOCORRENCIA, t.NMOCORRENCIA, v.NMVIGILANTE, CONCAT(p.NMPOSTO, ' - ', a.NMAREA) DSUNIDADE
        FROM ocorrencia pa
        JOIN tipoocorrencia t ON pa.IDTIPOOCORRENCIA = t.IDTIPOOCORRENCIA
        JOIN postoarea poa ON pa.IDPOSTOAREA = poa.IDPOSTOAREA
        JOIN area a ON poa.IDAREA = a.IDAREA
        JOIN posto p ON poa.IDPOSTO = p.IDPOSTO
        JOIN vigilante v ON pa.IDVIGILANTE = v.IDVIGILANTE
        WHERE pa.IDOCORRENCIA = :IDOCORRENCIA";
$arrOcorrencia = Database::MontaArrayNCampos($sql, $arrBinds, 'IDOCORRENCIA', array('DSOCORRENCIA', 'DTOCORRENCIA', 'NMOCORRENCIA', 'NMVIGILANTE', 'DSUNIDADE'));

$sql = "SELECT pa.IDOCORRENCIAANEXO, pa.NMARQUIVO
        FROM ocorrenciaanexo pa
        WHERE pa.IDOCORRENCIA = :IDOCORRENCIA";
$arrAnexo = Database::MontaArraySelect($sql, $arrBinds, 'IDOCORRENCIAANEXO', 'NMARQUIVO');

$sql = "SELECT DSPARECER 
        FROM manutencao
        WHERE IDOCORRENCIA = :IDOCORRENCIA";
$arrAnalise = Database::ExecutaSQLDados($sql, $arrBinds);
?>
<div class="ibox">
    <div class="ibox-content">
        <div class="container">
            <div id="resumo">
                <h3 id="ocorrencia">Ocorrência:</h3><span style='font-size: 16px'><?= $arrOcorrencia[$idOcorrencia]['NMOCORRENCIA']; ?></span>
                <h3 id="vigilante">Vigilante:</h3><span style='font-size: 16px'><?= $arrOcorrencia[$idOcorrencia]['NMVIGILANTE']; ?></span>
                <h3 id="unidade">Unidade:</h3><span style='font-size: 16px'><?= $arrOcorrencia[$idOcorrencia]['DSUNIDADE']; ?></span>
                <h3 id="hora">Data/Hora:</h3><span style='font-size: 16px'><?= FormataDataHoraUsuario($arrOcorrencia[$idOcorrencia]['DTOCORRENCIA']); ?></span>
                <h3 id="hora">Descrição:</h3><span style='font-size: 16px; white-space: nowrap;'><?= $arrOcorrencia[$idOcorrencia]['DSOCORRENCIA']; ?></span>
                <h3 id="hora">Anexos:</h3>

                <?php
                    foreach ($arrAnexo as $idAnexo => $nome): ?>
                        <b style='font-size: 16px'><a onclick="AbreArquivo(<?=$idAnexo?>)" rel='noreferrer' target="_blank"><?= $nome?></a><i onclick="ExcluiArquivo(<?=$idAnexo?>, this)" class="fa fa-trash icon-form" style='color:red; margin-left: 10px; cursor: pointer;' title="Excluir"></i></b></br>
                    <?php
                    endforeach;
                ?>
                <hr/>
                <h3 id="parecerSurpevisor" style='text-align: center;'>Análise Supervisor:</h3>
                <?php
                    if ($arrOcorrenciaStatus[0]['TPSTATUS'] === 'A' || $arrOcorrenciaStatus[0]['TPSTATUS'] === 'E') {
                        echo HTML::AddTextArea('DSPARECER', 'Parecer Surprevisor', [], ['style'=>'max-width: 100%', 'class'=>HTML::GetLargura(12)]);
                    } else {
                        ?>
                        <span style='font-size: 16px; white-space: nowrap;'><?= $arrAnalise[0]['DSPARECER'] ?? ''; ?></span>
                        <?php
                    }
                ?>
                <br/>
                <hr/>
                <div class="ibox-buttons" style='margin-bottom: 10px; float:right'>
                    <a class="btn btn-primary btn-outline" id='btnVoltar'>Voltar</a>
                    <?php
                        if ($arrOcorrenciaStatus[0]['TPSTATUS'] === 'A' || $arrOcorrenciaStatus[0]['TPSTATUS'] === 'E') {
                            echo  HTML::AddButton('button', 'btnSalvar', "Salvar", ['class'=>'btn btn-primary', 'onclick'=>'SalvaAnalise('.$idOcorrencia.')']);
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#DSPARECER').summernote({
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

    $('#btnVoltar').on('click', function() {
        window.close();
    })

    function AbreArquivo(idArquivo) {
        window.open('ajax.php?funcao=DownloadArquivo&IDANEXO='+idArquivo, '_blank');
    }

    function ExcluiArquivo(idArquivo, elem) {
        $.ajax({
            url: 'ajax.php',
            data: {
                'IDARQUIVO': idArquivo,
                'funcao': 'DeletaArquivo'
            },
            type: 'POST',
            success: function (ret) {
                $(elem).parent().remove();
            }
        });
    }

    function SalvaAnalise(idOcorrencia) {
        let parecer = $('.note-editable').text();
        $.ajax({
            url: 'ajax.php',
            data: {
                'IDOCORRENCIA': idOcorrencia,
                'DSPARECER': parecer,
                'funcao': 'SalvaAnalise'
            },
            type: 'POST',
            success: function (ret) {
                window.close();
            }
        });
    }
</script>
<?php
require 'footer.php';
?>