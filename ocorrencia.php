<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM ocorrencia pa
        WHERE TRUE";

$form = new Form(TRUE, "Ocorrências");
$form->SetSql($sql, 'ocorrencia', 'IDOCORRENCIA');
$form->SetCamposSQL(['IDOCORRENCIA', 'IDTIPOOCORRENCIA', 'IDVIGILANTE', 'IDAREA', 'DTOCORRENCIA', 'DSOCORRENCIA']);
$form->HabilitaAnexo(true);

$form->AddTable('IDOCORRENCIA', 'Ocorrência', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('IDTIPOOCORRENCIA', 'Tipo Ocorrência', ['width'=>"20px"], ['align'=>"left"]);
$form->AddTable('IDVIGILANTE', 'Vigilante', ['width'=>"20px"], ['align'=>"left"]);
$form->AddTable('IDAREA', 'Área', ['width'=>"20px"], ['align'=>"left"]);
$form->AddTable('DTOCORRENCIA', 'Data/Hora', ['width'=>"20px"], ['align'=>"left"]);

$form->AddInput('text', 'IDOCORRENCIA', 'Ocorrência', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddSelect('IDTIPOOCORRENCIA', 'Tipo Ocorrência', ['S'=>'Sim', 'N'=>'Não'], ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('IDVIGILANTE', 'Vigilante', ['S'=>'Sim', 'N'=>'Não'], ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('IDAREA', 'Área', ['S'=>'Sim', 'N'=>'Não'], ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddInput('text', 'DTOCORRENCIA', 'Data/Hora Ocorrência', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddTextArea('DSOCORRENCIA', 'Descrição', ['required'=>true], ['style'=>'max-width: 100%', 'class'=>$form->GetLargura(12)]);
$form->Show();

require 'footer.php';
?>