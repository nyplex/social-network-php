<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/Notification.php");

$limit = 150;

$notification = new Notification($connexion, $_REQUEST['userLoggedIn']);
echo $notification->getNotifications($_REQUEST, $limit);
?>
