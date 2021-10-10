<?php


include_once('configs/config.inc.php');

$_SESSION = array();
@session_destroy();

doLogout();

network_redirect('login.php');
?>