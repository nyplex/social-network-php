<?php
include("includes/header.php");

if(isset($_POST['cancel'])) {
  header("Location: settings.php");
}
if(isset($_POST['close_account'])) {
  $close_query = $connexion->prepare('UPDATE users SET user_closed = ? WHERE user_name = ?');
  $close_query->execute(array("yes", $userLoggedIn));
  session_destroy();
  header("Location: register.php");
}

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


    <div class="all_center settings_page_div" id='all_center_search_page'>
      <h4 class="setting_title ">Close Account <i id="icon_close_account" class="fas fa-radiation-alt"></i></h4>
      <div id="search_page_center">
        <h6>Are you sure you want to close your account ?</h6><br><br>
        <p>Closing your account will hide your profile and all your activity from other users.<br>
           You can re-open your account at any time by simply loggin in
        </p><br><br>
        <form action="close_account.php" method="post">
          <input type="submit" name="close_account" id="close_account" value="YES! Close it!">
          <input type="submit" name="cancel" id="cancel" value="No way!">
        </form>
      </div>
    </div>






</div>
