<?php

include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
?>
<!-- Script for the country input -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//geodata.solutions/includes/countrystatecity.js"></script>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


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

<!-- ---------------------------------- CENTER COLUMN ----------------------------------- -->

    <div class="all_center settings_page_div" id='all_center_search_page'>
      <h4 class="setting_title ">Account Settings <i id="settings_icon" class="fas fa-tools"></i></h4><span style="color:#4ab34a;font-size:20px;font-weight:bold;"><?php echo $message; ?></span>
      <div id="search_page_center">
        <div class="profile_pic_settings">
          <h5>Profile Picture <i id="settings_icon" class="fas fa-portrait medium_icon_setting"></i></h5>
          <?php
            echo "<img src='". $user['profile_pic'] ."' id='small_profile_pics'>";
          ?>
          <br />
          <a href='upload.php'>Upload new profile picture</a>
          <hr />
        </div>
        <div class="change_data_settings">
          <h5>Change your Data <i id="settings_icon" class="fas fa-file-signature medium_icon_setting"></i></h5>
          <?php
            $user_data_query = $connexion->prepare('SELECT first_name, last_name, email, date_of_birth FROM users WHERE user_name = ?');
            $user_data_query->execute(array($userLoggedIn));
            $row = $user_data_query->fetch();
            $first_name = $row['first_name'];
            $flast_name = $row['last_name'];
            $email = $row['email'];
            $date_of_birth = $row['date_of_birth'];
          ?>
          <form id="form_data_settings"   action="settings.php" method="post">

            <label>First Name: </label> <?php echo "<span class='error_message_settings'>".$fname_message."</span>"; ?> <br />
            <input id="first_setting_input" class="input_setting input_set" type="text" name="first_name" value="<?php echo $first_name; ?>" onkeypress="return event.charCode != 32"><br>

            <label>Last Name: </label> <?php echo "<span class='error_message_settings'>".$lname_message."</span>"; ?> <br />
            <input id="last_setting_input" class="input_setting input_set" type="text" name="last_name" value="<?php echo $flast_name; ?>" onkeypress="return event.charCode != 32"><br>

            <label>Date of Birth: </label> <?php echo "<span class='error_message_settings'>".$date_message."</span>"; ?> <br />
            <input autocomplete="off" class="input_set" id="datepicker_setting" type="text" name="date_of_birth" placeholder="Date of Birth" required value="<?php echo $date_of_birth; ?>"><br>

            <label>Sex: </label> <br />
            <div class="sex_div">
              <input type="radio" name="sex" value="male"><label>Male</label><input type="radio" name="sex" value="female"><label>Female</label><br>
            </div>
            <label>Email: </label> <?php echo "<span class='error_message_settings'>".$email_message."</span>"; ?> <br />
            <input id="email_setting_input" class="input_setting input_set" type="text" name="email" value="<?php echo $email; ?>" onkeypress="return event.charCode != 32"><br>

            <label>Confirm Email: </label> <br />
            <input id="email_setting_input_2" class="input_setting input_set" type="text" name="email2" value="<?php echo $email; ?>" onkeypress="return event.charCode != 32"><br>
            <input type="submit" name="update_details" id="save_details" value="Update Details"><br>

          </form>
          <hr />
        </div>

        <div class="change_pasword_settings">
          <h5>Change your Password <i id="settings_icon" class="fas fa-key medium_icon_setting"></i></h5>
          <form id="form_data_settings"  action="settings.php" method="post">
            <label>Old Password: </label> <br />
            <input class="input_set" type="password" autocomplete="current-password" name="old_password" onkeypress="return event.charCode != 32"><br>
            <label>New Password: </label> <br />
             <input class="input_set" type="password" autocomplete="off" name="new_password_1" onkeypress="return event.charCode != 32"><br>
            <label>Confirm New Password: </label> <br />
            <input class="input_set" type="password" autocomplete="off" name="new_password_2" onkeypress="return event.charCode != 32"><br>
            <input type="submit" name="update_password" id="save_password" value="Update Password"><br>
            <?php echo $password_message; ?> <br>
          </form>
          <hr />
        </div>
        <div class="change_info_settings">
            <h5>Your Info <i id="settings_icon" class="fas fa-info-circle medium_icon_setting"></i></h5>
            <?php
              $user_data_query = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
              $user_data_query->execute(array($userLoggedIn));
              $row = $user_data_query->fetch();
              $first_name = $row['first_name'];
              $flast_name = $row['last_name'];
              $email = $row['email'];
              $date_of_birth = $row['date_of_birth'];
            ?>
            <form id="form_data_settings" action="settings.php" method="post">
              <!-- Country city selection form -->
               <i id="briefcase_setting" class="fas fa-briefcase info_icon_setting icon_info"></i><input id="occupation_settings" class="input_setting" type="text" name="occupation" value="<?php echo $row['occupation']; ?>"><br />
              <div class="country_div">
                <i class="fas fa-house-user info_icon_setting icon_info"></i>
                <select name="live_country" class="countries" id="countryId" value="" >
                  <option value="">Country</option>
                </select>
                <select name="live_state" class="states" id="stateId" >
                  <option value="">State</option>
                </select>
                <select name="live_city" class="cities" id="cityId" >
                  <option value="">City</option>
                </select><span> *Where do you live?</span>
              </div>
              <!-- Where you were born selection form -->
              <div class="country_div">
                <i id="position_icon" class="fas fa-map-marker-alt info_icon_setting icon_info"></i>
                <input id="occupation_settings" class="input_setting" type="text" name="born_city" value="" placeholder="Place of birth"><br />
                <input id="occupation_settings_2" class="input_setting" type="text" name="born_country" value="" placeholder="Country">
              </div>
              <div class="relationship_div">
                <i id="heart_setting" class="fas fa-heart icon_info"></i>
                <input type="radio" name="relationship" value="single"><label>Single</label><br />
                <input class="icon_heart_move" type="radio" name="relationship" value="In a relationship"><label> In a relationship</label><br />
                <input class="icon_heart_move" type="radio" name="relationship" value="I'm Engaged"><label> Engaged</label><br />
                <input class="icon_heart_move" type="radio" name="relationship" value="I'm Married"><label> Married</label><br />
                <input class="icon_heart_move" type="radio" name="relationship" value="It's complicated!"><label> Complicated</label><br />
                <input class="icon_heart_move" type="radio" name="relationship" value="I'm Separeted"><label> Separated</label><br />
                <input class="icon_heart_move" type="radio" name="relationship" value="I'm Divorced"><label> Divorced</label><br />
              </div>
              <div class="biography_settings">
                <h6><i class="fas fa-book info_icon_setting icon_info"></i> Biography</h6>
                <textarea class="text_bio_settings" name="biography"><?php echo $row['bio']; ?></textarea>
              </div>
              <input type="submit" name="update_info" id="update_info" value="Update my Info"><br>
            </form>
            <hr />
        </div>
        <div class="close_account_settings">
          <h5>Close your Account <i id="settings_icon" class="fas fa-radiation-alt medium_icon_setting"></i></h5>
          <form action="settings.php" method="post">
            <input type="submit" name="closed_account" id="closed_account" value="Close Account">
          </form>
          <hr />
        </div>

      </div>
    </div>
<script type="text/javascript">
// CHANGE DATE VALUE TO TODAY DATE For Date Input
$(function() {
  $("#datepicker_setting").datepicker({
    dateFormat: 'yy-mm-dd',
    changeMonth: true,
    changeYear: true,
    minDate: new Date(1901, 10 - 1, 25),
    maxDate: '0',
    yearRange: '1901:c',
    inline: true
  });
});
</script>
