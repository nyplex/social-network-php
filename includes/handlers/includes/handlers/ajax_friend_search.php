<?php

include("../../config/config.php");
include("../classes/User.php");

$query = $_POST['query'];
$userLoggedin = $_POST['userLoggedin'];

$names = explode(" ", $query);

if(strpos($query, "_") !== false) {
  $usersReturned = $connexion->prepare('SELECT * FROM users WHERE user_name LIKE :query AND user_closed = :no LIMIT 8');
  $usersReturned->execute(array(
    'query'=> $query . "%",
    'no'=> "no"
  ));
} else if (count($names) == 2) {
  $usersReturned = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first AND last_name LIKE :last) AND user_closed = :no LIMIT 8');
  $usersReturned->execute(array(
    'first'=>"%".$names[0]."%",
    'last'=>"%".$names[1],
    'no'=>"no"
  ));
} else {
  $usersReturned = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first OR last_name LIKE :last) AND user_closed = :no LIMIT 8');
  $usersReturned->execute(array(
    'first'=>"%".$names[0]."%",
    'last'=>"%".$names[0],
    'no'=>"no"
  ));
}
if ($query != "") {

  while($row = $usersReturned->fetch()) {
    $user = new User($connexion, $userLoggedin);



    if($user->isFriend($row['user_name']) && $userLoggedin != $row['user_name']) {
      echo "<div class='live_saerch_result'><a class='link-live-search' href='messages.php?u=".$row['user_name']."' style='color:black;text-decoration: none;'>
      <div class='live_search_picture'><img width='40px' src='".$row['profile_pic']."'></div><div class='live_search_details_content'><div class='names_live_search'><span>".$row['first_name']. " " . $row['last_name']."</span></div>
      <div class='username_live_search'><span>@".$row['user_name']."</span></div></div>
      </a></div>";
    }
  }
}
?>
