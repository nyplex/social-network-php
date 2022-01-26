<?php

include '../../config/config.php';
include '../classes/User.php';
include '../classes/Message.php';

		$userLoggedIn = $_POST['me'];

		$return_string = "";
		$convos = array();

    $query = $connexion->prepare('SELECT * FROM messages WHERE user_to = ? OR user_from = ? ORDER BY id DESC');
    $query->execute(array($userLoggedIn, $userLoggedIn));

		while($row = $query->fetch()) {

			$user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

			if(!in_array($user_to_push, $convos))
				array_push($convos, $user_to_push);
		}

		foreach($convos as $username) {

      $is_unread_query = $connexion->prepare('SELECT * FROM messages WHERE (user_to = :userto AND user_from = :userfrom) OR (user_from = :userf AND user_to = :usert) ORDER BY id DESC');
      $is_unread_query->execute(array(
        'userto'=> $userLoggedIn,
        'userfrom'=> $username,
        'userf'=> $userLoggedIn,
        'usert'=> $username
      ));
      $row = $is_unread_query->fetch();

			$style = ($row['opened'] == 'no') ? "background-color: #DDEDFF" : "";

			$user_found_obj = new User($connexion, $username);

			$details = new Message($connexion, $userLoggedIn);
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


			else  {

				$return_string .= "<div style='".$style."' class='main_content_left_side'><a href='messages.php?u=$username'><div width='50px' style='float:left;'><img width='50px' class='profile_pic_left_content' src='". $user_found_obj->getProfilePic() ."'></div><div class='after_profile_pic'><div class='names_left_content'>
				<span>".$user_found_obj->getFirstAndLastName()."</span></div><div class='time_frame_left_content'><span>". $latest_message_details[1] ."</span></div><div class='message_content_left_Side'><span>" . $latest_message_details[0] . $split . "</span></div></div></a></div>";
			}

		}

		echo $return_string;
