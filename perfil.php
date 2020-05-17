<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM perfil pa
		WHERE TRUE";

$form = new Form(TRUE, "Perfil");
$form->SetSql($sql, 'perfil', 'IDPERFIL');
$form->SetCamposSQL(['IDPERFIL', 'NMPERFIL']);

$form->AddTable('IDPERFIL', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMPERFIL', 'Perfil', ['width'=>"40px"], ['align'=>"left"]);
// $form->AddTableAction("<i class=\"fa fa-lock\"></i>&nbsp;Permisões</a>", "perfilpermissao.php?id=:IDPERFIL:", true);

$form->AddInput('text', 'IDPERFIL', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMPERFIL', 'Perfil', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->Show();

require 'footer.php';
?>