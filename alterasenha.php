<?php
session_start();
require 'classes/login.php';

$login = new Login();
$login->AltereSenha();
?>