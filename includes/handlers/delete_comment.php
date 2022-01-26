<?php

require '../../config/config.php';

  if(isset($_POST['id'])) {
    $comment_id = $_POST['id'];
    $query = $connexion->prepare('UPDATE post_comments SET removed = ? WHERE id = ?');
    $query->execute(array("yes", $comment_id));
  }else {
    stop();
  }
?>
