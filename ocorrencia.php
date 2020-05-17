<?php
require 'header.php';

$sql = "SELECT pa.*, tp.NMOCORRENCIA, vg.NMVIGILANTE, CONCAT(pt.NMPOSTO, ' - ', a.NMAREA) NMPOSTOAREA
        FROM ocorrencia pa
        JOIN tipoocorrencia tp ON pa.IDTIPOOCORRENCIA = tp.IDTIPOOCORRENCIA
        JOIN vigilante vg ON pa.IDVIGILANTE = vg.IDVIGILANTE
        JOIN postoarea po ON pa.IDPOSTOAREA = po.IDPOSTOAREA
        JOIN posto pt ON po.IDPOSTO = pt.IDPOSTO
        JOIN area a ON po.IDAREA = a.IDAREA";

$form = new Form(TRUE, "Ocorrências");
$form->SetSql($sql, 'ocorrencia', 'IDOCORRENCIA');
$form->SetCamposSQL(['IDOCORRENCIA', 'IDTIPOOCORRENCIA', 'IDVIGILANTE', 'IDPOSTOAREA', 'DTOCORRENCIA', 'DSOCORRENCIA']);
$form->HabilitaAnexo(true);

$form->AddTable('IDOCORRENCIA', 'Ocorrência', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMOCORRENCIA', 'Tipo Ocorrência', ['width'=>"20px"], ['align'=>"left"]);
$form->AddTable('NMVIGILANTE', 'Vigilante', ['width'=>"20px"], ['align'=>"left"]);
$form->AddTable('NMPOSTOAREA', 'Posto/Área', ['width'=>"20px"], ['align'=>"left"]);
$form->AddTable('DTOCORRENCIA', 'Data/Hora', ['width'=>"20px"], ['align'=>"left"]);
// $form->AddTableAction("<i title=\"Visualizar\" style=\"color: red\" class=\"fas fa-file-pdf\"></i>", "ocorrenciapdf.php?id=:IDOCORRENCIA:", true);

$form->AddInput('text', 'IDOCORRENCIA', 'Ocorrência', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddSelect('IDTIPOOCORRENCIA', 'Tipo Ocorrência', BuscaArrTipoOcorrencia(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('IDVIGILANTE', 'Vigilante', BuscaArrVigilante() , ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('IDPOSTOAREA', 'Posto/Área', BuscaArrPostoArea(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddInput('text', 'DTOCORRENCIA', 'Data/Hora Ocorrência', ['required'=>true, 'class'=>"form-control input-sm", 'value'=>GetDataHoraAtual()], ['class'=>$form->GetLargura(2)]);
$form->AddTextArea('DSOCORRENCIA', 'Descrição', ['required'=>true], ['style'=>'max-width: 100%', 'class'=>$form->GetLargura(12)]);
$form->Show();

require 'footer.php';
?>