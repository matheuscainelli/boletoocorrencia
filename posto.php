<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM posto pa
		WHERE TRUE";

$form = new Form(TRUE, "Posto");
$form->SetSql($sql, 'posto', 'IDPOSTO');
$form->SetCamposSQL(['IDPOSTO', 'NMPOSTO']);

$form->AddTable('IDPOSTO', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMPOSTO', 'Posto', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDPOSTO', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMPOSTO', 'Posto', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();

require 'footer.php';
?>