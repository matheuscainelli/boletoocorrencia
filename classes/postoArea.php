<?php

class PostoArea
{   
    protected $chave;
    public $codPostoArea;
    public $codPosto;
    public $codArea;
    public $codCampus;
    public $dados;

    public function __construct(
        $codPostoArea/*,
        $codPosto,
        $codArea,
        $codCampus*/
    )
    {   
        $this -> codPostoArea = $codPostoArea;
        /*$this -> codPosto = $codPosto;
        $this -> codArea = $codArea;
        $this -> codCampus = $codCampus; 
        Database::ConnectaBD();   
    */
    }
    public function getCodPostoArea() {
        return $this->codPostoArea;
    }
}