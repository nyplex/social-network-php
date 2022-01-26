<?php
require '../../config/config.php';

if(isset($_POST['postID'])) {
  $postID = $_POST['postID'];
  $query = $connexion->prepare('SELECT likes FROM posts WHERE id = ?');
  $query->execute(array($postID));
  $row = $query->fetch();
  $num_likes = $row['likes'];

  if(isset($_POST['user'])) {
    $user = $_POST['user'];
    $check_user_liked = $connexion->prepare('SELECT * FROM like_post WHERE post_id = ? AND user_name = ?');
    $check_user_liked->execute(array($postID, $user));
    $num_row = $check_user_liked->rowCount();
    if($num_row == 0) {
      $toLike = "yes";
      $myarray = array($num_likes, $toLike);
      echo json_encode($myarray);
    }else {
      $toLike = "no";
      $myarray = array($num_likes, $toLike);
      echo json_encode($myarray);
    }
  }

}else {
  stop();
}



?>
