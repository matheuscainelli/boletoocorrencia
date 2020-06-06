<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM grupo pa
		WHERE TRUE";

$form = new Form(ConsultaPermissao('CAD_GRUPO'), "Grupo");
$form->SetSql($sql, 'grupo', 'IDGRUPO');
$form->SetCamposSQL(['IDGRUPO', 'NMGRUPO']);

$form->AddTable('IDGRUPO', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMGRUPO', 'Nome', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDGRUPO', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMGRUPO', 'Nome', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();

require 'footer.php';
?>