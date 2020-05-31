<?php
require 'header.php';

$sql = "SELECT pa.*, pe.NMPERFIL, ca.NMCAMPUS
        FROM usuario pa
        JOIN perfil pe ON pa.IDPERFIL = pe.IDPERFIL
        JOIN campus ca ON ca.IDCAMPUS = pa.IDCAMPUS
        WHERE pa.IDUSUARIO > 0";

$form = new Form(ConsultaPermissao('CAD_USUARIO'), "Usuário");
$form->SetSql($sql, 'usuario', 'IDUSUARIO');
$form->SetCamposSQL(['IDUSUARIO', 'NMUSUARIO', 'SGUSUARIO', 'PWUSUARIO', 'FLATIVO', 'IDPERFIL', 'IDCAMPUS']);

$form->AddTable('IDUSUARIO', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMUSUARIO', 'Usuário', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('SGUSUARIO', 'Sigla', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('FLATIVO', 'Ativo', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('NMPERFIL', 'Perfil', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDUSUARIO', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMUSUARIO', 'Nome', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddInput('text', 'SGUSUARIO', 'Sigla', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddInput('password', 'PWUSUARIO', 'Senha', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddSelect('IDPERFIL', 'Pefil', BuscaArrPerfil(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('IDCAMPUS', 'Campus', BuscaArrCampus(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('FLATIVO', 'Ativo', ['S'=>'Sim', 'N'=>'Não'], ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->Show();
require 'footer.php';
?>