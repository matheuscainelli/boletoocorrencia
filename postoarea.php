<?php
require 'header.php';

$sql = "SELECT pa.*, po.NMPOSTO, a.NMAREA
        FROM postoarea pa
        JOIN posto po ON pa.IDPOSTO = pa.IDPOSTO
        JOIN area a ON a.IDAREA = pa.IDAREA
		WHERE TRUE";

$form = new Form(ConsultaPermissao('CAD_POSTOAREA'), "Posto Área");
$form->SetSql($sql, 'postoarea', 'IDPOSTOAREA');
$form->SetCamposSQL(['IDPOSTOAREA', 'IDPOSTO', 'IDAREA']);

$form->AddTable('IDPOSTOAREA', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMPOSTO', 'Posto', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('NMAREA', 'Área', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDPOSTOAREA', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddSelect('IDPOSTO', 'Posto', BuscaArrPosto(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->AddSelect('IDAREA', 'Área', BuscaArrArea(), ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->Show();

require 'footer.php';
?>