<?php
require "../../config/config.php";


$query_logout = $connexion->prepare('UPDATE users SET login = ? WHERE user_name = ?');
$query_logout->execute(array("no", $_SESSION['username']));

session_destroy();
header('Location: ../../register.php');

?>
