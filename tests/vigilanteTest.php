<?php

include_once __DIR__.'/../classes/vigilante.php';
include_once __DIR__.'/../classes/form.php';
//namespace  boletoocorencia\classes;

use PHPUnit\Framework\TestCase;


class vigilanteTest extends TestCase {
    /*
    
    public function inserirvigilante()
    {
    $subjec = new Vigilantes(1,1 );
        
        $isvalid = $subjec-> insert();

        $this->assertEquals( 3, $isvalid);
    }*/

    /** @test */ 
    public function editVigilante()
    {
    $subjec = new Vigilantes(4 ,"sss" );
        
        $isvalid = $subjec-> deleteVigilante();

        $this->assertEquals( 3, $isvalid);
    }

}