<?php

include("includes/header.php");

if(isset($_GET['q'])) {
  $query = $_GET['q'];
} else {
  $query = "";
}

if(isset($_GET['type'])) {
  $type = $_GET['type'];
} else {
  $type = "name";
}
?>

<div class="all_index">
  <!-- ---------------------------------- LEFT COLUMN ----------------------------------- -->
      <div class="column popular_trend left_column">
        <h3>Popular <i class="fas fa-fire-alt"></i></h3>
        <div class="trend">
          <?php
          $query_trend = $connexion->query('SELECT * FROM trends ORDER BY hits DESC LIMIT 10');
          foreach ($query_trend as $row) {
            $word = $row['title'];
            $word_dot = strlen($word) >= 14 ? "..." : "";
            $trimmed_word = str_split($word, 14);
            $trimmed_word = $trimmed_word[0];
            echo "<a href='https://www.google.co.uk/search?hl=en&q=".$word."&btnG=Recherche+Google&meta=.' target='_blank'>".$trimmed_word. $word_dot. "</a><br>";
          }
          ?>
        </div>
      </div>


      <!-- ---------------------------------- RIGHT COLUMN ----------------------------------- -->
      <div class="column popular_trend right_column">
        <h3>Friends Online <i class="fas fa-user-circle"></i></h3>
        <div class="trend trend_right">
          <?php
          // Select also all profile_pic , username and names of users online
          $user_online_query = $connexion->prepare('SELECT user_name, profile_pic, first_name, last_name FROM users WHERE login = ? AND NOT user_name = ?');
          $user_online_query->execute(array("yes", $userLoggedIn));

            while($row = $user_online_query->fetch()) {
              $userObj = New User($connexion, $userLoggedIn);
              $areWe =  $userObj->isFriend($row['user_name']);
              if($areWe) {
              $picture = $row['profile_pic'];
              $name = $row['first_name'] . " " . $row['last_name'];
              $full_name = strlen($name) >= 14 ? "..." : "";
              $trimmed_word = str_split($name, 14);
              $trimmed_word = $trimmed_word[0];
              echo "<a href='".$row['user_name']."'><img class='profile_pic_online' src='" . $picture . "'>" . $trimmed_word . $full_name . "</a><br>";
            } else {
              echo "";
            }
            }
          ?>
        </div>
      </div>


<div class="all_center" id='all_center_search_page'>
  <h3 id="title_search_page">Results <i class="fas fa-poll-h" id="icon_title_search_page"></i></h3>
  <div  id="search_page_center">
  <?php
    if($query == "") {
      echo "You must enter something in the seatch box";
    }else {
      //if query contains an underscore ,assume user is searching for username
      if($type == "username") {
        $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE user_name LIKE :user AND user_closed = :no LIMIT 8');
        $userReturnedQuery->execute(array(
          'user'=> $query . "%",
          'no'=> "no"
        ));
      }else {
        $names = explode(" ", $query);
        //if there are 3 words, assume they are first and last names
        if(count($names) == 3) {
          $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first AND last_name LIKE :last) AND user_closed = :no');
          $userReturnedQuery->execute(array(
            'first'=> $names[0] . "%",
            'last'=> $names[2] . "%",
            'no'=> "no"
          ));
        }else if(count($names) == 2) {
          $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first AND last_name LIKE :last) AND user_closed = :no');
          $userReturnedQuery->execute(array(
            'first'=> $names[0] . "%",
            'last'=> $names[1] . "%",
            'no'=> "no"
          ));
        }else {
          $userReturnedQuery = $connexion->prepare('SELECT * FROM users WHERE (first_name LIKE :first OR last_name LIKE :last) AND user_closed = :no LIMIT 8');
          $userReturnedQuery->execute(array(
            'first'=> $names[0] . "%",
            'last'=> $names[0] . "%",
            'no'=> "no"
          ));
        }
      }
      $numRow = $userReturnedQuery->rowCount();
      // Check if results were found
      if($numRow == 0) {
        echo "We can't find anyone with a " . $type . " like: " .$query;
      }else if($numRow == 1) {
        echo $numRow . " result found: <br><br>";
      } else {
        echo $numRow . " results found: <br><br>";
      }
      echo "<p>Try Searching for:</p>";
      echo "<a href='search.php?q=". $query ."&type=name'>Names</a>, <a href='search.php?q=". $query ."&type=username'>Username</a><br><br><hr>";

      while ($row = $userReturnedQuery->fetch()) {
        $user_obj = new User($connexion, $userLoggedIn);
        $button = "";
        $mutual_friends = "";
        if($userLoggedIn != $row['user_name']) {
          //Generate button depending on frienship status
          if($user_obj->isFriend($row['user_name']))
            $button = "<input style='background-color:#f54b4b;' type='submit' name='". $row['user_name'] ."' class='danger friend_button btn_results' value='Remove Friend'>";
          else if($user_obj->didReceiveRequest($row['user_name'])) {
            $button = "<input style='background-color:#ffbe2d;' type='submit' name='". $row['user_name'] ."' class='warning friend_button btn_results' value='Accept Request'>";
          } else if ($user_obj->didSendRequest($row['user_name'])) {
            $button = "<input style='background-color:#5f9dfb;' type='submit' class='default friend_button btn_results' value='Request Sent'>";
          } else {
            $button = "<input style='background-color:#58d65c;' type='submit' name='". $row['user_name'] ."' class='success friend_button btn_results' value='Add Friend'>";
          }
          $mutual_friends = $user_obj->getMutualFriend($row['user_name']);
          if($mutual_friends <= 1) {
            $mutual_friends = $mutual_friends . " friend in common";
          } else {
            $mutual_friends = $mutual_friends . " friends in common";
          }
          //Buttons form
          if(isset($_POST[$row['user_name']])) {
            if($user_obj->isFriend($row['user_name'])) {
              $user_obj->removeFriend($row['user_name']);
              header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            }else if($user_obj->didReceiveRequest($row['user_name'])) {
              header("Location: requests.php");
            }else if ($user_obj->didSendRequest($row['user_name'])) {

            }else {
              $user_obj->sendRequest($row['user_name']);
              header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            }
          }
        }
        echo "<div class='main_column' id='main_column_search_page'>
                <div class='searchPageFriendButton'>
                  <form id='form_search_page' action='' method='POST'>
                    ". $button ." <br>
                  </form>
                </div>
                <div class='result_profile_pic'>
                  <a href='". $row['user_name'] ."'><img id='picture_search_page' src='". $row['profile_pic'] ."'></a>
                </div>
                <a href='". $row['user_name'] ."'>
                  <span class='para_names'>". $row['first_name'] ." ". $row['last_name'] ."</span><br>
                  <span class='para_username'>@". $row['user_name'] ."</span><br>
                </a>
                  <span class='para_mutual'>". $mutual_friends ." </span><br>
                <br>
              </div>
              <br>
              <hr>";
      } // End while loop
    } //end of else statment
    echo "No more result!";
  ?>
  </div>
</div>
