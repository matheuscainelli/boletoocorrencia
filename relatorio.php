<?php
require 'header.php';

$filter = New Filter(true, "Relatório Gerencial");

$filter->AddSelect('TPRELATORIO', 'Tipo', ['M'=>'Mensal', 'D'=>'Diário', 'A'=>'Áreas Abertas'], ['required'=>true, 'placeholder'=>"Selecione:", 'class'=>"form-control input-sm"]);

$filter->Show();
require 'footer.php';
?>