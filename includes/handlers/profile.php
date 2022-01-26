<?php
include("includes/header.php");
$message_obj = new Message($connexion, $userLoggedIn);
$user_ob = new User($connexion, $userLoggedIn);
/////////////////////////////// Get username details for profile page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if(isset($_GET['profile_username'])) {
  $username = $_GET['profile_username'];
  $user_details_query = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
  $user_details_query->execute(array($username));
  $user_array = $user_details_query->fetch();
  $user_num_row = $user_details_query->rowCount();
  $num_friends = (substr_count($user_array['friend_array'], ",")) -1;
  $orgDate = $user_array['date_of_birth'];
  $birthday = date("jS F Y", strtotime($orgDate));
  $orgDate2 = $user_array['signup_date'];
  $signup = date("F Y", strtotime($orgDate2));
  $areWeFriend = $user_ob->isFriend($user_array['user_name']);
  $mutual_friend = $user_ob->getMutualFriend($user_array['user_name']);

  if($user_num_row == 0) {
    header('Location: index.php');
  }
  if($areWeFriend) {
    $newsAcctive = "active";
    $tab = "";
    $bioActive = "";
    $photoAcctive = "";
    $sty = "block";
    $linetrought = "text-decoration:line-through;";
    $line = "";
  } else {
    $photoAcctive = "disabled";
    $newsAcctive = "disabled";
    $bioActive = "disabled";
    $tab = "active";
    $sty = "none";
    $linetrought = "";
    $line = "text-decoration:line-through;";
  }
  if($user_array['user_name'] == $userLoggedIn) {
    $tab = "disabled";
    $linetrought = "text-decoration:line-through;";
    $line = "";
  }else {
    $tab = "";
    $linetrought = "";
  }

}

/////////////////////////////// Friends button handlers \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if(isset($_POST['remove_friend'])) {
  $user = new User($connexion, $userLoggedIn);
  $user->removeFriend($username);
}
if(isset($_POST['add_friend'])) {
  $user = new User($connexion, $userLoggedIn);
  $user->sendRequest($username);
}
if(isset($_POST['respond_request'])) {
  header('Location: requests.php');
}
/////////////////////////////// When somoeone post something on user's profile \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if (isset($_POST['post'])) {
  $uploadOk = true;
  $imageName = $_FILES['pictureToUpload']['name'];
  $videoName = $_FILES['videoToUpload']['name'];
  $errorMessage = "";
  // Check if user has loaded an image
  if($imageName != "") {
    $targetDir = "assets/media/posts/images/";
    //Change the name of the image
    $imageName = $targetDir . uniqid() . basename($imageName);
    $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);
    //Check the size of the image
    if($_FILES['pictureToUpload']['size'] > 10000000) {
      $errorMessage = "Sorry your file is too large";
      $uploadOk = 0;
    }
    //Check the format of the image
    if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg" && strtolower($imageFileType) != "Pjpeg") {
      $errorMessage = "Only jpeg, png, jpg files are allowed";
      $uploadOk = 0;
    }
    //If all ok move the image in the final folder
    if($uploadOk) {
      if(move_uploaded_file($_FILES['pictureToUpload']['tmp_name'], $imageName)) {
        $uploadOk = true;
      }else {
        $uploadOk = 0;
        $errorMessage = "rate";
      }
    }
    // Check if user has loaded a video
  } if ($videoName != "") {
    $targetDirV = "assets/media/posts/videos/";
    //Change the name of the video
    $videoName = $targetDirV . uniqid() . basename($videoName);
    $videoFileType = pathinfo($videoName, PATHINFO_EXTENSION);
    //Check the size of the video
    if($_FILES['videoToUpload']['size'] > 1000000000000000000000000000000000) {
      $errorMessage = "Sorry your file is too large";
      $uploadOk = 0;
    }
    //Check the format of the video
    if(strtolower($videoFileType) != "m4v" && strtolower($videoFileType) != "avi" && strtolower($videoFileType) != "mp4" && strtolower($videoFileType) != "mov" && strtolower($videoFileType) != "mpg" && strtolower($videoFileType) != "mpeg") {
      $errorMessage = "Only jpeg, png, jpg files are allowed";
      $uploadOk = 0;
    }
    //If all ok move the video in the final folder
    if($uploadOk) {
      if(move_uploaded_file($_FILES['videoToUpload']['tmp_name'], $videoName)) {
        $uploadOk = true;
      }else {
        $uploadOk = 0;
        $errorMessage = "rate";
      }
    }
}
// If everything ok, send data to POST class.
  if($uploadOk == true) {
    $post = new Post($connexion, $userLoggedIn);
    $post->submitPost($_POST['post_text'], $username, $imageName, $videoName);
    header('Location: profile.php?profile_username='.$username);
  }else{
    echo "ERROR";
  }
}
/////////////////////////////// Friend online or not \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if($user_array['login'] == "yes") {
  $dotOnline = "<i id='dotOnline' class='fas fa-dot-circle'></i>";
} else {
  $dotOnline = "<i id='dotOffline' class='fas fa-dot-circle'></i>";
}
?>

