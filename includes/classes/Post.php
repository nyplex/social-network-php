<?php

class Post {
  private $user_obj;
  private $connexion;

  public function __construct($connexion, $user){
    $this->connexion = $connexion;
    $this->user_obj = new User($connexion, $user);;
  }

  public function submitPost($body, $user_to, $imageName, $videoName ) {
    $body = strip_tags($body); //remove html tags
    $body = str_replace('\r\n', '\n', $body); // keep the breakline
    $body = nl2br($body);
    $check_empty = preg_replace('/\s+/', '', $body); // remove all spaces

    if($check_empty != "" || $imageName != "" || $videoName != "") {
      //current date and time
      $date_added = date("Y-m-d H:i:s");
      // Get username
      $added_by = $this->user_obj->getUsername();
      //if user is on his own profil , user_to is none
      if($user_to == $added_by) {
        $user_to = "none";
      }
      //insert post inthe data base
      $query =$this->connexion->prepare('INSERT INTO posts (body, added_by, user_to, date_added, user_closed, deleted, likes, comments, images, video) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $query->execute(array($body, $added_by, $user_to, $date_added, "no", "no", "0", "0", $imageName, $videoName));
      // return id of the post
      $return_id = $this->connexion->lastInsertId();
      //insert notification

      //Update post count for user
      $num_posts = $this->user_obj->getNumPosts();
      $num_posts++;
      $update_query = $this->connexion->prepare('UPDATE users SET num_posts = ? WHERE user_name = ?');
      $update_query->execute(array($num_posts, $added_by));
    }
  }

  public function loadPostFriends($data, $limit) {
    $page = $data['page'];
    $userLoggedIn = $this->user_obj->getUsername();
    if ($page == 1)
      $start = 0;
    else
      $start = ($page - 1) * $limit;
      $str = ""; // declare string to return
      $data =  $this->connexion->prepare('SELECT * FROM posts WHERE deleted = ? ORDER BY id DESC ');
      $data->execute(array("no"));
      $num_rows = $data->rowCount();
    if($num_rows > 0) {

      $num_iterations = 0; // number of results checked but not necesserily posted_by
      $count = 1;

      while ($row = $data->fetch()){
        $id = $row['id'];
        $body = $row['body'];
        $added_by = $row['added_by'];
        $date_time = $row['date_added'];
        $imagePath = $row['images'];
        $videoPath = $row['video'];
        //prepare user_to string so can be included even if not posted to a user
        if ($row['user_to'] == "none") {
          $user_to = "";
        }else{
          $user_to_obj = new User($this->connexion, $row['user_to']);
          $user_to_name = $user_to_obj->getFirstAndLastName();
          $user_to = "<a> to</a> <a href='" .$row['user_to'] . "'>" . $user_to_name . "</a>" ;
        }
        //Check if user who posted has their account closed
        $added_by_obj = new User($this->connexion, $added_by);
        if ($added_by_obj->isClosed()) {
          continue;
        }
        // Check if friend
        $user_logged_obj = new User($this->connexion, $userLoggedIn);
        if($user_logged_obj->isFriend($added_by)){

          if($num_iterations++ < $start)
            continue;
            // Once 10 posts loaded , break
          if($count > $limit) {
            break;
          } else {
            $count++;
          }
          // Delete post button
          if($userLoggedIn == $added_by && $row['user_to'] == "none" || $userLoggedIn == $row['user_to'])
            $delete_button = "<button class='delete_post_button' id='post$id'><i class='fas fa-trash'></i></button>";
          else
            $delete_button = "";

          $user_details_query = $this->connexion->prepare('SELECT first_name, last_name, profile_pic  FROM users WHERE user_name = ? ');
          $user_details_query->execute(array($added_by));
          $user_row = $user_details_query->fetch();
          $first_name = $user_row['first_name'];
          $last_name = $user_row['last_name'];
          $profile_pic = $user_row['profile_pic'];

          ?>
          <script>
            function toggle<?php echo $id; ?>(){
              var target = $(event.target);
              if(!target.is("a")) {
                var element = document.getElementById("toggleComment<?php echo $id; ?>");
                  if(element.style.display == "block") {
                    element.style.display = "none";
                  } else {
                    element.style.display = "block";
                  }
                }
              }
          </script>
          <?php

          $comment_check = $this->connexion->prepare("SELECT * FROM post_comments WHERE post_id = ? AND removed = ?");
          $comment_check->execute(array($id, "no"));
          $comment_check_num = $comment_check->rowCount();
          if($comment_check_num <= 1) {
            $comment = "comment";
          }else {
            $comment = "comments";
          }

          // time frame
          $date_time_now = date("Y-m-d H:i:s");
          $start_date = new DateTime($date_time); // time of the post
          $send_date = new DateTime($date_time_now); // current time
          $interval = $start_date->diff($send_date); // diffenrence between
          if ($interval->y >= 1) {
            if ($interval == 1) {
              $time_message = $interval->y . " year ago";
            }else {
              $time_message = $interval->y . " years ago";
            }
          }else if ($interval-> m >= 1) {
            if ($interval->d == 0) {
              $days = " ago";
            } else if ($interval->d == 1) {
              $days = $interval->d . " day ago";
            } else {
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
            } else {
              $time_message = $interval->d . " days ago";
            }
          }else if($interval->h >= 1) {
            if ($interval->h == 1) {
              $time_message = $interval->h . " h ago";
            } else {
              $time_message = $interval->h . " h ago";
            }
          }else if($interval->i >= 1) {
            if ($interval->i == 1) {
              $time_message = $interval->i . " min ago";
            } else {
              $time_message = $interval->i . " min ago";
            }
          }else {
            if ($interval->s < 30 ) {
              $time_message = "Just now";
            } else {
              $time_message = $interval->s . " seconds ago";
            }
          }

          if($imagePath != "") {
            $imageDiv = "<div class='posted_image posted_media'><img onclick='openModal(this)' src='".$imagePath."'></div>";
          }else {
            $imageDiv = "";
          }
          if($videoPath != "") {
            $videoDiv = "<div class='posted_image posted_media'><video width='300px' src='".$videoPath."' controls></video></div>";
          }else {
            $videoDiv = "";
          }
          $str .= "<div class='all_center'>
                    <div class='post_profile_pic'>
                      <img class='user_post_pic' src='".$profile_pic."'>
                    </div>
                    <div class='posted_by'>
                      <a href='".$added_by."'>".$first_name." ".$last_name."</a> ".$user_to."
                    </div>
                    <div class='left_side_post'>
                      <div class='time_frame_post'>
                        <p class='time_frame'>".$time_message."</p>
                      </div>
                      <div class='delete_button_post'>
                        ".$delete_button."
                      </div>
                    </div>
                    <div class='each_post' >
                      <div id='post_body'>
                        <p>".$body."</p>".$imageDiv." ".$videoDiv."
                      </div>
                      <hr class='hr_post_body'>
                      <div class='newsFeedPostOptions'>
                        <span onClick='javascript:toggle$id()' class='link_comment' style='text-decoration:none;'>$comment_check_num $comment <i class='far fa-comment'></i></span>
                        <span class='like_post' id='like_post_button'><p class='like_post_button' onclick='likePost(\"$userLoggedIn\",\"$id\")' id='number_like_$id'></p><i onclick='likePost(\"$userLoggedIn\",\"$id\")' id='unlike_post' class='fas fa-heart'></i></span>
                      </div>
                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;'>
                      <iframe onClick='this.style.height=(this.contentDocument.body.scrollHeight+45) +'px';' scrolling='yes' src='comment_frame.php?post_id=$id' id='comment_iframe' class='comment_iframe' frameborder='0'></iframe>
                    </div>
                  </div>";
                  ?>
                  <script type="text/javascript">
                    setInterval(ilike, 500, <?php echo $id; ?>, "<?php echo $userLoggedIn; ?>");
                  </script>
                  <?php

        } //End is friend condition
        ?>
          <script>
            $(document).ready(function(){
              $('#post<?php echo $id; ?>').on('click', function(){
                bootbox.confirm("Are you sure you want to delete this post", function(result){
                  $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
                  if(result)
                    location.reload();
                });
              });
            });
          </script>
        <?php
      } // End while loop

      if($count > $limit)
        $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                <input type='hidden' class='noMorePosts' value='false'>";
      else
        $str .= "<div class='main-column column' id='nomore'>
                <input type='hidden' class='noMorePosts' value='true'><p class='no-more-post' style='text-align: center;'> No more posts to show!</p></div>";
    }
    echo $str;
  } // load Post friend function

  public function loadProfilePosts($data, $limit){
    $page = $data['page'];
    $profileUser = $data['profileUsername'];
    $userLoggedIn = $this->user_obj->getUsername();
    if ($page == 1)
      $start = 0;
    else
      $start = ($page - 1) * $limit;
      $str = ""; // declare string to return
      $data_query =  $this->connexion->prepare('SELECT * FROM posts WHERE deleted = ? AND ((added_by = ?) OR user_to = ?) ORDER BY id DESC ');
      $data_query->execute(array("no", $profileUser, $profileUser));
      $num_rows = $data_query->rowCount();
    if($num_rows > 0) {

      $num_iterations = 0; // number of results checked but not necesserily posted_by
      $count = 1;

      while ($row = $data_query->fetch()){
        $id = $row['id'];
        $body = $row['body'];
        $added_by = $row['added_by'];
        $date_time = $row['date_added'];
        $imagePath = $row['images'];
        $videoPath = $row['video'];

        //prepare user_to string so can be included even if not posted to a user
        if ($row['user_to'] == $profileUser) {
          $user_to = "";
        }else if($row['user_to'] == "none") {
          $user_to = "";
        }
        else{
          $user_to_obj = new User($this->connexion, $row['user_to']);
          $user_to_name = $user_to_obj->getFirstAndLastName();
          $user_to = "<a> to</a> <a href='" .$row['user_to'] . "'>" . $user_to_name . "</a>" ;
        }

          if($num_iterations++ < $start)
            continue;
            // Once 10 posts loaded , break
          if($count > $limit) {
            break;
          } else {
            $count++;
          }
          // Delete post button
          if($userLoggedIn == $added_by && $row['user_to'] == "none" || $userLoggedIn == $row['user_to'])
            $delete_button = "<button class='delete_post_button' id='post$id'><i class='fas fa-trash'></i></button>";
          else
            $delete_button = "";

          $user_details_query = $this->connexion->prepare('SELECT first_name, last_name, profile_pic  FROM users WHERE user_name = ? ');
          $user_details_query->execute(array($added_by));
          $user_row = $user_details_query->fetch();
          $first_name = $user_row['first_name'];
          $last_name = $user_row['last_name'];
          $profile_pic = $user_row['profile_pic'];

          ?>
          <script>
            function toggle<?php echo $id; ?>(){
              var target = $(event.target);
              if(!target.is("a")) {
                var element = document.getElementById("toggleComment<?php echo $id; ?>");
                  if(element.style.display == "block") {
                    element.style.display = "none";
                  } else {
                    element.style.display = "block";
                  }
                }
              }
          </script>
          <?php

          $comment_check = $this->connexion->prepare("SELECT * FROM post_comments WHERE post_id = ? AND removed = ?");
          $comment_check->execute(array($id, "no"));
          $comment_check_num = $comment_check->rowCount();
          if($comment_check_num <= 1) {
            $comment = "comment";
          }else {
            $comment = "comments";
          }

          // time frame
          $date_time_now = date("Y-m-d H:i:s");
          $start_date = new DateTime($date_time); // time of the post
          $send_date = new DateTime($date_time_now); // current time
          $interval = $start_date->diff($send_date); // diffenrence between
          if ($interval->y >= 1) {
            if ($interval == 1) {
              $time_message = $interval->y . " year ago";
            }else {
              $time_message = $interval->y . " years ago";
            }
          }else if ($interval-> m >= 1) {
            if ($interval->d == 0) {
              $days = " ago";
            } else if ($interval->d == 1) {
              $days = $interval->d . " day ago";
            } else {
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
            } else {
              $time_message = $interval->d . " days ago";
            }
          }else if($interval->h >= 1) {
            if ($interval->h == 1) {
              $time_message = $interval->h . " h ago";
            } else {
              $time_message = $interval->h . " h ago";
            }
          }else if($interval->i >= 1) {
            if ($interval->i == 1) {
              $time_message = $interval->i . " min ago";
            } else {
              $time_message = $interval->i . " min ago";
            }
          }else {
            if ($interval->s < 30 ) {
              $time_message = "Just now";
            } else {
              $time_message = $interval->s . " seconds ago";
            }
          }

          if($imagePath != "") {
            $imageDiv = "<div class='posted_image posted_media'><img onclick='openModal(this)' src='".$imagePath."'></div>";
          }else {
            $imageDiv = "";
          }
          if($videoPath != "") {
            $videoDiv = "<div class='posted_image posted_media'><video width='300px' src='".$videoPath."' controls></video></div>";
          }else {
            $videoDiv = "";
          }
          $str .= "<div class='all_center'>
                    <div class='post_profile_pic'>
                      <img class='user_post_pic' src='".$profile_pic."'>
                    </div>
                    <div class='posted_by'>
                      <a href='".$added_by."'>".$first_name." ".$last_name."</a> ".$user_to."
                    </div>
                    <div class='left_side_post'>
                      <div class='time_frame_post'>
                        <p class='time_frame'>".$time_message."</p>
                      </div>
                      <div class='delete_button_post'>
                        ".$delete_button."
                      </div>
                    </div>
                    <div class='each_post' >
                      <div id='post_body'>
                        <p>".$body."</p>".$imageDiv." ".$videoDiv."
                      </div>
                      <hr class='hr_post_body'>
                      <div class='newsFeedPostOptions'>
                        <span onClick='javascript:toggle$id()' class='link_comment'>$comment_check_num $comment <i class='far fa-comment'></i></span>
                        <span class='like_post' id='like_post_button'><p class='like_post_button' onclick='likePost(\"$userLoggedIn\",\"$id\")' id='number_like_$id'></p><i onclick='likePost(\"$userLoggedIn\",\"$id\")' id='unlike_post' class='fas fa-heart'></i></span>
                      </div>
                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;'>
                      <iframe onClick='this.style.height=(this.contentDocument.body.scrollHeight+45) +'px';' scrolling='yes' src='comment_frame.php?post_id=$id' id='comment_iframe' class='comment_iframe' frameborder='0'></iframe>
                    </div>
                  </div>";
                  ?>
                  <script type="text/javascript">
                    setInterval(ilike, 500, <?php echo $id; ?>, "<?php echo $userLoggedIn; ?>");
                  </script>

          <script>
            $(document).ready(function(){
              $('#post<?php echo $id; ?>').on('click', function(){
                bootbox.confirm("Are you sure you want to delete this post", function(result){
                  $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
                  if(result)
                    location.reload();
                });
              });
            });
          </script>
        <?php
      } // End while loop

      if($count > $limit)
        $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                <input type='hidden' class='noMorePosts' value='false'>";
      else
        $str .= "<div class='main-column column' id='nomore'>
                <input type='hidden' class='noMorePosts' value='true'><p class='no-more-post' style='text-align: center;'> No more posts to show!</p></div>";
    }
    echo $str;
  } // load Post friend function

} // Class Post

?>
