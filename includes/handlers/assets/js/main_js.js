// Function live search user on new message page
function getUsers(value, user) {
  $.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data){
    $(".results").html(data);
  });
}

// Drop down window on the header
function getDropdownData(user, type) {
  if($(".dropdown_data_window").css("height") == "0px") {
    var pageName;

    if(type == "notification") {
      pageName = "ajax_load_notifications.php";
      $("span").remove("#unread_notification");
    } else if (type == "message") {
      pageName = "ajax_load_messages.php";
      $("span").remove("#unread_message");
    }
    var ajaxreq = $.ajax({
      url: "includes/handlers/" + pageName,
      type: "POST",
      data: "page=1&userLoggedIn=" + user,
      cache: false,
      success: function(response) {
        $(".dropdown_data_window").html(response);
        $(".dropdown_data_window").css({"padding": "6px", "height" : "280px", "box-shadow": "0px 0px 10px 2px rgba(0, 0, 0, 0.75)"});
        $("#dropdown_data_type").val(type);
      }
    });
  } else {
      $(".dropdown_data_window").html("");
      $(".dropdown_data_window").css({"padding": "0px", "height" : "0px", "box-shadow": "none"});
  }
  $(window).click(function(){
    $(".dropdown_data_window").html("");
    $(".dropdown_data_window").css({"padding": "0px", "height" : "0px", "box-shadow": "none"});
  })
}

$(document).click(function(e){
  if(e.target.class != "search_results" && e.target.id != "search_text_input") {
    $(".search_results").html("");
    $(".search_results_footer").html("");
    $(".search_results_footer").toggleClass("search_results_footer_empty");
    $(".search_results_footer").toggleClass("search_results_footer");
  }
});

function getUsers(value, user) {
  $.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedin:user}, function(data){
    $(".results").html(data);
  });
}

// function live search user
function getLiveSearchUsers(value, user) {
  $.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn: user}, function(data){
    if($(".search_results_footer_empty")[0]) {
      $(".search_results_footer_empty").toggleClass("search_results_footer");
      $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
    }
    $(".search_results").html(data);
    $(".search_results_footer").html("<a href='search.php?q=" + value + "'>See All Results</a>");

    if(data == "") {
      $(".search_results_footer").html("");
      $(".search_results_footer").toggleClass("search_results_footer_empty");
      $(".search_results_footer").toggleClass("search_results_footer");
    }
  });
}
