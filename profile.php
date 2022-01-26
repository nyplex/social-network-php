<?php
include("includes/header.php");

/////////////////////////////// Get username details for profile page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if(isset($_GET['profile_username'])) {
  $username = $_GET['profile_username'];
  $user_details_query = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
  $user_details_query->execute(array($username));
  $user_array = $user_details_query->fetch();
  $num_friends = (substr_count($user_array['friend_array'], ",")) -1;
  $orgDate = $user_array['date_of_birth'];
  $birthday = date("jS F Y", strtotime($orgDate));
  $orgDate2 = $user_array['signup_date'];
  $signup = date("F Y", strtotime($orgDate2));
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
<div class="column popular_trend left_column">
  <h3>Informations <i class="fas fa-info-circle"></i></h3>
  <div class="trend information_column">
    <div class="informations_profile_pic">
      <img class="information_image" width="50px" src="<?php echo $user_array['profile_pic']; ?>" alt="">
      <?php echo $dotOnline; ?>
    </div>
    <p class='information_name'><?php echo $user_array['first_name']." ".$user_array['last_name']; ?></p>
    <i id='briefcase' class="fas fa-briefcase"></i><span><?php echo $user_array['occupation']; ?></span><br>
    <i id='briefcase' class="fas fa-building"></i><span><a href="https://www.google.fr/search?q='<?php echo $user_array['country']; ?>'"><?php echo $user_array['country']; ?></a></span><br>
    <i id='briefcase' class="fas fa-birthday-cake"></i><span><?php echo $birthday; ?></span><br>
    <i id='briefcase' class="fas fa-clock"></i><span>Since <?php echo $signup; ?></span><br>
    <i id='briefcase' class="fas fa-users"></i><span><?php echo $num_friends; ?> Friends</span>
    <?php
      $counter = 0;
      if($userLoggedIn) {
        $counter = 5;
        echo $counter;
      } else {
        $counter = 3;
      }
    ?>
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
        $picture = $row['profile_pic'];
        $name = $row['first_name'] . " " . $row['last_name'];
        $full_name = strlen($name) >= 14 ? "..." : "";
        $trimmed_word = str_split($name, 14);
        $trimmed_word = $trimmed_word[0];
        echo "<a href='".$row['user_name']."'><img class='profile_pic_online' src='" . $picture . "'>" . $trimmed_word . $full_name . "</a><br>";
      }
    ?>
  </div>
</div>

<!-- ---------------------------------- MENU NAV PROFILE ----------------------------------- -->
<div class="all_center" id="center_menu_mobile">
  <div class="main_column nav_menu">
    <ul class="nav nav-tabs" style="display:inline-flex;">
      <li class="nav-item">
        <a class="nav-link active" href="#">News Feed</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Bio</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Messages</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">photo</a>
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
      <i id='briefcase' class="fas fa-users"></i><span><?php echo $num_friends; ?> Friends</span>
    </form>
  </div>
</div>

<!-- ---------------------------------- POST AREA COLUMN ----------------------------------- -->
<div class="all_center">
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
<div class="posts_area">
</div>
<div class="loading main-column">
  <img id="loading" src="assets/icons/loading.gif" alt="loading-img">
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
<!-- Load the preview video for post Java Script -->
<script src="assets/js/load_video_post.js"></script>
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

</script>
<script>
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
 </script>
