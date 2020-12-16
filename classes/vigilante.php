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
    
    public function getNomeVigilante() {
        return $this->nomeVigilante;
    }
      
    public function setNomeVigilante($name) {
        $this->nomeVigilante= $name;
    }

    public function getCodVigilante() {
        return $this->codVigilante;
    }
      
    public function setCodVigilante($codigo) {
        $this->codVigilante= $codigo;
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
            return true;
        }else{
            return false;
        }
    }
    
    public function editVigilante(){
        if(!empty($this-> codVigilante) && !empty($this-> nomeVigilante) ){
            $this -> dados =[
                "IDVIGILANTE" => $this -> codVigilante,
                "NMVIGILANTE" => $this -> nomeVigilante,
            ];
            Database::Edita('vigilante',  $this ->dados,  ['IDVIGILANTE'=>$this->codVigilante]);
            return true;
        }else{
            return false;
        }
    } 

    public function deleteVigilante(){
        if(!empty($this-> codVigilante) && !empty($this-> nomeVigilante) ){
            $id = Database::registroExiste('vigilante',  $this->codVigilante);
            if($id == $this-> codVigilante){
                Database::Deleta('vigilante',  ['IDVIGILANTE'=>$this->codVigilante]);
            }else{
                return false;
            }
            return true;
        }else{
            return false;
        }
    }  
    

    
}