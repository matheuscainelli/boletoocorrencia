<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM tipoocorrencia pa
		WHERE TRUE";

$form = new Form(ConsultaPermissao('CAD_TIPOOCORRENCIA'), "Tipo Ocorrência");
$form->SetSql($sql, 'tipoocorrencia', 'IDTIPOOCORRENCIA');
$form->SetCamposSQL(['IDTIPOOCORRENCIA', 'NMOCORRENCIA', 'FLAREAABERTA']);

$form->AddTable('IDTIPOOCORRENCIA', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMOCORRENCIA', 'Nome', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('FLAREAABERTA', 'Área Aberta', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDTIPOOCORRENCIA', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMOCORRENCIA', 'Nome', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->addSelect('FLAREAABERTA', 'Área Aberta', ['N'=>'Não', 'S'=>'Sim'], ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->Show();

require 'footer.php';
?>