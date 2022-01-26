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

    <!-- ---------------------------------- CENTER ZONE ----------------------------------- -->
        <div class="all_center">
          <div class="main_column">
            <form class="post_form" action="index.php" method="post" enctype="multipart/form-data">
              <p>This account does not exist!</p>
            </form>
          </div>
        </div>
