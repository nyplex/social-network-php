<?php
session_start();
ob_start(); //Turns on output buffering

$timezone = date_default_timezone_set("Europe/London");
// Connexion to the Data Base
try {
	$connexion = new PDO('mysql:host=185.98.131.128;dbname=trave1368172_1qwtzl;charset=utf8', 'trave1368172', 'ckvcvwsukt');
}
catch(Exception $e) {
	die('Erreur : '.$e->getMessage());
}

?>
