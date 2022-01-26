// Function like comment
    function likeComment(user, id){
      var user_liked = user;
      var comment_liked = id;
      var xml = new XMLHttpRequest();

      xml.open('POST', 'includes/handlers/like_comment.php', true)
      xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      like_data = "user_liked="+user_liked+"&comment_liked="+comment_liked;
      xml.send(like_data);
    }


    // function to get number of like per post
    function GetlikeComment(id, user) {
      var commentID = id;
      var userloggin = user;
      var slectID = "number_like_"+commentID;
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
            textlike.innerHTML = "Like" + "\xa0" + number_like;
          } else {
            textlike.innerHTML = "Unlike" + "\xa0" + number_like;;
          }
        }
      }
      request.open('POST', 'includes/handlers/get_likes_comment.php', true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      data = "commentID="+commentID+"&user="+userloggin;
      request.send(data);
    }