<!-- ---------------------------------- LEFT COLUMN ----------------------------------- -->
    <div style="display:none;" class="column popular_trend left_column" id='trend_profile'>
      <h3>Popular <i class="fas fa-fire-alt"></i></h3>
      <div class="trend">
        <?php
        $query_trend = $connexion->query('SELECT * FROM trends ORDER BY hits DESC LIMIT 10');
        foreach ($query_trend as $row) {
          $word = $row['title'];
          $word_dot = strlen($word) >= 14 ? "..." : "";
          $trimmed_word = str_split($word, 14);
          $trimmed_word = $trimmed_word[0];
          echo "<a href='https://www.google.co.uk/search?hl=en&q=".$word."&btnG=Recherche+Google&meta=.' target='_blank'>".$trimmed_word. $word_dot. "</a><br>";
        }
        ?>
      </div>
    </div>

<!-- ---------------------------------- LEFT COLUMN ----------------------------------- -->
<div class="column popular_trend left_column" id='profile_left'>
  <h3>Informations <i class="fas fa-info-circle"></i></h3>
  <div class="trend information_column">
    <div class="informations_profile_pic">
      <img class="information_image" width="50px" src="<?php echo $user_array['profile_pic']; ?>" alt="">
      <?php echo $dotOnline; ?>
    </div>
    <p class='information_name'><?php echo $user_array['first_name']." ".$user_array['last_name']; ?></p>
    <i id='briefcase' class="fas fa-briefcase"></i><span><?php echo $user_array['occupation']; ?></span><br>
    <i id='briefcase' class="fas fa-building"></i><span><?php echo $user_array['country']; ?></span><br>
    <i id='briefcase' class="fas fa-birthday-cake"></i><span><?php echo $birthday; ?></span><br>
    <i id='briefcase' class="fas fa-clock"></i><span>Since <?php echo $signup; ?></span><br>
    <i id='briefcase' class="fas fa-users"></i><span><?php
    if($num_friends <= 1) {
      echo $num_friends . " Friend";
    } else {
      echo $num_friends . " Friends";
    }
    ?></span>
  </div>
</div>
<!-- ---------------------------------- RIGHT COLUMN ----------------------------------- -->
<div class="column popular_trend right_column">
  <h3>Friends Online <i class="fas fa-user-circle"></i></h3>
  <div class="trend trend_right">
    <?php
    // Select also all profile_pic , username and names of users online
    $user_online_query = $connexion->prepare('SELECT user_name, profile_pic, first_name, last_name FROM users WHERE login = ? AND NOT user_name = ?');
    $user_online_query->execute(array("yes", $userLoggedIn));

      while($row = $user_online_query->fetch()) {
        $userObj = New User($connexion, $userLoggedIn);
        $areWe =  $userObj->isFriend($row['user_name']);
        if($areWe) {
        $picture = $row['profile_pic'];
        $name = $row['first_name'] . " " . $row['last_name'];
        $full_name = strlen($name) >= 14 ? "..." : "";
        $trimmed_word = str_split($name, 14);
        $trimmed_word = $trimmed_word[0];
        echo "<a href='".$row['user_name']."'><img class='profile_pic_online' src='" . $picture . "'>" . $trimmed_word . $full_name . "</a><br>";
      } else {
        echo "";
      }
      }
    ?>
  </div>
