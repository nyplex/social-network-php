<?php
include("includes/header.php");
$data = $_POST['image'];

list($type, $data) = explode(';', $data);
list(, $data)      = explode(',', $data);

$data = base64_decode($data);
$imageName = time().'.png';
file_put_contents('assets/images/profile_pics/'.$imageName, $data);

//return cropped image to page
$result_path ="assets/images/profile_pics/".$imageName;
$insert_pic_query = $connexion->prepare('UPDATE users SET profile_pic = ? WHERE user_name = ?');
$insert_pic_query->execute(array($result_path, $userLoggedIn));

?>
