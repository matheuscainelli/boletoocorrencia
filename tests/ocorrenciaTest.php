<?php

include_once __DIR__.'/../classes/vigilante.php';
include_once __DIR__.'/../classes/ocorrencia.php';
include_once __DIR__.'/../classes/postoArea.php';
include_once __DIR__.'/../classes/tipoOcorrencia.php';
//namespace  boletoocorencia\classes;

use PHPUnit\Framework\TestCase;


class ocorrenciaTest extends TestCase {
    
     /** @test */
     public function testInclusao() {
        $vigilante = new Vigilantes(2 ,"Teste" );
         $postoArea = new PostoArea(10);
         $tipoOcorrencia = new TipoOcorrencia(5);    
         $ocorrencia = new Ocorrencia(null, $tipoOcorrencia, $vigilante, $postoArea, "2020-10-18 17:24:33", "teste entrada inclusão","E");
        
         $isvalid = $ocorrencia-> insertOcorrencia();
         $this->assertEquals( true, $isvalid);    
    }

    /** @test */
    public function testEdicao() {
        $vigilante = new Vigilantes(2 ,"Teste 2" );
         $postoArea = new PostoArea(10);
         $tipoOcorrencia = new TipoOcorrencia(5);    
         $ocorrencia = new Ocorrencia(5, $tipoOcorrencia, $vigilante, $postoArea, "2020-10-18 17:24:33", "teste entrada edição 2","E");
        
         $isvalid = $ocorrencia-> editOcorrencia();
        $this->assertEquals( true, $isvalid); 
    }

    /** @test */ 
    public function testExclusao() {
    $vigilante = new Vigilantes(1 ,"teste 1" );
    $postoArea = new PostoArea(1);
    $tipoOcorrencia = new TipoOcorrencia(5);    
    $ocorrencia = new Ocorrencia(8, $tipoOcorrencia, $vigilante, $postoArea, null, null,null);
   
    $isvalid = $ocorrencia-> deleteOcorrencia();

        $this->assertEquals( false, $isvalid);
    
    }



}