</div>

<!-- ---------------------------------- MENU NAV PROFILE ----------------------------------- -->
<div class="all_center" id="center_menu_mobile">
  <div class="main_column nav_menu">
    <ul class="nav nav-tabs" style="display:inline-flex;" role="tablist" id="profileTabs">
      <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $newsAcctive; ?>" href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab" style="<?php echo $line; ?>">News Feed</a>
      </li>
      <li class="nav-item" role="presentation">
        <a id='bio_link' class="bio_class nav-link <?php echo $bioActive; ?>" href="#bio_div" aria-controls="bio_div" role="tab" data-toggle="tab" style="<?php echo $line; ?>">Bio</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $tab; ?>" href="#message_div" aria-controls="message_div" role="tab" data-toggle="tab" style="<?php echo $linetrought; ?>">Messages</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $photoAcctive; ?>" href="#photo_div" aria-controls="photo_div" role="tab" data-toggle="tab" style="<?php echo $line; ?>">photo</a>
      </li>
    </ul>
    <form id="button_add_friend" class="button_add_friend" action="<?php echo $username; ?>" method="post">
      <?php
      $profile_user_obj = new User($connexion, $username);
      if($profile_user_obj->isClosed()) {
        header('Location: user_closed.php');
      }
      $logged_in_user_obj = new User($connexion, $userLoggedIn);
      if($userLoggedIn != $username) {
        if ($logged_in_user_obj->isFriend($username)) {
          echo "<button type='submit' name='remove_friend' class='danger friend_button'><i style='color:#da2929;' class='fas fa-user-times'></i></button>";
        } else if($logged_in_user_obj->didReceiveRequest($username)) {
          echo "<button type='submit' name='respond_request' class='warning friend_button'><i style='color:#ff8100;' class='fas fa-user-check'></i></button><br>";
        } else if($logged_in_user_obj->didSendRequest($username)) {
          echo "<button type='submit' name='' class='default friend_button'><i style='color:#4478ef;' class='fas fa-user-clock'></i></button><br>";
        } else {
          echo "<button type='submit' name='add_friend' class='success friend_button'><i style='color:#00bf06;' class='fas fa-user-plus'></i><br>";
        }
      }
      ?>
    </form>
  </div>
</div>



<!-- ---------------------------------- INFORMATION COLUMN ON MOBILE PHONE ----------------------------------- -->
<div class="all_center" id='all_center_mobile'>
  <div class="main_column" id='main_column_mobile'>
    <form id='info_column_mobile' class="post_form" action="" method="">
      <div class="informations_profile_pic">
        <img class="information_image" width="50px" src="<?php echo $user_array['profile_pic']; ?>" alt="">
        <?php echo $dotOnline; ?>
      </div>
      <p class='information_name'><?php echo $user_array['first_name']." ".$user_array['last_name']; ?></p>
      <i id='briefcase' class="fas fa-building"></i><span><?php echo $user_array['city'].", ".$user_array['country']; ?></span><br>
      <i id='briefcase' class="fas fa-birthday-cake"></i><span><?php echo $birthday; ?></span><br>
      <i id='briefcase' class="fas fa-users"></i><span><?php
      if($num_friends <= 1) {
        echo $num_friends . " Friend";
      }else {
        echo $num_friends . " Friends";
      }
      ?></span>
    </form>
  </div>
</div>

