<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM area pa
		WHERE TRUE";

$form = new Form(TRUE, "Área");
$form->SetSql($sql, 'area', 'IDAREA');
$form->SetCamposSQL(['IDAREA', 'NMAREA']);

$form->AddTable('IDAREA', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMAREA', 'Área', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDAREA', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMAREA', 'Área', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();

require 'footer.php';
?>