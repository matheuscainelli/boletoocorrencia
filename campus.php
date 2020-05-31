<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM campus pa
		WHERE TRUE";

$form = new Form(ConsultaPermissao('CAD_grupo'), "Campus");
$form->SetSql($sql, 'campus', 'IDCAMPUS');
$form->SetCamposSQL(['IDCAMPUS', 'NMCAMPUS']);

$form->AddTable('IDCAMPUS', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMCAMPUS', 'Campus', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDCAMPUS', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMCAMPUS', 'Campus', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();

require 'footer.php';
?>