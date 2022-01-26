<?php

include '../../config/config.php';

$userLoggedIn = $_POST['me'];
$user_to = $_POST['friend'];

$get_last_messages_query = $connexion->prepare('SELECT id FROM messages WHERE (user_to = :userto AND user_from = :userfrom) OR (user_from = :userf AND user_to = :userto) ORDER BY id DESC LIMIT 1');
$get_last_messages_query->execute(array(
  'userto'=> $userLoggedIn,
  'userfrom'=> $user_to,
  'userf'=> $userLoggedIn,
  'userto'=> $user_to
));

$my_last_message = $connexion->prepare('SELECT id, opened FROM messages WHERE user_from = ? AND user_to = ? ORDER BY id DESC LIMIT 1');
$my_last_message->execute(array($userLoggedIn, $user_to));

$friend_last_message = $connexion->prepare('SELECT id FROM messages WHERE user_to = ? AND user_from = ? ORDER BY id DESC LIMIT 1');
$friend_last_message->execute(array($userLoggedIn, $user_to));

$row1 = $my_last_message->fetch();
$row2 = $friend_last_message->fetch();
$row3 = $get_last_messages_query->fetch();



if($row1['id'] > $row2['id'] && $row1['opened'] === "yes" && $row1['id'] === $row3['id'])
	echo "<div class='checkSeen'>Seen</div>";
