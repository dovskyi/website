<?php

$host = "localhost";
$databasenm = "dovskyidb";
$username = "server";
$password = "!Merlin8568";

$dsn = "mysql:host=$host;dbname=$databasenm";

try {
        $db = new PDO($dsn, $username, $password);
}
catch (PDOException $error){
        echo $error->getMessage();
}

?>
