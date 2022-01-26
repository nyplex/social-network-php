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

      $body_array = preg_split("/\s+/", $body);
      foreach($body_array as $key => $value) {
        if(strpos($value, "www.youtube.com/watch?v=") !== false) {
          $link = preg_split("!&!", $value);
          $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
          $value = "<br><iframe style='display:block; margin:auto;' width='100%' height='315' src='". $value ."'></iframe><br>";
          $body_array[$key] = $value;
        }
      }
      $body = implode(" ", $body_array);

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
      if($user_to != 'none') {
        $notification = new Notification($this->connexion, $added_by);
        $notification->insertNotification($return_id, $user_to, "profile_post");
      }
      //Update post count for user
      $num_posts = $this->user_obj->getNumPosts();
      $num_posts++;
      $update_query = $this->connexion->prepare('UPDATE users SET num_posts = ? WHERE user_name = ?');
      $update_query->execute(array($num_posts, $added_by));

      $stopWords = "a about above across after again against all almost alone along already
			 also although always among am an and another any anybody anyone anything anywhere are
			 area areas around as ask asked asking asks at away b back backed backing backs be became
			 because become becomes been before began behind being beings best better between big
			 both but by c came can cannot case cases certain certainly clear clearly come could
			 d did differ different differently do does done down down downed downing downs during
			 e each early either end ended ending ends enough even evenly ever every everybody
			 everyone everything everywhere f face faces fact facts far felt few find finds first
			 for four from full fully further furthered furthering furthers g gave general generally
			 get gets give given gives go going good goods got great greater greatest group grouped
			 grouping groups h had has have having he her here herself high high high higher
		   highest him himself his how however i im if important in interest interested interesting
			 interests into is it its itself j just k keep keeps kind knew know known knows
			 large largely last later latest least less let lets like likely long longer
			 longest m made make making man many may me member members men might more most
			 mostly mr mrs much must my myself n necessary need needed needing needs never
			 new new newer newest next no nobody non noone not nothing now nowhere number
			 numbers o of off often old older oldest on once one only open opened opening
			 opens or order ordered ordering orders other others our out over p part parted
			 parting parts per perhaps place places point pointed pointing points possible
			 present presented presenting presents problem problems put puts q quite r
			 rather really right right room rooms s said same saw say says second seconds
			 see seem seemed seeming seems sees several shall she should show showed
			 showing shows side sides since small smaller smallest so some somebody
			 someone something somewhere state states still still such sure t take
			 taken than that the their them then there therefore these they thing
			 things think thinks this those though thought thoughts three through
	     thus to today together too took toward turn turned turning turns two
			 u under until up upon us use used uses v very w want wanted wanting
			 wants was way ways we well wells went were what when where whether
			 which while who whole whose why will with within without work
			 worked working works would x y year years yet you young younger
			 youngest your yours z lol haha omg hey ill iframe wonder else like
       hate sleepy reason for some little yes bye choose";

       $stopWords = preg_split("/[\s,]+/", $stopWords);
       $no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);
       if(strpos($no_punctuation, "height") === false && strpos($no_punctuation, "width") === false
            && strpos($no_punctuation, "http") === false) {
              $no_punctuation = preg_split("/[\s,]+/", $no_punctuation);

              foreach($stopWords as $value) {
                foreach ($no_punctuation as $key => $value2) {
                  if(strtolower($value) == strtolower($value2)){
                    $no_punctuation[$key] = "";
                  }
                }
              }
              foreach($no_punctuation as $value) {
                $this->calculateTrend(ucfirst($value));
              }
            }
    }
  }

  public function calculateTrend($term) {
    if($term != "") {
      $query = $this->connexion->prepare('SELECT * FROM trends WHERE title = ?');
      $query->execute(array($term));
      $num_rows = $query->rowCount();
      if($num_rows == 0) {
        $insert_query = $this->connexion->prepare('INSERT INTO trends (title, hits) VALUES (?, ?)');
        $insert_query->execute(array($term, "1"));
      }else {
        $insert_query = $this->connexion->prepare('UPDATE trends SET hits = hits+1 WHERE title = :title');
        $insert_query->execute(array(
          'title'=> $term
        ));
      }
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

  public function getSinglePost($post_id) {
    $userLoggedIn = $this->user_obj->getUsername();

    $opened_query = $this->connexion->prepare('UPDATE notifications SET opened = :opened WHERE user_to = :userto AND link LIKE :link');
    $opened_query->execute(array(
      'opened'=> "yes",
      'userto'=> $userLoggedIn,
      'link'=> "%=" . $post_id
    ));

      $str = ""; // declare string to return
      $data =  $this->connexion->prepare('SELECT * FROM posts WHERE deleted = ? AND id = ?');
      $data->execute(array("no", $post_id));
      $num_rows = $data->rowCount();
    if($num_rows > 0) {
        $row = $data->fetch();
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
          return;
        }
        // Check if friend
        $user_logged_obj = new User($this->connexion, $userLoggedIn);
        if($user_logged_obj->isFriend($added_by)){

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
                    <div class='post_profile_pic' style='margin-top:100px;'>
                      <img class='user_post_pic' src='".$profile_pic."'>
                    </div>
                    <div class='posted_by'>
                      <a href='".$added_by."'>".$first_name." ".$last_name."</a> ".$user_to."
                    </div>
                    <div class='left_side_post' style='margin-top:100px;'>
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
      } //End is friend condition
      else {
        echo "<div class='all_center'>
                  <div class='post_profile_pic' style='margin-top:100px;'>
                  </div>
                  <div class='each_post' >
                    <div id='post_body'>
                      <p>You can not see this post!</p>
                    </div>
                    <hr class='hr_post_body'>
                  </div>
                </div>";
      }
    }else {
      echo "<div class='all_center'>
                <div class='post_profile_pic' style='margin-top:100px;'>
                </div>
                <div class='each_post' >
                  <div id='post_body'>
                    <p>This post does not exist!</p>
                  </div>
                  <hr class='hr_post_body'>
                </div>
              </div>";
    }
    echo $str;
  } // load Post friend function


} // Class Post

?>
