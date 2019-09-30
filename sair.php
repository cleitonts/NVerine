<?php
/*
 * link para logout
 */

include("src/services/UAC/Login.php");

$login = new \src\services\UAC\Login();
$login->sair();
