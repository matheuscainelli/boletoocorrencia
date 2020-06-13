<?php
require 'header.php';

$filter = New Filter(ConsultaPermissao('REL_GERENCIAL'), "Relatório Gerencial");
$filter->HabilitaExportacao(true);
$filter->AddSelect('TPRELATORIO', 'Tipo', ['M'=>'Mensal', 'D'=>'Diário', 'A'=>'Áreas Abertas'], ['required'=>true, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->AddInput('text', 'DAMESREFERENCIA', 'Mês/Referência', ['required'=>true, 'class'=>"form-control input-sm"]);
$filter->AddSelect('IDGRUPO', 'Grupo', BuscaArrGrupo(), ['required'=>false, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->Show();
?>
<script type="text/javascript">
     $(document).ready(function() {
        $('#DAMESREFERENCIA').datepicker({
            format: 'mm/yyyy',
            minViewMode: 1,
            maxViewMode: 2
        });


        $('#TPRELATORIO').on('change', function() {
            let tprelatorio = $('#TPRELATORIO').val();

            if (tprelatorio === 'D') {
                $('#IDGRUPO').prop('required', true);
            } else {
                $('#IDGRUPO').prop('required', false);
            }
        })
    })
</script>
<?php
require 'footer.php';
?>