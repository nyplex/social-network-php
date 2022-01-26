<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; // number of post limited per extension_loaded

$post = new Post($connexion, $_REQUEST['userLoggedIn']);
$post->loadPostFriends($_REQUEST, $limit);
?>
