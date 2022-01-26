<?php
include("includes/header.php");
?>

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

    <!-- ---------------------------------- CENTER COLUMN FRIEND REQUEST ----------------------------------- -->

        <div class="all_center" id="center_menu_mobile">
          <div class="main_column main_column_request">
            <div class="">
              <h4 class="request_title">Friend Request</h4><i class="fas fa-user-friends request_icon"></i>
            </div>
            <?php
            $query = $connexion->prepare('SELECT * FROM friend_requests WHERE user_to = ?');
            $query->execute(array($userLoggedIn));
            $num_row = $query->rowCount();
            if($num_row == 0) {
              echo "<p class='no_request_title'>You have no friend request!</p><hr width='50%'>";
            }else {
              while ($row = $query->fetch()) {
                $user_from = $row['user_from'];
                $user_from_obj = new User($connexion, $user_from);
                $user_from_friend_array = $user_from_obj->getFriendArray();
                $logged_in_user_obj = $user_from_obj->getMutualFriend($userLoggedIn);
                if($logged_in_user_obj <= 1) {
                  $mut_friends = " friend in common";
                }else {
                  $mut_friends = " friends in common";
                }
                echo "<div class='result_request'>
                        <div class='div_picture_request'>
                          <img src=".$user_from_obj->getProfilePic().">
                        </div>
                        <div class='details_request'>
                          <a class='name_request' href='".$user_from_obj->getUsername()."'>".$user_from_obj->getFirstAndLastName()."</a><br>
                          <a class='username_request' href='".$user_from_obj->getUsername()."'>@".$user_from_obj->getUsername()."</a>
                          <p class='friends_request'>".$logged_in_user_obj . $mut_friends ."</p>
                        </div>

                      </div>";


                if(isset($_POST['accept_request' . $user_from])) {
                  $add_friend_query = $connexion->prepare('UPDATE users SET friend_array = CONCAT(friend_array, :us) WHERE user_name = :un ');
                  $add_friend_query->execute(array(
                    'us' => $user_from . ',',
                    'un' => $userLoggedIn
                  ));
                  $add_friend_query = $connexion->prepare('UPDATE users SET friend_array = CONCAT(friend_array, :un) WHERE user_name = :uf ');
                  $add_friend_query->execute(array(
                    'un' => $userLoggedIn . ',',
                    'uf' => $user_from
                  ));
                  $delete_query = $connexion->prepare('DELETE FROM friend_requests WHERE user_to = ? AND user_from = ?');
                  $delete_query->execute(array($userLoggedIn, $user_from));
                  echo "You are now friends!";
                  header('Location: requests.php');
                }

                if(isset($_POST['ignore_request' . $user_from])) {
                  $delete_query = $connexion->prepare('DELETE FROM friend_requests WHERE user_to = ? AND user_from = ?');
                  $delete_query->execute(array($userLoggedIn, $user_from));
                  echo "Request ignored!";
                  header('Location: requests.php');
                }
                ?>
                  <form id="form_friend_request" action="requests.php" method="POST">
                    <button  type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button">Accept</button><br>
                    <button  type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button">Ignore</button>
                  </form>
                  <hr width="60%" style="border-top: 1.3px solid rgba(0, 0, 0, 0.26);">
                <?php

              }
            }
            ?>
          </div>
        </div>
