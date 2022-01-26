<?php
require "config/config.php";
require "includes/form_handlers/register_handlers.php";
require "includes/form_handlers/login_handler.php";
require "includes/form_handlers/get_password_handler.php";

if(!isset($_GET['token'])) {
  header('Location: register.php');
}else {
  $token = $_GET['token'];
}
if(isset($_POST['new_password_button'])) {

  //Passwords
  $password = strip_tags($_POST['new_password']); //remove html tags
  $password2 = strip_tags($_POST['new_password_2']); //remove html tags

  $get_email_query = $connexion->prepare('SELECT email FROM get_password WHERE nouveau = ?');
  $get_email_query->execute(array($token));
  $result_query = $get_email_query->fetch();
  $num_row = $get_email_query->rowCount();
  $email = $result_query['email'];
  // Check password
  if ($password !== $password2) {
    array_push($error_array, "Your passwords do not match");
  }else if (!preg_match("/\d/", $password)) {
    array_push($error_array, "Your password must contain at least one digit");
  }else if (!preg_match("/[A-Z]/", $password)) {
    array_push($error_array, "Your password must contain at least one Capital Letter");
  }else if (!preg_match("/[a-z]/", $password)) {
    array_push($error_array, "Your password must contain at least one small letter");
  }else if (!preg_match("/\W/", $password)) {
    array_push($error_array, "Your password must contain at least one special character");
  }else if (!preg_match("/\S/", $password)) {
    array_push($error_array, "Password must not contain any white space");
  }
  //Check password length
  else if (strlen($password) > 30 || strlen($password) < 8) {
    array_push($error_array, "Your password must be between 8 and 30 characters");
  }else if ($num_row == 0) {
    array_push($error_array, "The link has expired");
  }
  //If there is no error
  if(empty($error_array)) {
    $new_password = password_hash($password, PASSWORD_DEFAULT); // Encrypt password
    $insert_new_password = $connexion->prepare('UPDATE users SET password = ? WHERE email = ?');
    $insert_new_password->execute(array($new_password, $email));

    $delete_old_token = $connexion->prepare('DELETE FROM get_password WHERE email = ?');
    $delete_old_token->execute(array($email));
    array_push($error_array, "We have reset your Password");

  }

}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to TSN</title>
    <!-- CSS file -->
    <link rel="stylesheet" href="assets/css/register_style.css">
    <!-- CSS Styles Jquery Date Picker -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- FONTS -->
    <link rel="stylesheet" href="https://use.typekit.net/wez4oyk.css">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/0fd6fa10db.js" crossorigin="anonymous"></script>
    <!-- Jquery Script -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Script for the country input -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//geodata.solutions/includes/countrystatecity.js"></script>
    <!-- Main JavaScript page -->
    <script src="assets/js/register.js"></script>
    <!-- Jquery date Picker script -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  </head>
  <body>

    <div class="wrapper">
      <div class="login_box">
        <div class="login_header">
          <h1>THE SOCIAL NETWORK</h1>
        </div>
        <div class="title">
          <h5>Reset your Password</h5>
        </div>
          <!-- Login Form -->
          <form class="new_password_form" action="new_password.php?token=<?php echo $token; ?>" method="post">
            <?php if(in_array("Your passwords do not match", $error_array)) echo "<div class='errors' id='errpassword1'>Your passwords do not match</div>";
                  else if(in_array("We have reset your Password", $error_array)) echo "<div class='errors' id='errfirst'>We have reset your password, click <a href='register.php'>here</a> to login</div>";
                  else if(in_array("The link has expired", $error_array)) echo "<div class='errors' id='errfirst'>The link has expired, click <a href='register.php'>here</a> to go back</div>";
                  else if(in_array("Your password must contain at least one digit", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one digit</div>";
                  else if(in_array("Your password must contain at least one Capital Letter", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one Capital Letter</div>";
                  else if(in_array("Your password must contain at least one small letter", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one small letter</div>";
                  else if(in_array("Your password must be between 8 and 30 characters", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must be between 8 and 30 characters</div>";
                  else if(in_array("Your password must contain at least one special character", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one special character</div>";
                  else if(in_array("Password must not contain any white space", $error_array)) echo "<div class='errors' id='errpassword1'>Password must not contain any white spacer</div>"; ?>
            <input autocomplete="off" class="inputN" type="password" name="new_password" placeholder="New Password" required><br>
            <input autocomplete="off" class="inputN" type="password" name="new_password_2" placeholder="Confirm New Password" required><br>
            <button type="submit" name="new_password_button">Confirm</button>
          </form>
          <div class="footer_register">
            <p>*Password must be at least 8 characters long and must contains at least one
            uppercase letter, one lowercase letter, one digit and one special characters.</p>
          </div>
      </div>
    </div>
  </body>
</html>
