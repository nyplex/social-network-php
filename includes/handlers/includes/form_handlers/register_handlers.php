<?php

//Declaring variables to prevent erross
$fname = ""; //First name
$lname = ""; //Last name
$dob = ""; //Date of birth
$sex = ""; //Sex
$country = ""; //Country
$state = ""; //State
$city = ""; //City
$email = ""; //email
$email2 = ""; //email 2
$password = ""; //password
$password2 = ""; //password 2
$date = ""; //Sign up date
$error_array = array(); //holds error messages

if(isset($_POST['register_button'])) {
  //Registration form values

  //First name
  $fname = strip_tags($_POST['reg_fname']); // remove html tags
  $fname = str_replace(' ', '', $fname); // remove any spaces
  $fname = ucfirst(strtolower($fname)); //Uppercase the first letter
  $fname = htmlentities($fname, ENT_NOQUOTES, 'utf-8');
  $fname = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $fname);
  $fname = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $fname);
  $fname = preg_replace('#&[^;]+;#', '', $fname);
  $_SESSION['reg_fname'] = $fname; // store first name variable into session variable
  //Last name
  $lname = strip_tags($_POST['reg_lname']); // remove html tags
  $lname = str_replace(' ', '', $lname); // remove any spaces
  $lname = ucfirst(strtolower($lname)); //Uppercase the first letter
  $lname = htmlentities($lname, ENT_NOQUOTES, 'utf-8');
  $lname = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $lname);
  $lname = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $lname);
  $lname = preg_replace('#&[^;]+;#', '', $lname);
  $_SESSION['reg_lname'] = $lname; // store first name variable into session variable
  //Date of Birth
  $dob = $_POST['reg_dob']; // store values into the variables
  $_SESSION['reg_dob'] = $dob; // store first name variable into session variable
  //country
  $country = $_POST['reg_country']; // store values into the variables
  $_SESSION['reg_country'] = $country; // store first name variable into session variable
  //State
  $state = $_POST['reg_state']; // store values into the variables
  $_SESSION['reg_state'] = $state; // store first name variable into session variable
  //City
  $city = $_POST['reg_city']; // store values into the variables
  $_SESSION['reg_city'] = $city; // store first name variable into session variable
  //Email
  $email = strip_tags($_POST['reg_email']); //remove html tags
  $email = str_replace(" ", "", $email); //remove any spaces
  $_SESSION['reg_email'] = $email; // store first name variable into session variable
  //Email 2
  $email2 = strip_tags($_POST['reg_email_2']); //remove html tags
  $email2 = str_replace(" ", "", $email2); //remove any spaces
  $_SESSION['reg_email_2'] = $email2; // store first name variable into session variable
  //Passwords
  $password = strip_tags($_POST['reg_password']); //remove html tags
  $password2 = strip_tags($_POST['reg_password_2']); //remove html tags
  //SignUp Date
  $date = date("Y-m-d H:i:s:u");

  if($email == $email2) {
    //Check if email is in a valid NumberFormatter
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email = filter_var($email, FILTER_VALIDATE_EMAIL);
      //Check if email already exist
      $e_check = $connexion->prepare('SELECT email FROM users WHERE email = ?');
      $e_check->execute(array($email));
      //Count number of row returned
      $num_row = $e_check->rowCount();

      if($num_row > 0) {
        array_push($error_array, "This email is already used");
      }
    }else {
      array_push($error_array, "Invalid email format");
    }
  }else {
    array_push($error_array, "Emails do not match");
  }
  // Check first name
  if (strlen($fname) > 20 || strlen($fname) < 2) {
    array_push($error_array, "Your first name must be between 2 and 20 charachers");
  }
  // Check last name
  if (strlen($lname) > 20 || strlen($lname) < 2) {
    array_push($error_array, "Your last name must be between 2 and 20 charachers");
  }
  // Check DOB format
  if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dob)) {
    array_push($error_array, "This is not a valid date");
  }
  //Check sex value
  if(!isset($_POST['reg_sex'])){
    array_push($error_array, "You must select an option");
  }
  //Check country value
  if($_POST['reg_country'] == "") {
    array_push($error_array, "You must select a Country");
  }
  //Check state value
  if($_POST['reg_state'] == "") {
    array_push($error_array, "You must select a State");
  }
  //Check city value
  if($_POST['reg_city'] == "") {
    array_push($error_array, "You must select a City");
  }
  // Check password
  if ($password !== $password2) {
    array_push($error_array, "Your passwords do not match");
  }elseif (!preg_match("/\d/", $password)) {
    array_push($error_array, "Your password must contain at least one digit");
  }elseif (!preg_match("/[A-Z]/", $password)) {
    array_push($error_array, "Your password must contain at least one Capital Letter");
  }elseif (!preg_match("/[a-z]/", $password)) {
    array_push($error_array, "Your password must contain at least one small letter");
  }elseif (!preg_match("/\W/", $password)) {
    array_push($error_array, "Your password must contain at least one special character");
  }elseif (!preg_match("/\S/", $password)) {
    array_push($error_array, "Password must not contain any white space");
  }
  //Check password length
  if (strlen($password) > 30 || strlen($password) < 8) {
    array_push($error_array, "Your password must be between 8 and 30 characters");
  }
  //If there is no error
  if(empty($error_array)) {
    $password = password_hash($password, PASSWORD_DEFAULT); // Encrypt password
    //Generate user
    $username = strtolower($fname . "_" . $lname);
    $check_username_query = $connexion->prepare('SELECT user_name FROM users WHERE user_name = ?');
    $check_username_query->execute(array($username));
    while ($user_result = $check_username_query->fetch()) {
			$i = 0;
			//If username already exist add number
			while($user_result['user_name'] !== null) {
				$i++;
				$username = $username . "_" . $i;
				$username_check = $connexion->prepare('SELECT user_name FROM users WHERE user_name = ?');
				$username_check->execute(array($username));
				$user_result = $username_check->fetch();
			}
			$username = $username;
		}
    //Generate default profile picture
		$sexValue = $_POST['reg_sex'];
		if($sexValue == "male"){
			$profile_pic = "assets/images/profile_pics/defaults/default_male_covid.png";
		}else {
			$profile_pic = "assets/images/profile_pics/defaults/default_female_covid.png";
		}
    //Insert values to the Data Base
    $query = $connexion->prepare('INSERT INTO users (first_name, last_name, date_of_birth, sex, country,
                                                     state, city, email, password, user_name, signup_date,
                                                     profile_pic, num_posts, num_likes, user_closed,
                                                     friend_array, login, occupation, born_country,
                                                     born_state, born_city, relationship, bio)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute(array(
      $fname,
      $lname,
      $dob,
      $_POST['reg_sex'],
      $_POST['reg_country'],
      $_POST['reg_state'],
      $_POST['reg_city'],
      $email,
      $password,
      $username,
      $date,
      $profile_pic,
      "0",
      "0",
      "no",
      ",",
      "no",
      "",
      "",
      "",
      "",
      "",
      ""
    ));
    //Generate the congrats message
    array_push($error_array, "Conrgats, you're all set! Go ahead and login!");
    //Clear session Variables
    $_SESSION['reg_fname'] = "";
    $_SESSION['reg_lname'] = "";
    $_SESSION['reg_dob'] = "";
    $_SESSION['reg_country'] = "";
    $_SESSION['reg_state'] = "";
    $_SESSION['reg_city'] = "";
    $_SESSION['reg_email'] = "";
    $_SESSION['reg_email_2'] = "";
  }
}
?>
