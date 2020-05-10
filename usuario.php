<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM usuario pa
		WHERE TRUE";

$form = new Form(TRUE, "Usuário");
$form->SetSql($sql, 'usuario', 'IDUSUARIO');
$form->SetCamposSQL(['IDUSUARIO', 'NMUSUARIO', 'SGUSUARIO', 'PWUSUARIO', 'FLATIVO']);

$form->AddTable('IDUSUARIO', 'Sequencial', ['width'=>"20px"], ['align'=>"right"]);
$form->AddTable('NMUSUARIO', 'Usuário', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('SGUSUARIO', 'Sigla', ['width'=>"40px"], ['align'=>"left"]);
$form->AddTable('FLATIVO', 'Ativo', ['width'=>"40px"], ['align'=>"left"]);

$form->AddInput('text', 'IDUSUARIO', 'Sequencial', ['readonly'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(1)]);
$form->AddInput('text', 'NMUSUARIO', 'Nome', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddInput('text', 'SGUSUARIO', 'Sigla', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddInput('password', 'PWUSUARIO', 'Senha', ['required'=>true, 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(3)]);
$form->AddSelect('FLATIVO', 'Ativo', ['S'=>'Sim', 'N'=>'Não'], ['required'=>true, 'placeholder'=>"Selecione:", 'data-width'=>"100%", 'class'=>"form-control input-sm"], ['class'=>$form->GetLargura(2)]);
$form->Show();
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('#tableCadastro').DataTable( {
			scrollY: '50vh',
			sScrollX: '100%',
    		sScrollXInner: '100%',
			scrollCollapse: true,
			paging: true,
			searching: false,
		});
	});
</script>

<?php
require 'footer.php';
?>