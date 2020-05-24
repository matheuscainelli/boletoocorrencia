<?php
require 'header.php';
$arrPerfilPermissao = GetPerfilPermissao($_GET['id']);
$nmPerfil = GetNomePerfil($_GET['id']);

if (array_key_exists('btnSubmit', $_POST)) {
    Database::Deleta('perfilpermissao', ['IDPERFIL'=>$_GET['id']]);
    $seqPerfilPermissao = Database::BuscaMaxSequencia('perfilpermissao');

    foreach ($arrPerfilPermissao as $arr) {
        $idPermissao = $arr['IDPERMISSAO'];
        $flConsultar = $_POST['FLCONSULTAR'.$idPermissao] ?? 'N';

        Database::Insere('perfilpermissao', ['IDPERFILPERMISSAO'=>$seqPerfilPermissao,
                                             'IDPERFIL'=>$_GET['id'],
                                             'IDPERMISSAO'=>$idPermissao,
                                             'FLCONSULTA'=>$flConsultar]);

        $seqPerfilPermissao++;

        return header('Location: perfil.php');
    }
}
?>
<script src="js/perfilpermissao.js"></script>
<div class="ibox">
    <div class="ibox-content">
        <div class="ibox-header">
            <span>Permissões - <?= $nmPerfil ?></span>
        </div>
        <div class="ibox-body">
            <form name="form" id="formPermissaoUsuario" class="inline-content" action="" method="POST">
                <div class="inline-body">
                    <table class="registros table table-striped table-bordered table-hover table-noborder">
                        <thead>
                            <tr>
                                <th>
                                    <?= HTML::AddInput('checkbox', 'FLTODOS', '<b>Permissão</b>', ['data-marca'=>"FL"], ['class'=>'checkbox checkbox-success']); ?>
                                </th>
                                <th>
                                    <?= HTML::AddInput('checkbox', 'FLCONSULTARTODOS', '<b>Consultar</b>', ['data-marca'=>"FLCONSULTAR"], ['class'=>'checkbox checkbox-success']); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($arrPerfilPermissao as $arr): ?>
                                <tr>
                                    <td>
                                        <?= HTML::AddInput('checkbox', 'FLPERMISSAO'.$arr['IDPERMISSAO'], $arr['NMPERMISSAO'], ['data-marca'=>"FLPERMISSAO", 'value'=>'S'], ['class'=>'checkbox checkbox-success']); ?>
                                    </td>
                                    <td width="120px">
                                        <?= HTML::AddInput('checkbox', 'FLCONSULTAR'.$arr['IDPERMISSAO'], ' ', ['checked'=>ValidaCheckbox($arr['FLCONSULTA']), 'value'=>'S'], ['class'=>'checkbox checkbox-success']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <hr/>
                <div class="inline-footer end">
                    <div class="ibox-buttons">
                        <a class="btn btn-primary btn-outline" href="perfil.php">Voltar</a>
                        <?= HTML::AddButton('submit', 'btnSubmit', 'Atualizar', [ 'class'=>"btn btn-primary"]); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
require 'footer.php';
?>