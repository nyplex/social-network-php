<?php
require '../../config/config.php';

  if(isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
  }
  if(isset($_POST['result'])) {
    if($_POST['result'] == 'true') {
      $query = $connexion->prepare('UPDATE posts SET deleted = ? WHERE id = ?');
      $query->execute(array("yes", $post_id));
    }
  }

?>
