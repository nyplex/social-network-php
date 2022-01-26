<?php
require '../../config/config.php';
include("../classes/User.php");
include("../classes/Post.php");
include("../classes/Notification.php");

if(isset($_POST['user_liked']) && isset($_POST['post_liked'])) {
  $post_liked = $_POST['post_liked'];
  $user_liked = $_POST['user_liked'];

  $query = $connexion->prepare('SELECT * FROM like_post WHERE user_name = ? AND post_id = ?');
  $query->execute(array($user_liked, $post_liked));
  $row = $query->fetch();
  $num_row = $query->rowCount();

  $who_got_like_query = $connexion->prepare('SELECT added_by, user_to FROM posts WHERE id= ?');
  $who_got_like_query->execute(array($post_liked));
  $who_got_like = $who_got_like_query->fetch();

  if($num_row == 0) {
    $insert_like = $connexion->prepare('INSERT INTO like_post (user_name, post_id) VALUES (?, ?)');
    $insert_like->execute(array($user_liked, $post_liked));
    $insert_num_like = $connexion->prepare('UPDATE posts SET likes = likes+1 WHERE id = ?');
    $insert_num_like->execute(array($post_liked));

    // Insert Notification
    if($who_got_like['added_by'] != $user_liked) {
      $notification = new Notification($connexion, $user_liked);
      $notification->insertNotification($post_liked, $who_got_like['added_by'], "like");
    }
    if($who_got_like['user_to'] != "none" && $who_got_like['user_to'] != $user_liked) {
      $notification = new Notification($connexion, $user_liked);
      $notification->insertNotification($post_liked, $who_got_like['user_to'], "like");
    }


    $ar = array('number_of_like: 53', 'unlike_it');
    echo json_encode($ar);
  }else {
    $delete_like = $connexion->prepare('DELETE FROM like_post WHERE user_name = ? AND post_id = ?');
    $delete_like->execute(array($user_liked, $post_liked));

    $insert_new_num_like = $connexion->prepare('UPDATE posts SET likes = likes-1 WHERE id = ?');
    $insert_new_num_like->execute(array($post_liked));

    $ar2 = array('number_of_like: 0', 'like_it');
    echo json_encode($ar2);
  }

}else {
  stop();
}

?>
