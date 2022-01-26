<?php
require "config/config.php";
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");
include("includes/classes/Notification.php");

if(isset($_SESSION['username'])) {
  // Select all data of the user logged in
  $userLoggedIn = $_SESSION['username'];
  $user_details_query = $connexion->prepare('SELECT * FROM users WHERE user_name = ?');
  $user_details_query->execute(array($userLoggedIn));
  $user = $user_details_query->fetch();
  $user_profile_pic = $user['profile_pic'];

}else {
  header("Location: register.php");
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to TSN</title>
    <!-- CSS file -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index_style_popular_wrapper.css">
    <link rel="stylesheet" href="assets/css/index_style_post_area.css">
    <link rel="stylesheet" href="assets/css/index_style_each_post.css">
    <link rel="stylesheet" href="assets/css/details_style.css">
    <link rel="stylesheet" href="assets/css/comment_frame_style.css">
    <link rel="stylesheet" href="assets/css/style_side_column_profile.css">
    <link rel="stylesheet" href="assets/css/profile_news_feed.css">
    <link rel="stylesheet" href="assets/css/style_request_page.css">
    <link rel="stylesheet" href="assets/css/style_message_page.css">
    <link rel="stylesheet" href="assets/css/live_search_result.css">
    <link rel="stylesheet" href="assets/css/profile_message.css">
    <link rel="stylesheet" href="assets/css/dropdown_window.css">
    <link rel="stylesheet" href="assets/css/unread_message_icons.css">
    <link rel="stylesheet" href="assets/css/live_search.css">
    <link rel="stylesheet" href="assets/css/search_friend_page.css">
    <link rel="stylesheet" href="assets/css/settings.css">
    <link rel="stylesheet" href="assets/css/photo_profile.css">
    <link rel="stylesheet" href="assets/css/bio_profile.css">
    <!-- Emoji Picker -->
    <link rel="stylesheet" href="assets/css/emojione.picker.css">
    <!-- Jquery Script -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootsrap Script -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- Bootsrap Script -->
    <script src="assets/js/bootbox.min.js"></script>
    <!-- Emoji Script -->
    <script src="assets/js/emojione.picker.min.js"></script>
    <!-- Bootsrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css.map">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- FONTS -->
    <link rel="stylesheet" href="https://use.typekit.net/wez4oyk.css">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/0fd6fa10db.js" crossorigin="anonymous"></script>
    <!-- Main Java Script File -->
    <script src="assets/js/main_js.js"></script>



  </head>
  <body>
    <div class="top_bar">
      <div class="logo">
        <a class="logo_main_resolution" href="index.php">THE SOCIAL NETWORK</a>
        <a class="logo_small_resolution" href="index.php" id="tsn">TSN</a>
      </div>
      <div class="search_div" id="search">
        <form class="search_form" action="search.php" method="GET" name="search_form">
          <input id="search_text_input" type="text" name="q" placeholder="Search..." autocomplete="off" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')">
          <input type="submit" id="submit_search_form" style="display:none;" name="" value="">
        </form>
      </div>
      <div class="main_search_result">
        <div class="search_results">
        </div>
        <div class="search_results_footer_empty">
        </div>
      </div>


      <script>
        document.getElementById('submit_search_form').addEventListener('keypress', function(event) {
          if (event.keyCode == 13) {
            event.preventDefault();
          }
        });
      </script>


      <nav class="top_menu">
        <?php
          //Unread messages
          $messages = new Message($connexion, $userLoggedIn);
          $num_messages = $messages->getUnreadNumber();
          //Unread notifications
          $notifications = new Notification($connexion, $userLoggedIn);
          $num_notifications = $notifications->getUnreadNumber();
          //Unread friend request
          $user_obj = new User($connexion, $userLoggedIn);
          $num_request = $user_obj->getNumberOfFriendRequests();

        ?>
        <i class="fas fa-search main_icon8" id="main_icon8"></i>
        <a href="index.php"><i class="fas fa-home main_icon1" id="main_icon"></i></a>
        <a href="<?php echo $userLoggedIn; ?>"><img class="user_pic_menu main_icon2" id="main_icon" src="<?php echo $user_profile_pic; ?>"></a>
        <a  href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')"><i class="fas fa-envelope main_icon3" id="main_icon"></i>
          <?php
          if($num_messages > 0)
           echo "<span class='notification_badge' id='unread_message'>".$num_messages."</span>";
          ?>
        </a>
        <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')"><i class="fas fa-bell main_icon4" id="main_icon"></i>
          <?php
          if($num_notifications > 0)
           echo "<span class='notification_badge' id='unread_notification'>".$num_notifications."</span>";
          ?>
        </a>
        <a href="requests.php"><i class="fas fa-user-friends main_icon5" id="main_icon"></i>
          <?php
          if($num_request > 0)
           echo "<span class='notification_badge' id='unread_requests' id='menu4'>".$num_request."</span>";
          ?>
        </a>
        <a href="settings.php"><i class="fas fa-user-cog main_icon6" id="main_icon"></i></a>
        <a href="includes/handlers/logout.php"><i class="fas fa-door-open main_icon7" id="main_icon"></i></a>
        <a class="menu_icon_link" href="#" id="icon_mobile"><i class="fas fa-bars" ></i></a>
      </nav>
    </div>
    <!---------------------------------------------- DROP DOWN WINDOWS ---------------------------------- -->
    <div class="dropdown_data_window " style='height: 0px;'>
      <div class="loaded_conversations">
        <input type="hidden" id="dropdown_data_type" value="">
      </div>
    </div>

    <div class="wrapper">
