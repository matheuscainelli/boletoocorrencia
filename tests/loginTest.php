<?php

//require 'vendor/autoload.php';
include_once __DIR__.'/../classes/login.php';
include_once __DIR__.'/../classes/database.php';
//namespace  boletoocorencia\classes;

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase {

    /**
     * @test
     * @dataProvider customerLoginDataProvider
     */
    public function isAllowedToOrder($login, $password, $expectedAlloed)
    {
        $subjec = new Login();
        
        $_POST = array(
            'login' => $login,
            'password' => $password
         );
        $isvalid = $subjec-> login();

        $this->assertEquals($expectedAlloed, $isvalid);
    }

    public function customerLoginDataProvider()
    {
        return [
            'Usuariosemlogin' => [
                'login' => '',
                'password' => 'admin1234',
                'expectedAllowed' => false
            ],
            'UsuariosemSenha' => [
                'login' => 'admin',
                'password' => '',
                'expectedAllowed' => false
            ],
            'UsuarioComSenhaErrada' => [
                'login' => 'admin',
                'password' => 'admin123',
                'expectedAllowed' => false
            ],
            'UsuarioCorreto' => [
                'login' => 'admin',
                'password' => 'admin1234',
                'expectedAllowed' => true
            ]
        ];
    }



    /**
     * @test
     * @dataProvider customerchangePassDataProvider
     */
    public function isAllowedChangePass($login, $password, $newpassword ,$expectedAlloed)
    {
        $subjec = new Login();
        
        $_POST = array(
            'login' => $login,
            'password' => $password,
            'passwordnew' => $newpassword
         );

        $isvalid = $subjec-> alteraSenhaDB();

        $this->assertEquals($expectedAlloed, $isvalid);
    }

    public function customerchangePassDataProvider()
    {
        return [
            'NaoAlteraSenhaFaltaUsuario' => [
                'login' => '',
                'password' => 'admin1234',
                'passwordnew' => 'admin123',
                'expectedAllowed' => false
            ],
            'NaoAlteraSenhaOriginal' => [
                'login' => 'admin',
                'password' => '',
                'passwordnew' => 'admin123',
                'expectedAllowed' => false
            ],
            'AlteraSenhaOriginal' => [
                'login' => 'admin',
                'password' => 'admin1234',
                'passwordnew' => 'admin123',
                'expectedAllowed' => true
            ],
            'VoltaSenhaOriginal' => [
                'login' => 'admin',
                'password' => 'admin123',
                'passwordnew' => 'admin1234',
                'expectedAllowed' => true
            ]
        ];
    }
}