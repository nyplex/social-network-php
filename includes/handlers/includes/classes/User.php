<?php

class User {
  private $user;
  private $connexion;

  public function __construct($connexion, $user){
    $this->connexion = $connexion;
    $user_details_query = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
    $user_details_query->execute(array($user));
    $this->user = $user_details_query->fetch();
  }

  public function getUsername() {
    return $this->user['user_name'];
  }

  public function getFirstName() {
    return $this->user['first_name'];
  }

  public function getNumberOfFriendRequests() {
    $username = $this->user['user_name'];
    $query = $this->connexion->prepare('SELECT * FROM friend_requests WHERE user_to = ?');
    $query->execute(array($username));
    $numRow = $query->rowCount();
    return $numRow;
  }

  public function getFirstAndLastName() {
    $username = $this->user['user_name'];
    $query = $this->connexion->prepare('SELECT first_name, last_name FROM users WHERE user_name = ?');
    $query->execute(array($username));
    $row = $query->fetch();
    return $row['first_name'] . " " . $row['last_name'];
  }

  public function getNumPosts() {
    $username = $this->user['user_name'];
    $query = $this->connexion->prepare('SELECT num_posts FROM users WHERE user_name = ?');
    $query->execute(array($username));
    $result_query = $query->fetch();
    return $result_query['num_posts'];
  }

  public function isClosed() {
    $username = $this->user['user_name'];
    $query = $this->connexion->prepare('SELECT user_closed FROM users WHERE user_name = ?');
    $query->execute(array($username));
    $row = $query->fetch();
    if ($row['user_closed'] == "yes") {
      return true;
    }else{
      return false;
    }
  }

  public function isFriend($username_to_check) {
    $usernameComma = "," . $username_to_check . ",";
    if((strstr($this->user['friend_array'], $usernameComma) || $username_to_check == $this->user['user_name'])){
      return true;
    } else {


      return false;
    }
  }

  public function getProfilePic() {
    $username = $this->user['user_name'];
    $query = $this->connexion->prepare('SELECT profile_pic FROM users WHERE user_name = ?');
    $query->execute(array($username));
    $result_query = $query->fetch();
    return $result_query['profile_pic'] ;
  }

  public function didReceiveRequest($user_from) {
    $user_to = $this->user['user_name'];
    $check_request_query = $this->connexion->prepare('SELECT * FROM friend_requests WHERE user_to = ?  AND user_from = ?');
    $check_request_query->execute(array($user_to, $user_from));
    $result_check_request_query = $check_request_query->rowCount();
    if($result_check_request_query > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function didSendRequest($user_to) {
    $user_from = $this->user['user_name'];
    $check_request_query = $this->connexion->prepare('SELECT * FROM friend_requests WHERE user_to = ?  AND user_from = ?');
    $check_request_query->execute(array($user_to, $user_from));
    $result_check_request_query = $check_request_query->rowCount();

    if($result_check_request_query > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function removeFriend($user_to_remove) {
    $logged_in_user = $this->user['user_name'];

    $query = $this->connexion->prepare('SELECT friend_array FROM users WHERE user_name = ?');
    $query->execute(array($user_to_remove));
    $row = $query->fetch();
    $friend_array_username = $row['friend_array'];

    $new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_array']);
    $remove_friend = $this->connexion->prepare('UPDATE users SET friend_array = ? WHERE user_name = ?');
    $remove_friend->execute(array($new_friend_array, $logged_in_user));

    $new_friend_array = str_replace($this->user['user_name'] . ",", "", $friend_array_username);
    $remove_friend = $this->connexion->prepare('UPDATE users SET friend_array = ? WHERE user_name = ?');
    $remove_friend->execute(array($new_friend_array, $user_to_remove));

  }

  public function sendRequest($user_to) {
    $user_from = $this->user['user_name'];
    $query = $this->connexion->prepare('INSERT INTO friend_requests (user_to, user_from) VALUES(?, ?)');
    $query->execute(array($user_to, $user_from));
  }

  public function getFriendArray() {
    $username = $this->user['user_name'];
    $query = $this->connexion->prepare('SELECT friend_array FROM users WHERE user_name = ?');
    $query->execute(array($username));
    $result_query = $query->fetch();
    return $result_query['friend_array'];
  }

  public function getMutualFriend($user_to_check) {
    $mutualFriends = 0;
    $user_array = $this->user['friend_array'];
    $user_array_explode = explode(",", $user_array);

    $query = $this->connexion->prepare('SELECT friend_array FROM users WHERE user_name = ?');
    $query->execute(array($user_to_check));
    $row = $query->fetch();
    $user_to_check_array = $row['friend_array'];
    $user_to_check_array_explode = explode(",", $user_to_check_array);
    foreach($user_array_explode as $i) {
      foreach($user_to_check_array_explode as $j){
        if($i == $j && $i != "") {
          $mutualFriends++;
        }
      }
    }
    return $mutualFriends;
  }




}

?>
