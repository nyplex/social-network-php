<?php

if(isset($_POST['get_password_button'])) {
  $email = filter_var($_POST['get_password_email'], FILTER_SANITIZE_EMAIL); // sanitize Email

  $check_email_query = $connexion->prepare('SELECT email FROM users WHERE email = ?');
  $check_email_query->execute(array($email));
  $result_query = $check_email_query->fetch();
  $num_row = $check_email_query->rowCount();

  if($num_row > 0) {
    function password_generate($chars) {
      $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
      return substr(str_shuffle($data), 0, $chars);
    }
      $token = password_generate(7);

      $email = $result_query['email'];
      $insert_temp_token = $connexion->prepare('INSERT INTO get_password (email, nouveau) VALUES (?, ?)');
      $insert_temp_token->execute(array($email, $token));

      $dest = $email;
  		$sujet = "YOUR DATA!";
  		$corp = "Hi there, click on this <a href='new_password.php?token=".$token."'>link</a> to reset your password";
  		$headers = "From: tsn@travelling-potatoes.fr\r\n";
  		$headers .= "MIME-Version: 1.0\r\n";
  		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
 		  mail($dest, $sujet, $corp, $headers);
      array_push($error_array, "We have sent you an email");
      

  }else {
    array_push($error_array, "This email does not exist!");
  }
}

?>
