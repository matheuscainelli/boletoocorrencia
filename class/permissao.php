<?php

class Permissao {

    private function View() {
        ?>
        <div class="ibox">
            <div class="ibox-content">
                <div class="ibox-header">
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
                                    <?php foreach ($this->arrUsuariosPermissao as $arr): ?>
                                        <tr>
                                            <td>
                                                <?= HTML::AddInput('checkbox', 'FLPERMISSAO'.$arr['IDPERFIL'], $arr['NMUSUARIO'], ['data-marca'=>"FLPERMISSAO", 'value'=>'S'], ['class'=>'checkbox checkbox-success']); ?>
                                            </td>
                                            <td width="120px">
                                                <?= HTML::AddInput('checkbox', 'FLCONSULTAR'.$arr['IDPERFIL'], ' ', ['checked'=>$this->ValidaCheckbox($arr['FLCONSULTAR']), 'value'=>'S'], ['class'=>'checkbox checkbox-success']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <hr/>
                        <div class="inline-footer end">
                            <div class="ibox-buttons">
                                <a class="btn btn-primary btn-outline" href="perfilpermissao.php">Voltar</a>
                                <?= HTML::AddButton('submit', 'btnSubmit', 'Atualizar', [ 'class'=>"btn btn-primary"]); ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
?>