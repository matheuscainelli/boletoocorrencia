<?php

require 'classes/html.php';
require 'config/config.php';
require 'classes/database.php';
require 'classes/session.php';
require 'classes/form.php';

class Vigilantes
{   
    
    protected $sql = "SELECT pa.*
    FROM vigilante pa
    WHERE TRUE";

    protected $chave;
    public $codVigilante;
    public $nomeVigilante;
    protected $arrCamposSQL = [];
    public $dados;

    public function __construct(
        $codVigilante,
        $nomeVigilante
    )
    {   
        $this -> codVigilante= $codVigilante;
        $this -> nomeVigilante =$nomeVigilante;
        Database::ConnectaBD();   
    }
    

    public function insert(){
        if($this-> codVigilante == null){
            $id = Database::BuscaMaxSequencia('vigilante');
            $this -> codVigilante = $id;
            $this -> dados =[
                "IDVIGILANTE" => $id,
                "NMVIGILANTE" => $this -> nomeVigilante,
            ];
            Database::Insere('vigilante',  $this ->dados);
            return $id;
        }
    /* $dados[$this->chave] = $id;
        Database::Insere($this->nmTabela, $dados);
        //$this->formclass-> show();
        if (1==1) {
            return $id;
        } else {
            return $id;
        }*/
    }
    public function edit(){
       // Database::Edita($this->nmTabela, $dados, [$this->chave=>$this->id]);
    
    }  
        
    
}