<div class="tab-content">
<!-- ---------------------------------- DIV NEWS FEED ----------------------------------- -->
  <div class="tab-pane active" role="tabpanel" id="newsfeed_div">
    <!-- ---------------------------------- POST AREA COLUMN ----------------------------------- -->
    <div class="all_center" style="display:<?php echo $sty; ?>;">
      <div class="main_column main_post_form">
        <form class="post_form" action="" method="post" enctype="multipart/form-data">
          <div class="label_div">
            <label for="pictureToUpload"><i class="far fa-images" id="icon_post2"></i></label><br>
            <label for="videoToUpload"><i class="fas fa-film" id="icon_post"></i></label>
          </div>
          <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
          <button type="submit" name="post" id="post_button" value="post">Post</button><br>
          <input type="file" name="pictureToUpload" class="upload-image-file" id="pictureToUpload" style="display:none;">
          <input type="file" class="upload-video-file" name="videoToUpload" id="videoToUpload" style="display:none;">
          <div class="mini_photo" id="mini_photo"></div>
          <div style="display: none;" class='video-prev' class="pull-right">
            <video class="video-preview" controls="controls"/>
          </div>
          <div style="display: none;" class='image-prev' class="pull-right">
            <img class="image-preview" >
          </div>
        </form>
      </div>
    </div>
    <!-- ---------------------------------- LOAD POST ZONE ----------------------------------- -->
    <div class="posts_area" style="display:<?php echo $sty; ?>;">
    </div>
    <div class="loading main-column">
      <img id="loading" src="assets/icons/loading.gif" alt="loading-img">
    </div>
  </div>

