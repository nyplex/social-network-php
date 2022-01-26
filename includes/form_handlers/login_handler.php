<?php

if(isset($_POST['login_button'])) {
  $email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); // sanitize Email
  $_SESSION['log_email'] = $email; // store email into session variable
  $password = $_POST['log_password']; // store password into new variable

  //Get the data from the data base
  $check_database_query = $connexion->prepare('SELECT * FROM users WHERE email = ?');
  $check_database_query->execute(array($email));
  $result_query = $check_database_query->fetch();

  if(password_verify($password, $result_query['password'])) {
    $username = $result_query['user_name'];
    $_SESSION['username'] = $username;

    //Update data base and set user loggedin to yes
    $upadate_user_loggedin =$connexion->prepare('UPDATE users SET login = ? WHERE user_name = ?');
    $upadate_user_loggedin->execute(array("yes", $username));

    // Check if user closed his account
    $check_user_closed = $connexion->prepare('SELECT * FROM users WHERE email = ? AND user_closed = ?');
    $check_user_closed->execute(array($email, "yes"));
    $result_user_closed = $check_user_closed->fetch();

    //If Account was closed , reopen the account
    if($result_user_closed['user_closed'] !== null) {
      $reopen_account = $connexion->prepare('UPDATE users SET user_closed="no" WHERE email = ? ');
      $reopen_account->execute(array($email));
    }
    //Success login , access to index.php
    header("Location: index.php");
    exit();

  //Login denied
  }else {
    array_push($error_array, "Incorrect email or password");
  }
}
?>
