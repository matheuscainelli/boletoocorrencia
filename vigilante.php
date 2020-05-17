<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM vigilante pa
		WHERE TRUE";

$form = new Form(TRUE, "Vigilantes");
$form->SetSql($sql, 'vigilante', 'IDVIGILANTE');
$form->SetCamposSQL(['IDVIGILANTE', 'NMVIGILANTE']);

$form->AddTable('IDVIGILANTE', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMVIGILANTE', 'Nome', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDVIGILANTE', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMVIGILANTE', 'Nome', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();

require 'footer.php';
?>