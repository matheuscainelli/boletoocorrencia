<?php
require 'header.php';

$filter = New Filter(ConsultaPermissao('REL_GERENCIAL'), "Relatório Gerencial");

$filter->AddSelect('TPRELATORIO', 'Tipo', ['M'=>'Mensal', 'D'=>'Diário', 'A'=>'Áreas Abertas'], ['required'=>true, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->AddSelect('IDGRUPO', 'Grupo', [ 'A'=>'Prédios A', 'B'=>'Prédios B', 'D'=>'Prédios D'], ['required'=>true, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);
$filter->AddInput('text', 'DAMESREFERENCIA', 'Mês/Referência', ['required'=>true, 'class'=>"form-control input-sm"]);
$filter->Show();
?>
<script type="text/javascript">
     $(document).ready(function() {
        $('#DAMESREFERENCIA').datepicker({
            format: 'mm/yyyy',
            minViewMode: 1,
            maxViewMode: 2
        });
    })
</script>
<?php
require 'footer.php';
?>