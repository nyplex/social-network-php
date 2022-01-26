<?php
// Declare the errors variables
$email_message = "";
$fname_message = "";
$lname_message = "";
$date_message = "";
$message = "";
$password_message = "";

if(isset($_POST['update_details'])) {
  $result = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
  $result->execute(array($userLoggedIn));
  $result_query = $result->fetch();

  //First Name
  $fname = strip_tags($_POST['first_name']); //remove html tags
  $fname = str_replace(" ", "", $fname); //remove any spaces
  $fname = ucfirst(strtolower($fname)); //Uppercase the first letter
  $fname = htmlentities($fname, ENT_NOQUOTES, 'utf-8');
  $fname = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $fname);
  $fname = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $fname);
  $fname = preg_replace('#&[^;]+;#', '', $fname);
  //Last Name
  $lname = strip_tags($_POST['last_name']); //remove html strip_tags
  $lname = str_replace(" ", "", $lname); //remove any spaces
  $lname = ucfirst(strtolower($lname)); // Uppercase the first letter
  $lname = htmlentities($lname, ENT_NOQUOTES, 'utf-8');
  $lname = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $lname);
  $lname = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $lname);
  $lname = preg_replace('#&[^;]+;#', '', $lname);
  //Date of Birth
  $date_of_birth = $_POST['date_of_birth'];
  //Email
  $email = $_POST['email'];
  //Email 2
  $email2 = $_POST['email2'];

  //Check if emails are matching
  if($email == $email2) {
    // Check is email is valid
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email = filter_var($email, FILTER_VALIDATE_EMAIL);
      // Check if email already in used
      $email_check = $connexion->prepare('SELECT * FROM users WHERE email = ?');
      $email_check->execute(array($email));
      $row = $email_check->fetch();
      $matched_user = $row['user_name'];

      if($matched_user == $userLoggedIn) {
        $email_message = "";
      }else if ($matched_user == "") {
        $email_message = "";
      }else {
        $email_message = "Email already in used!";
      }
    }else {
      $email_message = "Email not valid!";
    }
    // Check first name
    if (strlen($fname) > 20 || strlen($fname) < 2) {
      $fname_message = "Your first name must be between 2 and 20 charachers";
    }
    // Check last name
    if (strlen($lname) > 20 || strlen($lname) < 2) {
      $lname_message = "Your last name must be between 2 and 20 charachers";
    }
    // Check DOB format
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST['date_of_birth'])) {
      $date_message = "This is not a valid date";
    }
    if(!isset($_POST['sex'])) {
      $sex = $result_query['sex'];
    }else {
      $sex = $_POST['sex'];
    }
    if (empty($email_message) && empty($fname_message) && empty($lname_message) && empty($date_message)) {
      $query = $connexion->prepare('UPDATE users SET first_name = ?, last_name = ?, date_of_birth = ?, email = ?, sex = ? WHERE user_name = ?');
      $query->execute(array($fname, $lname, $date_of_birth, $email, $sex, $userLoggedIn));
      $message = "Details Updated!";
    }
  }else {
    $email_message = "Emails not matching!";
  }
}

//***************************************

if(isset($_POST['update_password'])) {
  $old_password = strip_tags($_POST['old_password']);
  $new_password_1 = strip_tags($_POST['new_password_1']);
  $new_password_2 = strip_tags($_POST['new_password_2']);

  $password_query = $connexion->prepare('SELECT password FROM users WHERE user_name = ?');
  $password_query->execute(array($userLoggedIn));
  $row = $password_query->fetch();
  $db_password = $row['password'];

  if(password_verify($old_password, $db_password)) {
    if($new_password_1 == $new_password_2) {
      if (strlen($new_password_1) > 30 || strlen($new_password_1) < 8){
        $password_message = "Password must contains between 8 and 30 characters!";
      }else if(!preg_match("/\d/", $new_password_1)) {
        $password_message = "Password muyst contains at least one digit!";
      }else if(!preg_match("/[A-Z]/", $new_password_1)) {
        $password_message = "Password must contains at least one uppercase letter!";
      }else if (!preg_match("/[a-z]/", $new_password_1)) {
        $password_message = "Password must contains at least one lower case letter!";
      }else if (!preg_match("/\W/", $new_password_1)) {
        $password_message = "Password password must contain at least one special character!";
      }else if (!preg_match("/\S/", $new_password_1)) {
        $password_message = "Password must not contains any white space!";
      }else {
        $password_message = "Password has been changed";
        $password = password_hash($new_password_1, PASSWORD_DEFAULT);
        $update_password = $connexion->prepare('UPDATE users SET password = ? WHERE user_name = ?');
        $update_password->execute(array($password, $userLoggedIn));
      }
    }else {
      $password_message = "New passwords do not match!";
    }
  }else {
    $password_message = "Old password is incorrect";
  }
}

//*****************************************************

if(isset($_POST['update_info'])) {
  $info_query = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
  $info_query->execute(array($userLoggedIn));
  $row_info = $info_query->fetch();

// Occupation
  if(!isset($_POST['occupation'])) {
    $occupation = $row_info['occupation'];
  }else {
    $occupation = $_POST['occupation'];
  }
// where do you live
  if($_POST['live_country'] == "") {
    $live_country = $row_info['country'];
  }else {
    $live_country = $_POST['live_country'];
  }

  if($_POST['live_state'] == "") {
    $live_state = $row_info['state'];
  }else {
    $live_state = $_POST['live_state'];
  }

  if($_POST['live_city'] == "") {
    $live_city = $row_info['city'];
  }else {
    $live_city = $_POST['live_city'];
  }
// where are you born
  if($_POST['born_country'] == "") {
    $born_country = $row_info['born_country'];
  }else {
    $born_country = $_POST['born_country'];
  }

  if($_POST['born_city'] == "") {
    $born_city = $row_info['born_city'];
  }else {
    $born_city = $_POST['born_city'];
  }
//relationship
if(!isset($_POST['relationship'])) {
  $relation = $row_info['relationship'];
}else {
  $relation = $_POST['relationship'];
}
//biography
if(!isset($_POST['biography'])) {
  $biography = $row_info['bio'];
}else {
  $biography = $_POST['biography'];
}

$query_info = $connexion->prepare('UPDATE users SET occupation = ?, country = ?, state = ?, city = ?, born_country = ?, born_city = ?, relationship = ?, bio = ? WHERE user_name = ?');
$query_info->execute(array($occupation, $live_country, $live_state, $live_city, $born_country, $born_city, $relation, $biography, $userLoggedIn));
$message = "Info Updated!";

}


//************************************************
if(isset($_POST['closed_account'])) {
  header('Location: close_account.php');
}

?>
