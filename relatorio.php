<?php
require 'header.php';

$filter = New Filter(ConsultaPermissao('REL_GERENCIAL'), "Relatório Gerencial");

$filter->AddSelect('TPRELATORIO', 'Tipo', ['M'=>'Mensal', 'D'=>'Diário', 'A'=>'Áreas Abertas'], ['required'=>true, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);

$filter->Show();
require 'footer.php';
?>