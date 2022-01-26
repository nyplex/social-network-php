<?php
require "config/config.php";
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");

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
    <!-- Jquery Script -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootsrap Script -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- Bootsrap Script -->
    <script src="assets/js/bootbox.min.js"></script>
    <!-- Bootsrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css.map">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- FONTS -->
    <link rel="stylesheet" href="https://use.typekit.net/wez4oyk.css">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/0fd6fa10db.js" crossorigin="anonymous"></script>

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
        </form>
      </div>
      <nav class="top_menu">
        <i class="fas fa-search main_icon8" id="main_icon8"></i>
        <a href="index.php"><i class="fas fa-home main_icon1" id="main_icon"></i></a>
        <a href="<?php echo $userLoggedIn; ?>"><img class="user_pic_menu main_icon2" id="main_icon" src="<?php echo $user_profile_pic; ?>"></a>
        <a href="#"><i class="fas fa-envelope main_icon3" id="main_icon"></i></a>
        <a href="#"><i class="fas fa-bell main_icon4" id="main_icon"></i></a>
        <a href="#"><i class="fas fa-user-friends main_icon5" id="main_icon"></i></a>
        <a href="#"><i class="fas fa-user-cog main_icon6" id="main_icon"></i></a>
        <a href="includes/handlers/logout.php"><i class="fas fa-door-open main_icon7" id="main_icon"></i></a>
        <a class="menu_icon_link" href="#" id="icon_mobile"><i class="fas fa-bars" ></i></a>
      </nav>
    </div>
    <div class="wrapper">
