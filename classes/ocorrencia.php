<?php

class Ocorrencia
{   
    protected $chave;
    public $TipoOcorrencia;
    public $Vigilante;
    public $PostoArea;
    public $dataOcorrencia;
    public $descricao;
    public $codStatus;
    public $codOcorrencia;

    public function __construct(
        $codOcorrencia,
        TipoOcorrencia $TipoOcorrencia,
        Vigilantes $Vigilante,
        PostoArea $PostoArea,
        $dataOcorrencia,
        $descricao,
        $codStatus
    )
    {      
        $this -> codOcorrencia = $codOcorrencia;
        $this -> TipoOcorrencia = $TipoOcorrencia;
        $this -> Vigilante = $Vigilante;
        $this -> PostoArea = $PostoArea;
        $this -> dataOcorrencia = $dataOcorrencia;
        $this -> descricao = $descricao;
        $this -> codStatus = $codStatus;  
        Database::ConnectaBD();   
    }

    public function teste(){
        return true;
    } 

    
    public function insertOcorrencia(){
        if($this-> codOcorrencia == null){
            $id = Database::BuscaMaxSequencia('ocorrencia');
            $this -> codOcorrencia = $id;
            $this -> dados =[
                "IDOCORRENCIA" => $id,
                "IDTIPOOCORRENCIA" => $this -> TipoOcorrencia ->getCodTipoOcorrencia(),
                "IDVIGILANTE" => $this -> Vigilante -> getCodVigilante(),
                "IDPOSTOAREA" => $this -> PostoArea -> getCodPostoArea(), 
                "DTOCORRENCIA" => $this -> dataOcorrencia, //data ocorencia
                "DSOCORRENCIA" => $this -> descricao,
                "TPSTATUS" => $this -> codStatus,
            ];
            Database::Insere('ocorrencia',  $this ->dados);
            return true;
        }else{
            return false;
        }
    }
    
    
    public function editOcorrencia(){
        if(!empty($this -> TipoOcorrencia ->getCodTipoOcorrencia()) || !empty($this -> Vigilante -> getCodVigilante()) 
        || !empty($this -> PostoArea -> getCodPostoArea())){
            $this -> dados =[
                "IDOCORRENCIA" => $this -> codOcorrencia,
                "IDTIPOOCORRENCIA" => $this -> TipoOcorrencia ->getCodTipoOcorrencia(),
                "IDVIGILANTE" => $this -> Vigilante -> getCodVigilante(),
                "IDPOSTOAREA" => $this -> PostoArea -> getCodPostoArea(), 
                "DTOCORRENCIA" => $this -> dataOcorrencia, //data ocorencia
                "DSOCORRENCIA" => $this -> descricao,
                "TPSTATUS" => $this -> codStatus,
            ];
            Database::Edita('ocorrencia',  $this ->dados,  ['IDOCORRENCIA'=>$this->codOcorrencia]);
            return true;
        }else{
            return false;
        }
    } 

    public function deleteOcorrencia(){
        if(!empty($this-> codOcorrencia) ){
            $id = Database::registroExiste('ocorrencia',  $this->codOcorrencia);
            if($id == $this-> codOcorrencia){
                Database::Deleta('ocorrencia',  ['IDOCORRENCIA'=>$this->codOcorrencia]);
            }else{
                return false;
            }
            return true;
        }else{
            return false;
        }
    }  

}