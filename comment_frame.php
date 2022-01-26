<?php
  require 'config/config.php';
  include("includes/classes/User.php");
  include("includes/classes/Post.php");

  // Check if user has logged in
  if(isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
    $user_details->execute(array($userLoggedIn));
    $result_details = $user_details->fetch();
  } else {
    header('Location: register.php');
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <!-- Jquery Script -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootsrap Script -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- Bootsrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <!-- FONTS -->
    <link rel="stylesheet" href="https://use.typekit.net/wez4oyk.css">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/0fd6fa10db.js" crossorigin="anonymous"></script>
    <!-- CSS file -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/comment_frame_style.css">
    <link rel="stylesheet" href="assets/css/index_style_popular_wrapper.css">
    <link rel="stylesheet" href="assets/css/index_style_post_area.css">
    <link rel="stylesheet" href="assets/css/index_style_each_post.css">
    <link rel="stylesheet" href="assets/css/details_style.css">
    <!-- Like Post Java Script -->
    <script src="assets/js/like_comment.js"></script>

  </head>
  <body class="body_iframe">
    <script>
      function toggle(){
        var element = document.getElementById("comment_section");
        if(element.style.display == "block") {
          element.style.display = "none";
        } else {
          element.style.display = "block";
        }
      }
    </script>
    <?php
    //Get ID of the post
    if(isset($_GET['post_id'])) {
      $post_id = $_GET['post_id'];
    }
    $user_query = $connexion->prepare('SELECT added_by, user_to FROM posts WHERE id= ?');
    $user_query->execute(array($post_id));
    $row = $user_query->fetch();
    $posted_to = $row['added_by'];
    $user_to = $row['user_to'];
    if(isset($_POST['postComment' . $post_id])){
      $post_body = $_POST['post_body'];
      $post_body = str_replace('\r\n', '\n', $post_body); // keep the breakline
      $post_body = nl2br($post_body);
      $date_time_now = date("Y-m-d H:i:s");
      $insert_post = $connexion->prepare('INSERT INTO post_comments (post_body, posted_by, posted_to, date_added, removed, post_id, likes, user_closed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
      $insert_post->execute(array($post_body, $userLoggedIn, $posted_to, $date_time_now, "no", $post_id, "0", "no" ));
    }
    ?>

<!--------------------------------- FORM to post the comment!!! ---------------------- -->
    <div class="post_comment_section">
      <form id="comment_form" class="comment_form" name="postComment<?php echo $post_id;?>" action="comment_frame.php?post_id=<?php echo $post_id;?>" method="POST">
        <textarea  name="post_body" placeholder="Write your comment here"></textarea>
        <div class="button_comment">
          <button type="submit" name="postComment<?php echo $post_id;?>">POST</button>
        </div>
      </form>
    </div>

<!--------------------------------- Load the comments!!! ---------------------- -->

<?php

$get_comment =  $connexion->prepare('SELECT * FROM post_comments WHERE post_id= ? AND removed = ? ORDER BY id DESC');
$get_comment->execute(array($post_id, "no"));
$count = $get_comment->rowCount();

if($count != 0) {
  while($comment = $get_comment->fetch()) {
    $comment_body = $comment['post_body'];
    $id_comment = $comment['id'];
    $posted_to = $comment['posted_to'];
    $posted_by = $comment['posted_by'];
    $date_added = $comment['date_added'];
    $removed = $comment['removed'];
    $user_closed = $comment['user_closed'];
    $comment_likes = $comment['likes'];
    // time frame
    $date_time_now = date("Y-m-d H:i:s");
    $start_date = new DateTime($date_added); // time of the post
    $send_date = new DateTime($date_time_now); // current time
    $interval = $start_date->diff($send_date); // diffenrence between
    if ($interval->y >= 1) {
      if ($interval == 1) {
          $time_message = $interval->y . " year ago";
      } else {
        $time_message = $interval->y . " years ago";
      }
    }else if ($interval-> m >= 1) {
      if ($interval->d == 0) {
        $days = " ago";
      }else if ($interval->d == 1) {
        $days = $interval->d . " day ago";
      }else {
        $days = $interval->d . " days ago";
      }
      if($interval->m == 1) {
        $time_message = $interval->m . " month" . $days;
      }else {
        $time_message = $interval->m . " months" . $days;
      }
    }else if($interval->d >= 1) {
      if ($interval->d == 1) {
        $time_message = "Yesterday";
      }else {
        $time_message = $interval->d . " days ago";
      }
    }else if($interval->h >= 1) {
      if ($interval->h == 1) {
        $time_message = $interval->h . " hour ago";
      }else {
        $time_message = $interval->h . " hours ago";
      }
    }else if($interval->i >= 1) {
      if ($interval->i == 1) {
        $time_message = $interval->i . " minute ago";
      }else {
        $time_message = $interval->i . " minutes ago";
      }
    }else {
      if ($interval->s < 30 ) {
        $time_message = "Just now";
      }else {
        $time_message = $interval->s . " seconds ago";
      }
    }//End time frame condition
    $user_obj = new User($connexion, $posted_by);
    ?>
    <!--------------------------------- Comments Section!!! ---------------------- -->
    <div class="comment_section">
      <div class="comment_profile_pic">
        <a href="<?php echo $posted_by;?>" target="_parent"><img class="user_post_pic" src="<?php echo $user_obj->getProfilePic();?>" alt="profile-picture" title="<?php echo $posted_by;?>"></a>
      </div>
      <div class="comment_name_time">
        <div class="posted_by_comment">
          <a href="<?php echo $posted_by;?>" target="_parent"><?php echo $user_obj->getFirstAndLastName();?></a><br>
        </div>
        <div class="comment_time_frame">
          <p><?php echo $time_message; ?></p>
        </div>
      </div>
      <div class="comment_left_side">
        <div class="comment_like">

          <button type="button" name="button"><span class="like_comment" id="like_comment_button"><span class="like_comment_button" onclick="likeComment('<?php echo $userLoggedIn; ?>','<?php echo $id_comment; ?>')" id="number_like_<?php echo $id_comment; ?>"></span>
            <i onclick="likeComment('<?php echo $userLoggedIn; ?>','<?php echo $id_comment; ?>')" id="unlike_comment" class="fas fa-heart"></i></span></button>
        </div>
        <div class="comment_like">
          <?php
            if($posted_by == $userLoggedIn || $posted_to == $userLoggedIn ){
              ?>
              <button type="submit" name="delete_comment" id="comment<?php echo $id_comment; ?>"><i class="fas fa-trash"></i></button>
              <?php
            }
          ?>
          <script type="text/javascript">
            setInterval(GetlikeComment, 500, <?php echo $id_comment; ?>, "<?php echo $userLoggedIn; ?>");
          </script>
        </div>
      </div>
      <div class="body_comment">
        <p><?php echo $comment_body; ?></p>
      </div>
    </div>
    <script>
    // Function delete comment
    var comment = document.getElementById("comment<?php echo $id_comment; ?>");
        comment.addEventListener('click', deleteComment);
        function deleteComment(){
          jQuery.ajax({
            type: "POST",
            url: 'includes/handlers/delete_comment.php',
            data: {id: <?php echo $id_comment; ?> },
            success:function(data) {
              location.reload();
            }
          });
        }
    </script>


    <?php
  } // End while loop
  ?> <p class="no_more_comment">No more comment!</p><hr width="50%"> <?php
}
////////////////////////// If there is no comment  \\\\\\\\\\\\\\\\\\\\\\\\\
else {
  echo "<div class='no_comment'><span class='no_comment'>No comment to show !</span></div>";
}
?>

</div>

  </body>
</html>
