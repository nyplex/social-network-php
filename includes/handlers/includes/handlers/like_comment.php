<?php
require '../../config/config.php';
include("../classes/User.php");
include("../classes/Post.php");
include("../classes/Notification.php");

if(isset($_POST['user_liked']) && isset($_POST['comment_liked'])) {
  $comment_liked = $_POST['comment_liked'];
  $user_liked = $_POST['user_liked'];

  $query = $connexion->prepare('SELECT * FROM like_comment WHERE user_name = ? AND comment_id = ?');
  $query->execute(array($user_liked, $comment_liked));
  $row = $query->fetch();
  $num_row = $query->rowCount();

  $who_got_like_query = $connexion->prepare('SELECT posted_by, posted_to FROM post_comments WHERE id= ?');
  $who_got_like_query->execute(array($comment_liked));
  $who_got_like = $who_got_like_query->fetch();

  if($num_row == 0) {
    $insert_like = $connexion->prepare('INSERT INTO like_comment (user_name, comment_id) VALUES (?, ?)');
    $insert_like->execute(array($user_liked, $comment_liked));

    $insert_num_like = $connexion->prepare('UPDATE post_comments SET likes = likes+1 WHERE id = ?');
    $insert_num_like->execute(array($comment_liked));

    // Insert Notification
    if($who_got_like['posted_by'] != $user_liked) {
      $notification = new Notification($connexion, $user_liked);
      $notification->insertNotification($comment_liked, $who_got_like['posted_by'], "like_comment");
    }

  }else {
    $delete_like = $connexion->prepare('DELETE FROM like_comment WHERE user_name = ? AND comment_id = ?');
    $delete_like->execute(array($user_liked, $comment_liked));

    $insert_new_num_like = $connexion->prepare('UPDATE post_comments SET likes = likes-1 WHERE id = ?');
    $insert_new_num_like->execute(array($comment_liked));


  }

}else {
  stop();
}



?>
