<?php


class TipoOcorrencia
{   

    protected $chave;
    public $codTipoOcorrencia;
    public $nomeTipoOcorrencia;
    public $AreaAbertaTipoOcorrencia;
    public $dados;

    public function __construct(
        $codTipoOcorrencia/*,
        $nomeTipoOcorrencia,
        $AreaAbertaTipoOcorrencia
    */)
    {   
        $this -> codTipoOcorrencia= $codTipoOcorrencia;
      /*  $this -> nomeTipoOcorrencia =$nomeTipoOcorrencia;
        $this -> AreaAbertaTipoOcorrencia =$AreaAbertaTipoOcorrencia; 
        */
    }
    public function getCodTipoOcorrencia() {
        return $this->codTipoOcorrencia;
    }
    
}