<?php
include("includes/header.php");
$message_obj = new Message($connexion, $userLoggedIn);
if(isset($_GET['u'])) {
	$check_user_exist = $connexion->prepare('SELECT user_name FROM users WHERE user_name = ?');
	$check_user_exist->execute(array($_GET['u']));
	$numRow = $check_user_exist->rowCount();
	$check_result = $check_user_exist->fetch();
	if($numRow == 0) {
		$user_to = 'new';
	}else {
		if($check_result['user_name'] == $userLoggedIn) {
			$user_to = 'new';
		}else {
			$user_to = $check_result['user_name'];
		}
	}
}else {
	$user_to = $message_obj->getMostRecentUser();
	if($user_to == false)
		$user_to = 'new';
}
?>
<!-- ---------------------------------- LEFT COLUMN ----------------------------------- -->
<div class="column popular_trend left_column">
  <h3>Conversations <i style="color:orange;" class="fas fa-comments"></i></h3>
  <div class="trend">
  <?php
  	if($user_to != "new") {
    	$user_to_obj = new User($connexion, $user_to);
      $friend_name = $user_to_obj->getFirstAndLastName();
      $friend_username = $user_to_obj->getUsername();
      $query_friend = $connexion->prepare('SELECT profile_pic FROM users WHERE user_name = ?');
      $query_friend->execute(array($friend_username));
      $row = $query_friend->fetch();
      ?>
      <div class="user-details column mobile-convo" id="conversations2">
				<div class="new_message_title_side">
					<a class="link-new" href="messages.php?u=new">New Message</a>
				</div>
        <div class="loaded_conversations">
          <?php echo $message_obj->getConvos(); ?>
        </div>
        <br>
      </div>
      <?php
		}else {
			?>
			<div class="user-details column mobile-convo" id="conversations2">
        <div class="loaded_conversations">
          <?php echo $message_obj->getConvos(); ?>
        </div>
        <br>
      </div>
			<?php
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

<!-- ---------------------------------- CENTER COLUMN NEW MESSAGE----------------------------------- -->
<div class="all_center" id="center_menu_mobile">
  <div class="main_column main_column_request">
    <div class="main-column column" id="main-column">
      <?php
      if($user_to != "new"){
      	echo "<h4 class='message_title'>&nbsp;You and <a href='$user_to'>" . $friend_name . "</a></h4><hr style='margin:0;'>";
        echo "<div class='loaded_messages' id='scroll_messages'>";
        echo $message_obj->getMessages($user_to);
        echo "</div>";
      }else
        echo "<h4>New Message</h4>";
        ?>
        <div class="message_post">
          <form id="form_private_message" action="" method="POST" enctype="multipart/form-data" name="imgForm">
		        <?php
		          if($user_to == "new") {
		            echo '<div class="new-mess">';
		            echo "Select the friend you would like to message <br><br>";
		        ?>
		            To: <input type='text' onkeyup='getUsers(this.value, "<?php echo $userLoggedIn;?>")' name='q' placeholder='Name' autocomplete='off' id='search_text_input'>
		        <?php
		            echo "<div class='results'></div>";
		            echo '</div>';
		          }else {
								?>
								<div style="display: none;" class='video-prev' class="pull-right">
			            <video class="video-preview" controls="controls"/>
			          </div>
			          <div style="display: none;" class='image-prev' class="pull-right">
			            <img id='img_preview' class="image-preview" >
			          </div>
								<div class="label_div">
			            <label for="fileToUpload"><i class="far fa-images" id="icon_post2"></i></label><br>
			            <label for="videoToUpload"><i class="fas fa-film" id="icon_post"></i></label>
			          </div>
								<?php
		            echo "<textarea class='message_body' name='message_body' id='message_textarea' placeholder='Write your message'></textarea>";
		        ?>
		            <button type='submit' style="display:none;" onclick="sendMessage()" name='post_message' class='info' id='message_submit'>SEND</button>
		        <?php
		            echo "<input style='display:none;' type='file' class='upload-image-file'  name='fileToUpload' id='fileToUpload'>";
								echo "<input type='file' class='upload-video-file' name='videoToUpload' id='videoToUpload' style='display:none;'>";
		        	}
		        ?>
          </form>
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


<script>
	var div = document.getElementById("scroll_messages");
	div.scrollTop = div.scrollHeight;
</script>


<script>
  const sendMessage = () => {
    var body = $("textarea").val();
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

  	const scrollDown = () => {
    	div.scrollTop = div.scrollHeight;
  	}
  	setTimeout(scrollDown, 800);
    var file = document.getElementById("fileToUpload");
    file.value = file.defaultValue;
		var video = document.getElementById("videoToUpload");
    video.value = video.defaultValue;
  }

  var div = document.getElementById("scroll_messages");
	if(div != null) {
    div.scrollTop = div.scrollHeight;
  }
  var userLoggedIn = "<?php echo $userLoggedIn; ?>";
	var userTo = "<?php echo $user_to; ?>";

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
    if($("#scroll_messages").scrollTop() < test.scrollHeight - 700){

    }else {
      div.scrollTop = div.scrollHeight;
    }
  }
	setInterval(getMessages, 500);

  const getConvos = () => {
  	$.post("includes/handlers/get_convos.php", {me:userLoggedIn}, function(data){
      $(".loaded_conversations").html(data);
    });
  }
  setInterval(getConvos, 2000);

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
        const scrollDown = () => {
        	div.scrollTop = div.scrollHeight;
        }
				setTimeout(scrollDown, 800);
      }
    });
  });
</script>
<!-- Modal Java Script -->
<script src="assets/js/modal.js"></script>
<!-- Load the preview photo for post Java Script -->
<script src="assets/js/load_photo_post.js"></script>
<!-- Load the preview video for post Java Script -->
<script src="assets/js/load_video_post.js"></script>
