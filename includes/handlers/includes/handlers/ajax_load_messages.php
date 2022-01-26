<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/Message.php");

$limit = 150;

$message = new Message($connexion, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropdown($_REQUEST, $limit);
?>
