<?php
require 'header.php';
$hora = array_key_exists('DTOCORRENCIA', $_POST) ? QuotedStr($_POST['DTOCORRENCIA']) : NULL;

$sql = sprintf("SELECT pa.IDOCORRENCIA, pa.DTOCORRENCIA, t.NMOCORRENCIA, v.NMVIGILANTE, CONCAT(p.NMPOSTO, ' - ', a.NMAREA) DSUNIDADE,
                        CASE pa.TPSTATUS
                                WHEN 'E' THEN 'Encaminhada'
                                WHEN 'A' THEN 'Em Análise'
                                WHEN 'R' THEN 'Resolvida'
                        END DSSTATUS,
                        CASE WHEN TPSTATUS = 'E' THEN CONCAT('<a data-id=\"',pa.IDOCORRENCIA,'\" data-ocorrencia=\"',pa.IDOCORRENCIA,'\" title=\"Analisar\" class=\"btnAvaliar\"><i class=\"fa fa-gear\" style=\"color: #1378bf\"></i></a') 
                                WHEN TPSTATUS = 'R' THEN CONCAT('<a data-id=\"',pa.IDOCORRENCIA,'\" data-ocorrencia=\"',pa.IDOCORRENCIA,'\" title=\"Analisar\" class=\"btnAvaliar\"><i class=\"fa fa-check\" style=\"color: #64c832\"></i></a')
                            ELSE CONCAT('<a data-id=\"',pa.IDOCORRENCIA,'\" data-ocorrencia=\"',pa.IDOCORRENCIA,'\" title=\"Analisar\" class=\"btnAvaliar\"><i class=\"fas fa-hourglass-half\" style=\"color: #ff9800\"></i></a')
                     END BTNACAO
                FROM ocorrencia pa
                JOIN tipoocorrencia t ON t.IDTIPOOCORRENCIA = pa.IDTIPOOCORRENCIA
                JOIN vigilante v ON v.IDVIGILANTE = pa.IDVIGILANTE
                JOIN postoarea poa ON poa.IDPOSTOAREA = pa.IDPOSTOAREA
                JOIN area a ON a.IDAREA = poa.IDAREA
                JOIN posto p ON p.IDPOSTO = poa.IDPOSTO
                WHERE DATE_FORMAT(pa.DTOCORRENCIA , '%%m/%%Y') = %s 
                {{WHERE}}", $hora);

$filter = New Filter(ConsultaPermissao('TAR_MANUTENCAO'), 'Manutenção de Ocorrências', $sql);
$filter->HabilitaExportacao(false);
$filter->SetCamposSQL(['IDTIPOOCORRENCIA', 'IDVIGILANTE', 'IDPOSTOAREA', 'TPSTATUS']);

$filter->AddInput('text', 'DTOCORRENCIA', 'Mês/Referência', ['required'=>true, 'class'=>"form-control input-sm"]);
$filter->AddSelect('IDTIPOOCORRENCIA', 'Tipo Ocorrência', BuscaArrTipoOcorrencia(), ['required'=>false, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->AddSelect('IDVIGILANTE', 'Vigilante', BuscaArrVigilante(), ['required'=>false, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->AddSelect('IDPOSTOAREA', 'Unidade', BuscaArrPostoArea(), ['required'=>false, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->AddSelect('TPSTATUS', 'Status', ['A'=>'Em Análise', 'E'=>'Encaminhada', 'R'=>'Resolvida'], ['required'=>false, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);

$filter->AddTable('IDOCORRENCIA', 'Sequencial', [], ['style'=>'width: 1%']);
$filter->AddTable('NMOCORRENCIA', 'Ocorrência', [], ['style'=>'width: 10%']);
$filter->AddTable('NMVIGILANTE', 'Vigilante', [], ['style'=>'width: 10%']);
$filter->AddTable('DSUNIDADE', 'Unidade', [], ['style'=>'width: 10%']);
$filter->AddTable('DTOCORRENCIA', 'Data/Hora', [], ['style'=>'width: 10%']);
$filter->AddTable('DSSTATUS', 'Status', [], ['style'=>'width: 10%']);
$filter->AddTable('BTNACAO', '', [], ['style'=>"width: 2%; text-align: center"]);
$filter->Show();
?>
<script type="text/javascript">   
     $(document).ready(function() {
        $('#DTOCORRENCIA').datepicker({
            format: 'mm/yyyy',
            minViewMode: 1,
            maxViewMode: 2
        });

        $('.ibox-body').on('click', '.btnAvaliar', function AbreOcorrencia() {
            idOcorrencia = $(this).data('ocorrencia');
            // $(this).parents('tr').remove();
            window.open('manutencao.php?IDOCORRENCIA='+idOcorrencia, '_blank')
        });

        let searchParams = new URLSearchParams(window.location.search);
        if (searchParams.has('TPSTATUS')) {
            var data = new Date();
            data = data.toLocaleDateString();
            data = data.substring(3, 10);

            $('#DTOCORRENCIA').val(data);
            $('#TPSTATUS').val(searchParams.get('TPSTATUS'));
            $('#btnFiltrar').trigger('click');
        }
    })
</script>
<?php
require 'footer.php';
?>