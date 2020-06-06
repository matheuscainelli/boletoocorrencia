<?php
require 'header.php';

$sql = "SELECT pa.*, g.NMGRUPO
        FROM area pa
        LEFT JOIN grupo g ON g.IDGRUPO = pa.IDGRUPO
		WHERE TRUE";

$form = new Form(ConsultaPermissao('CAD_AREA'), "Área");
$form->SetSql($sql, 'area', 'IDAREA');
$form->SetCamposSQL(['IDAREA', 'NMAREA', 'IDGRUPO']);

$form->AddTable('IDAREA', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMAREA', 'Área', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('NMGRUPO', 'Grupo', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDAREA', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMAREA', 'Área', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddSelect('IDGRUPO', 'Grupo', BuscaArrGrupo(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->Show();

require 'footer.php';
?>