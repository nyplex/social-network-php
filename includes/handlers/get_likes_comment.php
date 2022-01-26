<?php
require '../../config/config.php';

if(isset($_POST['commentID'])) {
  $commentID = $_POST['commentID'];
  $query = $connexion->prepare('SELECT likes FROM post_comments WHERE id = ?');
  $query->execute(array($commentID));
  $row = $query->fetch();
  $num_likes = $row['likes'];

  if(isset($_POST['user'])) {
    $user = $_POST['user'];
    $check_user_liked = $connexion->prepare('SELECT * FROM like_comment WHERE comment_id = ? AND user_name = ?');
    $check_user_liked->execute(array($commentID, $user));
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