<!-- ---------------------------------- DIV BIO ----------------------------------- -->
  <div class="tab-pane fade" id="bio_div" role="tabpanel">
    <div class="all_center" id="center_menu_mobile1">
      <div class="main_column main_column_request" id="message_center_profile_bio">
        <div class="main-column column" id="main-column">
          <div class="profile_pic_profile_section">
            <img width='200px' src="<?php echo $user_array['profile_pic']; ?>">
            <h4><?php echo $user_array['first_name']." ".$user_array['last_name']; ?></h4>
            <hr>
          </div>
          <div class="top_part_bio_profile">
            <i id="bio_icon1" class=" icon_bio fas fa-briefcase"></i><span><?php echo $user_array['occupation']; ?></span><br />
            <i id="bio_icon2" class="icon_bio fas fa-house-user"></i><span>Live in <?php echo $user_array['city']; ?>, <?php echo $user_array['country']; ?></span><br />
            <i id="bio_icon3" class="icon_bio fas fa-birthday-cake"></i><span><?php
            $original_date = $user_array['date_of_birth'];
            $ori = new DateTime($original_date);
            $new_date = date("d F Y", strtotime($original_date));
            $today_date = date("Y-m-d");
            $tod = new DateTime($today_date);
            $date_diff = $tod->diff($ori);
            echo $new_date . " (" . $date_diff->format('%Y') . "yo)" ;
            ?></span><br />
            <i id="bio_icon4" class="icon_bio fas fa-clock"></i><span><?php
             $original_sign = $user['signup_date'];
             $new_date_signup = date("F Y", strtotime($original_sign));
             echo "Join TSN on " . $new_date_signup;
            ?></span> <br />
            <i id="bio_icon5" class="icon_bio fas fa-users"></i><span><?php
            if($mutual_friend <= 1) {
              echo $mutual_friend . " Friend in common";
            } else {
              echo $mutual_friend . " Friends in common";
            }
            ?></span> <br />
            <hr>
          </div>
          <div class="middle_part_bio_profile">
            <i id="bio_icon6" class="icon_bio fas fa-map-marker-alt"></i><span>Born in <?php echo $user_array['born_city'].", ".$user_array['born_country']; ?></span><br />
            <i id="bio_icon7" class="icon_bio fas fa-heart"></i><span><?php echo $user_array['relationship']; ?></span> <br />
            <i id="bio_icon8" class="icon_bio fas fa-user-friends"></i><span><?php
            if($num_friends <= 1) {
              echo $num_friends . " Friend";
            } else {
              echo $num_friends . " Friends";
            }

            ?></span> <br />
            <i id="bio_icon9" class="icon_bio fas fa-thumbs-up"></i><span><?php
            if($user['num_likes'] <= 1) {
              echo $user_array['num_likes'] . " Like";
            } else {
              echo $user_array['num_likes'] . " Likes";
            }

            ?></span><br />
            <i id="bio_icon10" class="icon_bio fas fa-pen-square"></i><span><?php
            if($user['num_posts'] <= 1) {
              echo $user_array['num_posts'] . " Post";
            } else {
              echo $user_array['num_posts'] . " Posts";
            }

            ?></span> <br />
            <hr>
          </div>
          <div class="last_part_bio_profile">
            <i id="bio_icon11" class="icon_bio fas fa-book"></i><span>Biography</span>
            <p><?php echo $user_array['bio']; ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- ---------------------------------- DIV MESSAGE ----------------------------------- -->
  <div class="tab-pane fade" id="message_div" role="tabpanel">

    <div class="all_center" id="center_menu_mobile1">
      <div class="main_column main_column_request" id="message_center_profile">
        <div class="main-column column" id="main-column">
          <?php
          	echo "<h4 class='message_title'>&nbsp;You and <a href='$username'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr style='margin:0;'>";
            echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($username);
            echo "</div>";
            ?>
            <div class="message_post">
              <form id="form_private_message" action="" method="POST" enctype="multipart/form-data" name="imgForm">
    								<div style="display: none;" class='video-prev2' class="pull-right">
    			            <video style="max-height:120px;" class="video-preview2" controls="controls"/>
    			          </div>
    			          <div style="display: none;" class='image-prev2' class="pull-right">
    			            <img id='img_preview' class="image-preview2" >
    			          </div>
    								<div class="label_div">
    			            <label for="fileToUpload"><i class="far fa-images" id="icon_post2"></i></label><br>
    			            <label for="videoToUpload2"><i class="fas fa-film" id="icon_post"></i></label>
    			          </div>
    		            <textarea class='message_body' name='message_body' id='message_textarea' placeholder='Write your message'></textarea>
    		            <button type='submit' style="display:none;" onclick="sendMessage()" name='post_message' class='info' id='message_submit'>SEND</button>
    		            <input style='display:none;' type='file' class='upload-image-file2'  name='fileToUpload' id='fileToUpload'>
    								<input type='file' class='upload-video-file2' name='videoToUpload' id='videoToUpload2' style='display:none;'>
              </form>
              <script>
               $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                   div = document.getElementById("scroll_messages");

                  if(div != null) {
                    div.scrollTop = div.scrollHeight;
                  }
                });
              </script>
            </div>
        </div>
      </div>
    </div>

  </div>

