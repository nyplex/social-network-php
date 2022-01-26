$(document).ready(function(){
  //On click signup hide the login form and show the register form
  $("#signup").click(function(){
    $(".first").slideUp("slow", function(){
      $(".second").slideDown("slow");
    });
  });
  //On click signin hide the register form and show the login form
  $("#signin").click(function(){
    $(".second").slideUp("slow", function(){
      $(".first").slideDown("slow");
    });
  });
});


// CHANGE DATE VALUE TO TODAY DATE For Date Input
$(function() {
  $("#reg_dob").datepicker({
    dateFormat: 'yy-mm-dd',
    changeMonth: true,
    changeYear: true,
    minDate: new Date(1901, 10 - 1, 25),
    maxDate: '0',
    yearRange: '1901:c',
    inline: true
  });
});
