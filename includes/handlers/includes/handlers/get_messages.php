<?php

include '../../config/config.php';
include '../classes/User.php';

		$userLoggedIn = $_POST['me'];
		$otherUser = $_POST['friend'];


		$data = "";

    $query = $connexion->prepare('UPDATE messages SET opened = ? WHERE user_to = ? AND user_from = ?');
    $query->execute(array("yes", $userLoggedIn, $otherUser));

    $get_messages_query = $connexion->prepare('SELECT messages.* FROM messages JOIN (SELECT MAX(id) id FROM messages WHERE (user_to = :userto AND user_From = :userfrom) OR (user_from = :userF AND user_to = :userT)) x ON messages.id = x.id');
    $get_messages_query->execute(array(
      'userto'=> $userLoggedIn,
      'userfrom'=> $otherUser,
      'userF'=> $userLoggedIn,
      'userT'=> $otherUser

    ));


		while($row = $get_messages_query->fetch()) {

			$id = $row['id'];
			$user_to = $row['user_to'];
			$body = $row['body'];
			$date = $row['date_message'];
			$image = $row['images'];
			$video = $row['video'];
			$friend = new User($connexion, $otherUser);
			$userLogged = new User($connexion, $userLoggedIn);
			$userLogged_pic = $userLogged->getProfilePic();
			$friend_name = $friend->getFirstName();
			$pic = $friend->getProfilePic();

			if($image != "") {
				$imageDiv = "<div class='postedImage'>
											<img onclick='openModal(this)' width='75' src='$image'>
										</div>";
			}
			else {
				$imageDiv = "";
			}
			if($video != "") {
				$videoDiv = "<div class='postedImage'>
								<video src='$video' width='150px' controls></video>
							</div>";
			}
			else {
				$videoDiv = "";
			}


			$info = date("M d Y H:i", strtotime($date));
			$div_top = ($user_to === $userLoggedIn) ? "<div class='message_g' id='green'><img class='profile_user_green' src='".$pic."' height='30' width='30'>" : "<div class='message_b' id='blue'><img class='profile_user_blue' src='".$userLogged_pic."' height='30' width='30'>";

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
				echo "<div class='testclass' id='$id'>" . $data . "</div><div class='checkSeen'></div>";
