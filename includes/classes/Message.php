<?php

class Message {
  private $user_obj;
  private $connexion;

  public function __construct($connexion, $user){
    $this->connexion = $connexion;
    $this->user_obj = new User($connexion, $user);
  }

  public function getMostRecentUser() {
    $userLoggedIn = $this->user_obj->getUsername();

    $query = $this->connexion->prepare('SELECT user_to, user_from FROM messages WHERE user_to = ? OR user_from = ? ORDER BY id DESC LIMIT 1');
    $query->execute(array($userLoggedIn, $userLoggedIn));
    $num_rows = $query->rowCount();

    if($num_rows == 0)
      return false;

    $row = $query->fetch();
    $user_to = $row['user_to'];
    $user_from = $row['user_from'];

    if($user_to != $userLoggedIn)
      return $user_to;
    else
      return $user_from;

  }

  public function SendMessage($user_to, $body, $date, $imageName, $videoName){
    if($body != "" || $imageName !== "" || $videoName !== "") {
      if($imageName !== "" && strpos($imageName, "../../assets/images/messages") !== false) {
				$imageName = str_replace("../../assets/images/messages", "assets/images/messages", $imageName);
			}
      if($videoName !== "" && strpos($videoName, "../../assets/videos/messages") !== false) {
				$videoName = str_replace("../../assets/videos/messages", "assets/videos/messages", $videoName);
			}
      $userLoggedIn = $this->user_obj->getUsername();
      $query = $this->connexion->prepare('INSERT INTO messages (user_to, user_from, body, date_message, opened, viewed, deleted, images, video) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $query->execute(array($user_to, $userLoggedIn, $body, $date, "no", "no", "no", $imageName, $videoName));
    }
  }

  public function getMessages($otherUser) {
    $userLoggedIn = $this->user_obj->getUsername();
    $profile_pic_user_logged_in = $this->user_obj->getProfilePic();
    $data = "";
    $query = $this->connexion->prepare('UPDATE messages SET opened = ? WHERE user_to = ? AND user_from = ?');
    $query->execute(array("yes", $userLoggedIn, $otherUser));
    $get_message_query = $this->connexion->prepare('SELECT * FROM messages WHERE (user_to = ? AND user_from = ?) OR (user_from = ? AND user_to = ?) AND deleted = ?');
    $get_message_query->execute(array($userLoggedIn, $otherUser, $userLoggedIn, $otherUser, "no"));
    while ($row = $get_message_query->fetch()) {
      $id = $row['id'];
			$user_to = $row['user_to'];
			$user_from = $row['user_from'];
			$body = $row['body'];
			$date = $row['date_message'];
			$image = $row['images'];
      $video = $row['video'];
			$friend = new User($this->connexion, $otherUser);
			$friend_name = $friend->getFirstName();
			$pic = $friend->getProfilePic();
      if($image != "") {
				$imageDiv = "<div class='postedImage'>
								<img onclick='openModal(this)' width='70px' src='$image'>
							</div>";
			}else {
				$imageDiv = "";
			}
      if($video != "") {
				$videoDiv = "<div class='postedImage'>
								<video src='$video' width='150px' controls></video>
							</div>";
			}else {
				$videoDiv = "";
			}


			$info = date("M d Y H:i", strtotime($date));
			$div_top = ($user_to === $userLoggedIn) ? "<div class='message_g' id='green'><img class='profile_user_green' src='".$pic."' height='30' width='30'>" : "<div class='message_b' id='blue'><img class='profile_user_blue' src='".$profile_pic_user_logged_in."' height='30' width='30'>";
			$body_array = preg_split("/\s+/", $body);
			foreach($body_array as $key => $value) {
				if(strpos($value, "www.youtube.com/watch?v=") !== false) {
					$link = preg_split("!&!", $value);
					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
					$value = "<p><iframe width='400' height='300' src='" . $value . "'></iframe></p>";
					$body_array[$key] = $value;
					$body = implode(" ", $body_array);
				}
			}
			$data = $data . $div_top . "<div class='time_frame_message'>" . $info . "</div>" . "<div class='text_content_user'>" . "<div class='message_content_user'>" . nl2br($body) . "</div>" . "<div>" . $imageDiv . "</div>" . "<div>" . $videoDiv . "</div>" . "</div></div><br>";
		}

		if($data !== "")
			return "<div class='testclass' id='$id'>" . $data . "</div>";
  }

  public function getLatestMessage($userLoggedIn, $user2) {
    $details_array = array();

    $query = $this->connexion->prepare('SELECT body, user_to, date_message, images, video FROM messages WHERE (user_to = ? AND user_from = ?) or (user_to = ? AND user_from = ?) ORDER BY id DESC LIMIT 1');
    $query->execute(array($userLoggedIn, $user2, $user2, $userLoggedIn));
    $row = $query->fetch();
    $sent_by = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

    // time frame
    $date_time_now = date("Y-m-d H:i:s");
    $start_date = new DateTime($row['date_message']); // time of the post
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
      if(strpos($row['body'], "www.youtube.com") !== false) {

			$sent_by = ($row['user_to'] === $userLoggedIn) ? "They" : "You";

			$row['body'] = " sent a clip";
		}

		if(strpos($row['body'], "https://") !== false && strpos($row['body'], "www.youtube.com") === false) {

			$sent_by = ($row['user_to'] === $userLoggedIn) ? "They" : "You";

			$row['body'] = " sent a link";
		}

		array_push($details_array, $sent_by);
    array_push($details_array, $time_message);
    if($row['body'] == "" && $row['images'] != "") {
      array_push($details_array, "picture");
    } else {
      array_push($details_array, $row['body']);
    }



	   return $details_array;

  }

  public function getConvos() {
    $userLoggedIn = $this->user_obj->getUsername();
    $return_string = "";
    $convos = array();

    $query = $this->connexion->prepare('SELECT user_to, user_from FROM messages WHERE user_to = ? OR user_from = ? ORDER BY id DESC');
    $query->execute(array($userLoggedIn, $userLoggedIn));

    while($row = $query->fetch()) {
      $user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

      if(!in_array($user_to_push, $convos)) {
        array_push($convos, $user_to_push);
      }
    }
    foreach($convos as $username) {

      $is_unread_query= $this->connexion->prepare('SELECT * FROM messages WHERE (user_to = ? AND user_from = ?) OR (user_from = ? AND user_to = ?) ORDER BY id DESC');
      $is_unread_query->execute(array($userLoggedIn, $username, $userLoggedIn, $username));
      $row = $is_unread_query->fetch();

			$style = ($row['opened'] == 'no') ? "background-color: #DDEDFF" : "";

			$user_found_obj = new User($this->connexion, $username);

			$details = new Message($this->connexion, $userLoggedIn);
			$latest_message_details = $details->getLatestMessage($userLoggedIn, $username);


      $dots = (strlen($latest_message_details[2]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[2], 12);
			$split = $split[0] . $dots;

			if($row['opened'] === 'yes' && $row['user_from'] === $userLoggedIn && $row['user_to'] === $username) {

				$latest_message_details[2] .= " <b>✓</b>";
			}

			if ($row['opened'] === 'no' && $row['user_from'] === $userLoggedIn && $row['user_to'] === $username) {

				$style = "";
				$latest_message_details[2] .= " <b>←</b>";
			}

			if($row['opened'] === 'no' && $row['user_to'] === $userLoggedIn && $row['user_from'] === $username) {

				$style = "background-color: #DDEDFF";
			}

			if(strpos($latest_message_details[1], "http://") !== false) {

        $return_string .= "<div style='".$style."' class='main_content_left_side'><a href='messages.php?u=$username'><div width='50px' style='float:left;'><img width='50px' class='profile_pic_left_content' src='". $user_found_obj->getProfilePic() ."'></div><div class='after_profile_pic'><div class='names_left_content'>
				<span>".$user_found_obj->getFirstAndLastName()."</span></div><div class='time_frame_left_content'><span>". $latest_message_details[2] ."</span></div></div></a></div>";
			}

			else {

        $return_string .= "<div style='".$style."' class='main_content_left_side'><a href='messages.php?u=$username'><div width='50px' style='float:left;'><img width='50px' class='profile_pic_left_content' src='". $user_found_obj->getProfilePic() ."'></div><div class='after_profile_pic'><div class='names_left_content'>
				<span>".$user_found_obj->getFirstAndLastName()."</span></div><div class='time_frame_left_content'><span>". $latest_message_details[1] ."</span></div><div class='message_content_left_Side'><span>" . $latest_message_details[0] . $split . "</span></div></div></a></div>";
			}

		}

		echo $return_string;
  }

  public function getConvosDropdown($data, $limit) {
    // link for new message on the dropdown messages
    echo "<div style='text-align:center;'><a href='messages.php?u=new'>New Message</a></div>";


    $page = $data['page'];
    $userLoggedIn = $this->user_obj->getUsername();
    $return_string = "";
    $convos = array();

    if($page == 1)
      $start = 0;
    else
      $start = ($page - 1) * $limit;

    $set_viewed_query = $this->connexion->prepare('UPDATE messages SET viewed = ? WHERE user_to = ?');
    $set_viewed_query->execute(array("yes", $userLoggedIn));


    $query = $this->connexion->prepare('SELECT user_to, user_from FROM messages WHERE user_to = ? OR user_from = ? ORDER BY id DESC');
    $query->execute(array($userLoggedIn, $userLoggedIn));

    while($row = $query->fetch()) {
      $user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

      if(!in_array($user_to_push, $convos)) {
        array_push($convos, $user_to_push);
      }
    }

    $num_iterations = 0; //number of messages checked
    $count = 1; // Number of messages posted

    foreach($convos as $username) {

      if($num_iterations++ < $start)
        continue;

      if($count > $limit)
          break;
      else
        $count++;

        $is_unread_query = $this->connexion->prepare('SELECT * FROM messages WHERE (user_to = ? AND user_from = ?) OR (user_from = ? AND user_to = ?) ORDER BY id DESC');
        $is_unread_query->execute(array($userLoggedIn, $username, $userLoggedIn, $username));
        $row = $is_unread_query->fetch();

  			$style = ($row['opened'] == 'no') ? "background-color: #DDEDFF" : "";

  			$user_found_obj = new User($this->connexion, $username);
  			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);

  			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
  			$split = str_split($latest_message_details[1], 12);
  			$split = $split[0] . $dots;

  			if($row['opened'] === 'yes' && $row['user_from'] === $userLoggedIn && $row['user_to'] === $username) {

  				$latest_message_details[2] .= " <b>✓</b>";
  			}

  			if ($row['opened'] === 'no' && $row['user_from'] === $userLoggedIn && $row['user_to'] === $username) {

  				$style = "";
  				$latest_message_details[2] .= " <b>←</b>";
  			}

  			if(strpos($latest_message_details[1], "http://") !== false) {

  				$return_string .= "<a href='messages.php?u=$username'> <div class='user_found_messages' style='" . $style . "'>
  								<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>" . $user_found_obj->getFirstAndLastName() . "<br><span class='timestamp_smaller' id='grey'>" . $latest_message_details[2] . "</span>
  								    <p id='grey' style='margin: 0;'></p></div></a>";
  			}

  			else {
  				$return_string .= "<a href='messages.php?u=$username'> <div class='user_found_messages' style='" . $style . "'>
  								<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>" . $user_found_obj->getFirstAndLastName() . "<br><span class='timestamp_smaller' id='grey'>" . $latest_message_details[2] . "</span>
  								    <p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split  . "</p></div></a>";
  			}

  		}

  		//If posts were loaded

  		if($count > $limit)
  			$return_string .="<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
  		else
  			$return_string .="<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>No more messages to load!</p>";

  	return $return_string;
  }

  public function getUnreadNumber () {
    $userLoggedIn = $this->user_obj->getUsername();
    $query = $this->connexion->prepare('SELECT * FROM messages WHERE viewed = ? AND user_to = ?');
    $query->execute(array("no", $userLoggedIn));
    $numRows = $query->rowCount();
    return $numRows;
  }
}
 ?>
