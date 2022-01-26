<?php
require "config/config.php";
require "includes/form_handlers/register_handlers.php";
require "includes/form_handlers/login_handler.php";
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
    <!-- Register JavaScript page -->
    <script src="assets/js/register.js"></script>
    <!-- Jquery date Picker script -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  </head>
  <body>
<!-- Modal Congrats Message -->
    <div id="myModal" class="modal">
    	<div class="modal-content">
    		 <span class="close">&times;</span>
    		  <p>Congrats, you're all set! Go ahead and login!</p>
    	</div>
    </div>
    <?php
// Prevent the hide of the form after subtmiting it
      if(isset($_POST['register_button'])) {
        echo "<script>
                $(document).ready(function(){
                  $('.first').hide();
                  $('.second').show();
                });
              </script>";
      }
      //To Display the congrat message in the modal
  		if(in_array("Conrgats, you're all set! Go ahead and login!", $error_array)) echo "<div class='congrats' id='congrats'></div>";

    ?>
    <div class="wrapper">
      <div class="login_box">
        <div class="login_header">
          <h1>THE SOCIAL NETWORK</h1>
        </div>
        <div class="first">
          <div class="forgot_password">
            <a href="get_password.php"><i class="fas fa-question-circle"></i>Password forgot</a>
          </div>
          <!-- Login Form -->
          <form class="login_form" action="register.php" method="post">
            <?php if(in_array("Incorrect email or password", $error_array)) echo "<div class='errors' id='errfirst'>Incorrect email or password</div>"; ?>
            <?php if(in_array("Your password has been reset!", $error_array)) echo "<div class='errors' id='errfirst'>Your password has been reset!</div>"; ?>
            <input autocomplete="email" class="inputN" type="email" name="log_email" placeholder="Email" value="<?php
            if (isset($_SESSION['log_email'])) {
                echo $_SESSION['log_email'] ;
            }
             ?>" required><br>
            <input autocomplete="current-password" class="inputN" type="password" name="log_password" placeholder="Password" required><br>
            <button type="submit" name="login_button">Login</button>
          </form>
          <div class="login_footer">
            <h4 id="signup">New user? Sign Up here!</h4>
          </div>
        </div>
        <div class="second">
          <!-- Register Form -->
          <form class="register_form" action="register.php" method="post">
            <?php if(in_array("Your first name must be between 2 and 20 charachers", $error_array)) echo "<div class='errors' id='errfirst'>Your first name must be between 2 and 20 charachers</div>"; ?>
            <input class="inputN first_input" type="text" name="reg_fname" placeholder="First Name" required value="<?php
            if (isset($_SESSION['reg_fname'])) {
                echo $_SESSION['reg_fname'] ;
            }
             ?>">
            <br>
            <?php if(in_array("Your last name must be between 2 and 20 charachers", $error_array)) echo "<div class='errors' id='errlast'>Your last name must be between 2 and 20 charachers</div>"; ?>
            <input class="inputN" type="text" name="reg_lname" placeholder="Last Name" required value="<?php
            if (isset($_SESSION['reg_lname'])) {
                echo $_SESSION['reg_lname'] ;
            }
             ?>">
            <br>
            <?php if(in_array("This is not a valid date", $error_array)) echo "<div class='errors' id='errdob'>This is not a valid date</div>"; ?>
            <input autocomplete="off" class="inputN" id="reg_dob" type="text" name="reg_dob" placeholder="Date of Birth" required value="<?php
            if (isset($_SESSION['reg_dob'])) {
                echo $_SESSION['reg_dob'] ;
            }
             ?>"><br>
            <!-- Sex selection form -->
            <?php if(in_array("You must select an option", $error_array)) echo "<div class='errors' id='errsex'>You must select an option</div>"; ?>
            <div class="sex_div">
              <input class="inputS" type="radio" id="sex_male" name="reg_sex" value="male" ><label for="sex_male" class="inputS input_male">Male</label>
              <input class="inputS" type="radio" id="sex_female" name="reg_sex" value="female"><label for="sex_female" class="inputS">Female</label>
            </div>
            <br>
            <!-- Country city selection form -->
            <div class="country_div">
              <?php if(in_array("You must select a Country", $error_array)) echo "<div class='errors' id='erremail1'>You must select a Country</div>";
                    else if(in_array("You must select a State", $error_array)) echo "<div class='errors' id='erremail1'>You must select a State</div>";
                    else if(in_array("You must select a City", $error_array)) echo "<div class='errors' id='erremail1'>You must select a City</div>"; ?>
              <select name="reg_country" class="countries" id="countryId" value="" >
                <option value="">Country</option>
              </select>
              <select name="reg_state" class="states" id="stateId" >
                <option value="">State</option>
              </select>
              <select name="reg_city" class="cities" id="cityId" >
                <option value="">City</option>
              </select>
            </div>
            <br>
            <?php if(in_array("This email is already used", $error_array)) echo "<div class='errors' id='erremail1'>This email is already used</div>";
                  else if(in_array("Invalid email format", $error_array)) echo "<div class='errors' id='erremail1'>Invalid email format</div>";
                  else if(in_array("Emails do not match", $error_array)) echo "<div class='errors' id='erremail1'>Emails do not match</div>"; ?>
            <input class="inputN" type="email" name="reg_email" placeholder="Email" required value="<?php
            if (isset($_SESSION['reg_email'])) {
                echo $_SESSION['reg_email'] ;
            }
             ?>">
            <br>
            <input class="inputN" type="email" name="reg_email_2" placeholder="Confirm Email" required value="<?php
            if (isset($_SESSION['reg_email_2'])) {
                echo $_SESSION['reg_email_2'] ;
            }
             ?>">
            <br>
            <?php if(in_array("Your passwords do not match", $error_array)) echo "<div class='errors' id='errpassword1'>Your passwords do not match</div>";
                  else if(in_array("Your password must contain at least one digit", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one digit</div>";
                  else if(in_array("Your password must contain at least one Capital Letter", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one Capital Letter</div>";
                  else if(in_array("Your password must contain at least one small letter", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one small letter</div>";
                  else if(in_array("Your password must be between 8 and 30 characters", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must be between 8 and 30 characters</div>";
                  else if(in_array("Your password must contain at least one special character", $error_array)) echo "<div class='errors' id='errpassword1'>Your password must contain at least one special character</div>";
                  else if(in_array("Password must not contain any white space", $error_array)) echo "<div class='errors' id='errpassword1'>Password must not contain any white spacer</div>"; ?>
            <input autocomplete="off" class="inputN" type="password" name="reg_password" placeholder="Password*" required>
            <br>
            <input autocomplete="off" class="inputN" type="password" name="reg_password_2" placeholder="Confirm Password*" required>
            <br>
            <button type="submit" name="register_button">Register</button>
          </form>
          <div class="login_footer">
            <h4 id="signin">Already have an account? Login here!</h4>
          </div>
          <div class="footer_register">
            <p>*Password must be at least 8 characters long and must contains at least one
            uppercase letter, one lowercase letter, one digit and one special characters.</p>
          </div>
        </div>
      </div>
    </div>
    <script>
    //Diplay the congrat message
    var congrat = document.querySelector("#congrats"); //Select the PHP error message from error_array
    if(congrat !== null) { // if error message exist:
      //Display the modal
      var span = document.getElementsByClassName("close")[0];
      var modal = document.getElementById("myModal");
      modal.style.display = "block";
      span.onclick = function() {
          // Hide register form and show login form
        modal.style.display = "none";
        $(".second").slideUp("slow", function() {
          $(".first").slideDown("slow");
        });
      }
      window.onclick = function(event) {
        if (event.target == modal) {
          modal.style.display = "none";
            // Hide register form and show login form
          $(".second").slideUp("slow", function() {
            $(".first").slideDown("slow");
          });
        }
      }
    };
    </script>
  </body>
</html>
