<?php
session_start();
ob_start(); //Turns on output buffering

$timezone = date_default_timezone_set("Europe/London");
// Connexion to the Data Base
try {
	$connexion = new PDO('mysql:host=localhost;dbname=the_social_network;charset=utf8;port=3308', 'root', '');
}
catch(Exception $e) {
	die('Erreur : '.$e->getMessage());
}

?>
