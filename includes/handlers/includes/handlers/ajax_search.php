<?php

include("../../config/config.php");
include("../../includes/classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

//if query contains an underscore ,assume user is searching for username
if(strpos($query, '_') !== false) {
  $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE user_name LIKE :user AND user_closed = :no AND NOT user_name = :myuser LIMIT 8');
  $userReturnedQuery->execute(array(
    'user'=> $query . "%",
    'no'=> "no",
    'myuser'=> $userLoggedIn
  ));
}

//if there are 2 words, assume they are first and last names
else if(count($names) == 2) {
  $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first AND last_name LIKE :last) AND user_closed = :no LIMIT 8');
  $userReturnedQuery->execute(array(
    'first'=> $names[0] . "%",
    'last'=> $names[1] . "%",
    'no'=> "no"
  ));
}

// if only 1 word, search for first and last names
else {
  $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first OR last_name LIKE :last) AND user_closed = :no AND NOT user_name = :myuser LIMIT 8');
  $userReturnedQuery->execute(array(
    'first'=> $names[0] . "%",
    'last'=> $names[0] . "%",
    'no'=> "no",
    'myuser'=> $userLoggedIn
  ));
}

if($query != "") {
  while($row = $userReturnedQuery->fetch()) {
    $user = new User($connexion, $userLoggedIn);
    if($row['user_name'] != $userLoggedIn) {
      $mutualFriends = $user->getMutualFriend($row['user_name']) . " friends in common";
    }else {
      $mutualFriends = "";
    }



    echo "<div class='live_search_result'>
    <a href='". $row['user_name'] ."' id='link_live_saerch'>
      <div class='image_live_search'>
        <img width='55px' src='" .$row['profile_pic']. "' >
      </div>
      <div class='side_live_search'>
        <div class='names_live_search'>
          <span>". $row['first_name'] ." ". $row['last_name'] ."</span>
        </div>
        <div class='username_live_search'>
          <span>@". $row['user_name'] ."</span>
        </div>
        <div class='mutual_friend_live_search'>
          <span>". $mutualFriends ."</span>
        </div>
      </div>
    </a>
    </div>";
  }
}
?>
