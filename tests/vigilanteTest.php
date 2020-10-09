<?php

include_once __DIR__.'/../classes/vigilante.php';
include_once __DIR__.'/../classes/form.php';
//namespace  boletoocorencia\classes;

use PHPUnit\Framework\TestCase;


class vigilanteTest extends TestCase {
    /** @test */ 
    public function isAllowedToOrder()
    {
    $subjec = new Vigilantes(null,1 );
        
        $isvalid = $subjec-> insert();

        $this->assertEquals( 3, $isvalid);
    }

}