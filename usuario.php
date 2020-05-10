<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM usuario pa
        WHERE TRUE";

$form = new Form(TRUE, "Usuário");
$form->SetSql($sql, 'usuario', 'IDUSUARIO');
$form->SetCamposSQL(['IDUSUARIO', 'NMUSUARIO', 'SGUSUARIO', 'PWUSUARIO']);

$form->AddTable('IDUSUARIO', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMUSUARIO', 'Usuário', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('SGUSUARIO', 'Sigla', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDUSUARIO', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMUSUARIO', 'Nome', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddInput('text', 'SGUSUARIO', 'Sigla', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddInput('password', 'PWUSUARIO', 'Senha', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();
require 'footer.php';
?>