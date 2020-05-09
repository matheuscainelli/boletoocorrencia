<?php
require 'header.php';

$sql = "SELECT pa.*
        FROM usuario pa";

$form = new Form(TRUE, "Usuário");
$form->setDB($sql, 'IDUSUARIO');

$form->Show();
require 'footer.php';
?>