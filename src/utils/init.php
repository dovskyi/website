<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/conn.php';

session_start();
$_SESSION["usrname"] = $_SESSION["usrname"] ?? '';

?>