<!-- ---------------------------------- DIV PHOTO ----------------------------------- -->
  <div class="tab-pane fade" id="photo_div" role="tabpanel">
    <div class="container_photo">
      <div class="all_center" id="center_menu_mobile1">
        <div class="main_column main_column_request" id="photo_div_profile">
          <div class="main-column column" id="main-column">
            <div class="container_gallery">
              <?php
              $gallery = $connexion->prepare('SELECT images FROM posts WHERE added_by = ? AND deleted = ? ORDER BY id DESC ');
              $gallery->execute(array($username, "no"));
              while($photo = $gallery->fetch()) {
                if(isset($photo['images']) && $photo['images'] != "") {
                  echo "<img onclick='openModal(this)' class='mesphoto' width='250px' src='".$photo['images']."'>";
                }else {
                  echo "";
                }
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-------------------------------- Modal Box Picture of each posts  ---------------------------- -->
    <div id="myModal" class="modal">
  <span class="close_modal">X</span>
  <img class="modal-content" id="img01">
  <div id="caption"></div>
</div>


<!-- Header Java Script -->
<script src="assets/js/header.js"></script>
<!-- Like Post Java Script -->
<script src="assets/js/like_post.js"></script>
<!-- Modal Java Script -->
<script src="assets/js/modal.js"></script>
<!-- Load the preview photo for post Java Script -->
<script src="assets/js/load_photo_post.js"></script>
<!-- Load the preview photo message for post Java Script -->
<script src="assets/js/load_photo_post2.js"></script>
<!-- Load the preview video for post Java Script -->
<script src="assets/js/load_video_post.js"></script>
<!-- Load the preview video message for post Java Script -->
<script src="assets/js/load_video_post2.js"></script>
<!-- Text area and post zone Java Script -->
<script type="text/javascript">
//Function sticky column left and right
window.onscroll = function() {myFunction()};
  var header1 = document.querySelector(".left_column");
  var header2 = document.querySelector(".right_column");
  var center = document.querySelector(".center_column");
  var sticky = header1.offsetTop + 75;
  function myFunction() {
    if (window.pageYOffset > sticky) {
      header1.classList.add("sticky");
      header2.classList.add("sticky");
    } else {
      header1.classList.remove("sticky");
      header2.classList.remove("sticky");
    }
}
// Function to auto resize the text area
textarea = document.querySelector("#post_text");
textarea.addEventListener('input', autoResize, false);
function autoResize() {
  this.style.height = 'auto';
  this.style.height = this.scrollHeight + 'px';
}

$(function() {
    // for bootstrap 3 use 'shown.bs.tab', for bootstrap 2 use 'shown' in the next line
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // save the latest tab; use cookies if you like 'em better:
        localStorage.setItem('lastTab', $(this).attr('href'));
    });

    // go to the latest tab, if it exists:
    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        $('[href="' + lastTab + '"]').tab('show');
    }
});

</script>

<script>
var userLoggedIn = "<?php echo $userLoggedIn; ?>";
var userTo = "<?php echo $username; ?>";

var div = document.getElementById("scroll_messages");
div.scrollTop = div.scrollHeight;

const sendMessage = () => {
	var body = $("#message_textarea").val();
	var form = document.forms.namedItem("imgForm");
	var formData = new FormData(form);
	var otherData = [];
	otherData.push({"me":userLoggedIn, "body":body, "friend":userTo});
	formData.append('otherData', JSON.stringify(otherData));
	$.ajax({
				type: "POST",
				url: "includes/handlers/send_message.php",
				data: formData,
				contentType: false,
				processData: false,
				success:(function(data) {
					$("textarea").val("");
					$(".checkSeen").remove();
			})
	});
  var monimage = document.getElementById("fileToUpload");
  var mavideo = document.getElementById("videoToUpload2");
  if(monimage.value != "") {
    var dialog = bootbox.dialog({
    message: '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i>We are uploading your picture... <<<<<<</p>',
    closeButton: false
});
setTimeout(function () {
window.location.reload(1);
}, 2000);
dialog.modal('hide');
  }else if (mavideo.value != "") {
    var dialog = bootbox.dialog({
    message: '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i>We are uploading your video... >>> If your video is too long it may not be uploaded!!! <<<<<<</p>',
    closeButton: false
});
setTimeout(function () {
window.location.reload(1);
}, 8000);
dialog.modal('hide');

  }
	const scrollDown = () => {
		div.scrollTop = div.scrollHeight;
	}
	setTimeout(scrollDown, 800);
}
const getMessages = () => {
  $.post("includes/handlers/get_messages.php", {me:userLoggedIn, friend:userTo}, function(result){
    $(".loaded_messages").append(result);
    var all_elements = $(".loaded_messages").children();
    all_elements.each(function(){
      var el_id = this.id;
      // data("verified") prevents the removal triggered by its duplicate, if any.
      $(this).data("verified",true);
      all_elements.each(function(){
        if(el_id==this.id && !$(this).data("verified")){
          $(this).remove();
        }
      });
    });
    // Turn all "surviving" element's data("verified") to false for future "clean".
    $(".loaded_messages").children().each(function(){
      $(this).data("verified",false);
    });
  });
  var test = document.getElementById("scroll_messages");
  //if I scroll more than 1000px...
  if($("#scroll_messages").scrollTop() < test.scrollHeight - 1150){
  }else {
    div.scrollTop = div.scrollHeight;
  }
}
setInterval(getMessages, 500);

