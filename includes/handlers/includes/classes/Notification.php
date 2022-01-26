<?php

class notification {
  private $user_obj;
  private $connexion;

  public function __construct($connexion, $user){
    $this->connexion = $connexion;
    $this->user_obj = new User($connexion, $user);
  }

  public function getUnreadNumber () {
    $userLoggedIn = $this->user_obj->getUsername();
    $query = $this->connexion->prepare('SELECT * FROM notifications WHERE opened = ? AND user_to = ?');
    $query->execute(array("no", $userLoggedIn));
    $numRows = $query->rowCount();
    return $numRows;
  }

  public function insertNotification($post_id, $user_to, $type) {
    $userLoggedIn = $this->user_obj->getUsername();
    $userLoggedInName = $this->user_obj->getFirstAndLastName();

    $date_time = date("Y-m-d H:i:s");

    switch ($type) {
      case 'comment':
        $message = $userLoggedInName . " commented on your post";
        break;

      case 'like':
        $message = $userLoggedInName . " like your post";
        break;

        case 'like_comment':
          $message = $userLoggedInName . " like your comment";
          break;

      case 'profile_post':
        $message = $userLoggedInName . " posted on your profile";
        break;

      case 'comment_non_owner':
        $message = $userLoggedInName . " comment on a post you commented on";
        break;

      case 'profile_comment':
        $message = $userLoggedInName . " commented on your post";
        break;
    }
    $link = "post.php?id=" . $post_id;
    $insert_query = $this->connexion->prepare('INSERT INTO notifications (user_to, user_from, message, link, date_time, opened, viewed) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insert_query->execute(array($user_to, $userLoggedIn, $message, $link, $date_time, "no", "no"));
  }

  public function getNotifications($data, $limit) {

    $page = $data['page'];
    $userLoggedIn = $this->user_obj->getUsername();
    $return_string = "";

    if($page == 1)
      $start = 0;
    else
      $start = ($page - 1) * $limit;

    $set_viewed_query = $this->connexion->prepare('UPDATE notifications SET viewed = ? WHERE user_to = ?');
    $set_viewed_query->execute(array("yes", $userLoggedIn));


    $query = $this->connexion->prepare('SELECT * FROM notifications WHERE user_to = ? ORDER BY id DESC');
    $query->execute(array($userLoggedIn));
    $row_count = $query->rowCount();

    if($row_count  == 0) {
      echo "You have no notification!";
      return;
    }

    $num_iterations = 0; //number of messages checked
    $count = 1; // Number of messages posted

    while ($row = $query->fetch()) {

      if($num_iterations++ < $start)
        continue;

      if($count > $limit)
          break;
      else
        $count++;

        $user_from = $row['user_from'];

        $user_data_query = $this->connexion->prepare('SELECT * FROM users WHERE user_name = ?');
        $user_data_query->execute(array($user_from));
        $user_data = $user_data_query->fetch();


        // time frame
        $date_time_now = date("Y-m-d H:i:s");
        $start_date = new DateTime($row['date_time']); // time of the post
        $send_date = new DateTime($date_time_now); // current time
        $interval = $start_date->diff($send_date); // diffenrence between
        if ($interval->y >= 1) {
          if ($interval == 1) {
              $time_message = $interval->y . " year ago";
          } else {
            $time_message = $interval->y . " years ago";
          }
        } else if ($interval-> m >= 1) {
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
        } else if($interval->d >= 1) {
            if ($interval->d == 1) {
              $time_message = "Yesterday";
            } else {
              $time_message = $interval->d . " days ago";
            }
        } else if($interval->h >= 1) {
            if ($interval->h == 1) {
              $time_message = $interval->h . " hour ago";
            } else {
              $time_message = $interval->h . " hours ago";
            }
        } else if($interval->i >= 1) {
            if ($interval->i == 1) {
              $time_message = $interval->i . " minute ago";
            } else {
              $time_message = $interval->i . " minutes ago";
            }
        } else {
            if ($interval->s < 30 ) {
              $time_message = "Just now";
            } else {
              $time_message = $interval->s . " seconds ago";
            }
          }

      $opened = $row['opened'];
      $style = (isset($row['opened']) && $row['opened'] == 'no') ? "background-color:#b3d6ff;" : "";

      $return_string .=   "<div  class='dropdown_each_result'><a class='result_dropdown'  href='".$row['link']."' ><div class='profile_pic_dropdown'><img  width='50px' src='" .$user_data['profile_pic']. "'></div><div id='result_notif' style='".$style."' class='side_result_dropdown'><div class='names_result_dropdwon'>
          <span>".$row['message']."</span></div><div class='time_frame_dropdown'><span>".$time_message."</span></div></div></a></div><hr>";

    }

    //if posts were loaded
    if($count > $limit) {
      $return_string .= "<input type='hidden' class='nextPageDropDownData' value='".($page + 1)."'><input type='hidden' class='noMoreDropdownData' value='false'>";
    } else {
      $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>No more notification!</p>";
    }
    return $return_string;
  }

}

  ?>
