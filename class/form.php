<?php
class Form {
    public $permissao;
    public $action;
    public $acao;
    public $id;
    public $sql;
    public $nmTbela;
    public $chave;
    public $arrDados = [];
    public $attrInput;
    protected $arrLabelAcao = ['c'=>'Incluir', 'r'=>'Consultar', 'u'=>'Alterar', 'd'=>'Excluir'];
    protected $GetDados = [];
    protected $arrTableAction = [];
    protected $arrCamposSQL = [];
    protected $arrValidaPermissao = ['c'=>true, 'r'=>true, 'u'=>true, 'd'=>true];
    private $arrBinds = [];
    private $HabilitaAnexo = false;
    private $tabelaAnexo = null;
    private $chaveAnexo = null;

    function __construct($permissao, $legend) {
        if (!$permissao) {
            $this->MostraMensagemPermissao();
        }

        Database::ConnectaBD();
        $this->permissao = $permissao;
        $this->legend = $legend;
        $this->method = 'POST';
        $this->setAction($_SERVER['PHP_SELF']);
        $this->acao = array_key_exists('acao', $_GET) ? $_GET['acao'] : NULL;
        $this->id = array_key_exists('id', $_GET) ? $_GET['id'] : NULL;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function SetSql($sql, $tabela, $chave) {
        $this->sql = $sql;
        $this->nmTabela = $tabela;
        $this->chave = $chave;
    }

    public function GetDados() {
        return Database::ExecutaSQLDados($this->sql, array());
    }

    public function SetDados() {
        if ($this->acao <> 'c') {
            if (array_key_exists('id', $_GET)) {
                $this->sql = $this->sql . " AND " . $this->chave. " = ". $this->id;
            }
    
            $this->arrDados = Database::ExecutaSqlDados($this->sql, $this->arrBinds);

            if (array_key_exists(0, $this->arrDados)) {
                $this->arrDados = $this->arrDados[0];
            }
        }
    }

    public function AddInput($type, $name, $caption = null, $attrInput = [], $attrDiv = [], $attrLabel = []) {
        $this->SetDados();
        if (!empty($this->arrDados[$name])) {
            if ($type === 'checkbox') {
                $attrInput['checked'] = $this->arrDados[$name] === 'S';
            } else {
                $attrInput['value'] = FormataValorUsuario(substr($name, 0 ,2), $this->arrDados[$name]);
            }
        }

        if (in_array($this->acao, ['r', 'd'])) {
            $attrInput['disabled'] = true;
        }

        $arr = [
            'method'=>'AddInput',
            'arg'=>[$type, $name, $caption, $attrInput, $attrDiv, $attrLabel]
        ];

        if (!isset($this->viewForm)) {
            $this->arrForm[] = $arr;
        } else {
            return forward_static_call_array(["HTML", $arr['method']], $arr['arg']);
        }
    }

    public function AddSelect($name, $caption = null, $arrOption = [], $attrSelect = [], $attrDiv = [], $attrLabel = []) {
        $this->SetDados();
        $selected = $this->arrDados[$name] ?? null;

        if (in_array($this->acao, ['r', 'd'])) {
            $attrSelect['disabled'] = true;
        }

        $arr = [
            'method'=>'AddSelect',
            'arg'=>[$name, $caption, $selected, $arrOption, $attrSelect, $attrDiv, $attrLabel]
        ];

        if (!isset($this->viewForm)) {
            $this->arrForm[] = $arr;
        } else {
            return forward_static_call_array(["HTML", $arr['method']], $arr['arg']);
        }
    }

    public function AddTextArea($name, $caption = null, $attrTextArea = [], $attrDiv = [], $attrLabel = []) {
        $this->SetDados();
        $attrTextArea['value'] = $this->arrDados[$name] ?? NULL;

        if (in_array($this->acao, ['r', 'd'])) {
            $attrTextArea['disabled'] = true;
        }

        $arr = [
            'method'=>'AddTextArea',
            'arg'=>[$name, $caption, $attrTextArea, $attrDiv, $attrLabel]
        ];

        if (!isset($this->viewForm)) {
            $this->arrForm[] = $arr;
        } else {
            return forward_static_call_array(["HTML", $arr['method']], $arr['arg']);
        }
    }

    protected function AddHTML($html) {
        $this->arrForm[] = [
            'method'=>'AjustaHTMLDados',
            'arg'=>[$html, []]
        ];
    }

    public function GetLargura($largura = NULL) {
        return HTML::GetLargura($largura);
    }

    private function Table() {
        $dados = $this->GetDados();

        $arrIconActionTable = [
            'r'=>'fa-search',
            'u'=>'fa-pencil',
            'd'=>'fa-trash',
        ];

        foreach ($this->arrValidaPermissao as $tpPermissao => $flPermissao) {
            if ($tpPermissao == 'c') {
                continue;
            }

            if (in_array($tpPermissao, array('u', 'd')) && Session::Get('IDPERFIL') == 2) {
                continue;
            }

            $this->AddTableAction("<i class=\"fa ".$arrIconActionTable[$tpPermissao]." icon-form\" title=\"".$this->arrLabelAcao[$tpPermissao]."\"></i>", $this->action."?acao=$tpPermissao&id=:$this->chave:", $flPermissao);
        }

        ?>
        <div>
            <div class="ibox">
                <div class="ibox-content">
                    <div class="ibox-header">
                        <span class="ibox-title"><?= $this->legend; ?></span>
                        <div class="ibox-buttons">
                            <a class="btn btn-primary btn-sm" title="Incluir" href="<?= $this->action?>?acao=c">
                                <i class="fas fa-plus-circle"></i>&nbsp;Novo registro
                            </a>
                        </div>
                    </div>
                    <div class="ibox-body">
                        <div class="inline-content">
                            <div class="inline-body">
                                <div class="table-responsive">
                                    <table id="tableCadastro" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <?php foreach ($this->arrTable as $arr): ?>
                                                    <th <?= HTML::MontaAttrHtml($arr['arg']['attrTh']); ?> ><?= $arr['arg']['caption']; ?></th>
                                                <?php endforeach; ?>
                                                    <th class="no-sort" width="1px">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados as $arrVal): ?>
                                                <tr>
                                                    <?php
                                                        foreach ($this->arrTable as $arr): ?>
                                                        <td <?= HTML::MontaAttrHtml($arr['arg']['attrTd'])?>>
                                                            <?= array_key_exists($arr['name'], $arrVal) ? FormataValorUsuario(substr($arr['name'], 0, 2), $arrVal[$arr['name']]) : forward_static_call_array(["HTML", 'AjustaHTMLDados'], [$arr['name'], $arrVal]); ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                        <td>
                                                            <div class="btn-group">
                                                                <?php foreach ($this->arrTableAction as $arr): ?>
                                                                    <a class="btn-white btn btn-xs" href="<?= forward_static_call_array(["HTML", $arr['method']], [$arr['href'], $arrVal]); ?>">
                                                                        <?= $arr['caption']; ?>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


    public function Cadastro() {
        ?>
        <div>
            <div class="ibox">
                <div class="ibox-content">
                    <div class="ibox-header">
                        <span class="ibox-title"><?= $this->legend; ?></span>
                    </div>
                    <div class="ibox-body">
                        <form name="form" id="formCadastro" class="inline-content" action="<?= $this->action?>?acao=<?= $this->acao ?>&id=<?=$this->id?>" method="POST" enctype = "multipart/form-data">
                            <div class="inline-body">
                                <?php
                                foreach ($this->arrForm as $arr):
                                    echo forward_static_call_array(["HTML", $arr['method']], $arr['arg']);
                                endforeach;
                                ?>
                            </div>
                            <hr/>
                            <div class="inline-footer">
                                <span class="legenda-obrigatorio"><span>*</span>&nbsp;Campos de preenchimento obrigatório</span>
                                <div class="ibox-buttons">
                                    <a class="btn btn-primary btn-outline"  href="<?= $this->action?>">Voltar</a>
                                    <?php
                                        if ($this->acao <> 'r'):
                                            if ($this->HabilitaAnexo):
                                                echo HTML::AddInput('file', 'ANEXOOCORRENCIA[]', null, ['multiple'=>true, 'style'=>'display:none', 'accept'=>"image/*"]);
                                                echo HTML::AddButton('button', 'btnAnexoOcorrencia', "<i class='far fa-paperclip'></i>&nbsp;Anexos", ['class'=>'btn btn-primary', 'onclick'=>"$('#btnAnexoOcorrencia').siblings(':file').click()"]);
                                            endif;
                                            echo  HTML::AddButton('submit', 'btnSubmit', $this->arrLabelAcao[$this->acao], ['class'=>'btn btn-primary']);
                                        endif;
                                    ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function AddTable($name, $caption, $attrTh = [], $attrTd = []) {
        $this->arrTable[] = [
            'name'=>$name,
            'arg'=>[
                'caption'=>$caption,
                'attrTh'=>$attrTh,
                'attrTd'=>$attrTd
            ]
        ];
    }

    public function AddTableAction($caption, $href, $flPermissao = true) {
        if ($flPermissao) {
            $this->arrTableAction[] = [
                'method'=>'AjustaHTMLDados',
                'caption'=>$caption,
                'href'=>$href
            ];
        }
    }

    public function SetCamposSQL($arrCamposSQL) {
        $this->arrCamposSQL = $arrCamposSQL;
    }

    private function AjustaCamposSQL() {
        $dados = [];

        foreach ($this->arrCamposSQL as $key => $value) {
            if (is_numeric($key)) {
                $dados[$value] = $_POST[$value] ?? null;
            } else if ($this->acao == 'c') {
                $dados[$key] = $value;
            }
        }

        return $dados;
    }

    public function HabilitaAnexo($habilita, $tabelaanexo, $chaveanexo) {
        $this->HabilitaAnexo = $habilita;
        $this->tabelaAnexo = $tabelaanexo;
        $this->chaveAnexo = $chaveanexo;
    }

    private function InsereAnexo($id) {
        $arrArquivos = $_FILES['ANEXOOCORRENCIA'];
        Database::Deleta($this->tabelaAnexo, [$this->chaveAnexo=>$this->id]);
        $sequencial = Database::BuscaMaxSequencia($this->tabelaAnexo);

        for ($i = 0; $i < count($arrArquivos); $i++) {
            if (array_key_exists($i, $arrArquivos['name']) && $arrArquivos['name'][$i] <> NULL) {
                if ($arrArquivos['size'][$i] < 10000) { // Valida tamanho do arquivo, conforme documentação
                    $nmtemporario = $arrArquivos['tmp_name'][$i];
                    $dsconteudo = file_get_contents($nmtemporario);
                    $dados = array($this->chaveAnexo=>$sequencial,
                                  'IDOCORRENCIA'=>$id,
                                  'DSARQUIVO'=>base64_encode(gzcompress($dsconteudo)),
                                  'NRTAMANHOARQUIVO'=>$arrArquivos['size'][$i],
                                  'NMARQUIVO'=>$arrArquivos['name'][$i],
                                  'TPANEXO'=>$arrArquivos['type'][$i]);
                    Database::Insere($this->tabelaAnexo, $dados);
                    $sequencial++;
                }
            }
        }

        unset($arrArquivos);
    }

    private function MostraMensagemPermissao() {
        $str = '';
        $str .= "<div class='wrapper wrapper-content'>";
            $str .= "<div class='error-permissao middle-box text-center animated fadeInRightBig'>";
                $str .= "<h3 class='font-bold'>Acesso negado</h3>";
                $str .= "<div class='error-desc'>";
                    $str .= "Voce não possui permissão para acessar essa página.";
                    $str .= "<br><a href='index.php' class='btn btn-primary m-t'>Voltar</a>";
                $str .= "</div>";
            $str .= "</div>";
        $str .= "</div>";
 
        die($str);
    }

    public function Show() {
       $this->SetDados();
        if (array_key_exists('btnSubmit', $_POST)) {
            $dados = $this->AjustaCamposSQL();

            switch ($_GET['acao']) {
                case 'c':
                    $id = Database::BuscaMaxSequencia($this->nmTabela);
                    $dados[$this->chave] = $id;
                    Database::Insere($this->nmTabela, $dados);
                    break;
                case 'u':
                    Database::Edita($this->nmTabela, $dados, [$this->chave=>$this->id]);
                    break;
                case 'd':
                    Database::Deleta($this->nmTabela, [$this->chave=>$this->id]);
                    break;
            }

            if (!empty($_FILES['ANEXOOCORRENCIA']['name'][0] <> NULL)) {
                $this->InsereAnexo($id);
            }

            header('location:'.$this->action);
        }

        if (!array_key_exists('acao', $_GET)) {
            $this->Table();
        } else if (array_key_exists('acao', $_GET)) {
            $this->Cadastro();
        }
    }
}
?>