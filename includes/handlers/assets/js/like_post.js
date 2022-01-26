// function to get number of like per post
function ilike(id, user) {
  var postID = id;
  var userloggin = user;
  var slectID = "number_like_"+postID;
  var textlike = document.getElementById(slectID);
  textlike.style.display = "inline-block";
  var request = new XMLHttpRequest();
  request.onreadystatechange = function () {
    if (request.readyState === 4) {
      var num_likes = request.responseText;
      var get_like = JSON.parse(num_likes);
      var number_like = get_like[0];
      var to_like = get_like[1];
      textlike.innerHTML = number_like;
      if(to_like == "yes") {
        textlike.innerHTML = "Like" + "\xa0\xa0" + number_like;
      } else {
        textlike.innerHTML = "Unlike" + "\xa0\xa0" + number_like;;
      }
    }
  }
  request.open('POST', 'includes/handlers/get_likes_post.php', true)
  request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  data = "postID="+postID+"&user="+userloggin;
  request.send(data);
}

// Function like post
    function likePost(user, id){
      var user_liked = user;
      var post_liked = id;
      var xml = new XMLHttpRequest();
      xml.onreadystatechange = function () {
        if (xml.readyState === 4) {
          var liked = xml.responseText;
          var data = JSON.parse(liked);
        }
      }
      xml.open('POST', 'includes/handlers/like_post.php', true)
      xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      like_data = "user_liked="+user_liked+"&post_liked="+post_liked;
      xml.send(like_data);
    }