const checkSeen = () => {
  $.post("includes/handlers/check_seen.php", {me:userLoggedIn, friend:userTo}, function(data){
    $(".checkSeen").html(data);
  });
}
setInterval(checkSeen, 4000);
  $(function(){
    $(document).keypress(function(e){
      if(e.keyCode === 13 && e.shiftKey === false && $("#message_textarea").is(":focus")) {
        e.preventDefault();
        $("#message_submit").click();
        setTimeout(scrollDown, 800);
      }
    });
      $("#message_div").submit(function(e) {
        e.preventDefault();
      });

  });
  $(function(){
    var userLoggedIn = '<?php echo $userLoggedIn; ?>';
    var profileUsername = '<?php echo $username; ?>';
    var inProgress = false;
    loadPosts(); //Load first posts
    $(window).scroll(function() {
      var bottomElement = $(".all_center").last();
      var noMorePosts = $('.posts_area').find('.noMorePosts').val();
      // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
      if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
        loadPosts();
      }
    });
    function loadPosts() {
      if(inProgress) { //If it is already in the process of loading some posts, just return
        return;
      }
      inProgress = true;
      $('#loading').show();
      var page = $('.posts_area').find('.nextPage').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'
      $.ajax({
        url: "includes/handlers/ajax_load_profile_posts.php",
        type: "POST",
        data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
        cache:false,
        success: function(response) {
          $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
          $('.posts_area').find('.noMorePosts').remove();
          $('.posts_area').find('.noMorePostsText').remove();
          $('#loading').hide();
          $(".posts_area").append(response);
          inProgress = false;
        }
      });
    }
    //Check if the element is in view
    function isElementInView (el) {
      if(el == null) {
        return;
      }
      var rect = el.getBoundingClientRect();
      return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
        rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
      );
    }
  });

  $(document).ready(function(){
    setTimeout(
      function(){
        var bio = document.querySelector(".bio_class");
        var profile = document.getElementById("profile_left");
        var trends = document.getElementById("trend_profile");
        var x = window.matchMedia("(max-width: 1100px)")
        var center_mobile = document.getElementById("all_center_mobile");
        if (x.matches && !bio.classList.contains("active")) {
          trends.style.display = "none";
          profile.style.display = "none";
          center_mobile.style.display = "block";
        }else if (x.matches && bio.classList.contains("active")) {
          trends.style.display = "none";
          profile.style.display = "none";
          center_mobile.style.display = "none";
        }else if(bio.classList.contains("active")) {
          trends.style.display = "flex";
          profile.style.display = "none";
        }else {
          trends.style.display = "none";
          profile.style.display = "flex";
        }
      $(document).click(function(){
        var bio = document.querySelector(".bio_class");
        var profile = document.getElementById("profile_left");
        var trends = document.getElementById("trend_profile");
        var x = window.matchMedia("(max-width: 1100px)")
        if (x.matches && !bio.classList.contains("active")) {
          trends.style.display = "none";
          profile.style.display = "none";
          center_mobile.style.display = "block";
        }else if (x.matches && bio.classList.contains("active")) {
          trends.style.display = "none";
          profile.style.display = "none";
          center_mobile.style.display = "none";
        }else if(bio.classList.contains("active")) {
          trends.style.display = "flex";
          profile.style.display = "none";
        }else {
          trends.style.display = "none";
          profile.style.display = "flex";
        }
      })
    }, 500);
  });
 </script>
