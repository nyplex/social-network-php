<?php
require "config/config.php";
require "includes/form_handlers/register_handlers.php";
require "includes/form_handlers/login_handler.php";
require "includes/form_handlers/get_password_handler.php";
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
          <h5>Get your Password back!</h5>
        </div>
          <!-- Login Form -->
          <form class="login_form" action="get_password.php" method="post">
            <?php if(in_array("This email does not exist!", $error_array)) echo "<div class='errors' id='errfirst'>This email does not exist!</div>"; ?>
            <?php if(in_array("We have sent you an email", $error_array)) echo "<div class='errors' id='errfirst'>We have sent you an email, click <a href='register.php'>here</a> to login</div>"; ?>
            <input autocomplete="email" class="inputN" type="email" name="get_password_email" placeholder="Email" required><br>
            <button type="submit" class="get_password" name="get_password_button">Get my Password</button>
          </form>
      </div>
    </div>
  </body>
</html>
