
var mobile = document.querySelector("#icon_mobile");
var mainIcon1 = document.querySelector(".main_icon1");
var mainIcon2 = document.querySelector(".main_icon2");
var mainIcon3 = document.querySelector(".main_icon3");
var mainIcon4 = document.querySelector(".main_icon4");
var mainIcon5 = document.querySelector(".main_icon5");
var mainIcon6 = document.querySelector(".main_icon6");
var mainIcon7 = document.querySelector(".main_icon7");
var mainIcon8 = document.querySelector(".main_icon8");
var smallLogo = document.querySelector(".logo_small_resolution");
var searchInput = document.querySelector("#search");
mobile.addEventListener("click", mobileMenu);
mainIcon8.addEventListener("click", mobileSearch);

function mobileMenu(e) {
  if(smallLogo.style.visibility == "visible" || searchInput.style.display == "inline-block") {
    mainIcon1.style.display = "inline-block";
    mainIcon2.style.display = "inline-block";
    mainIcon3.style.display = "inline-block";
    mainIcon4.style.display = "inline-block";
    mainIcon5.style.display = "inline-block";
    mainIcon6.style.display = "inline-block";
    mainIcon7.style.display = "inline-block";
    mainIcon8.style.display = "inline-block";
    searchInput.style.display = "none"
    smallLogo.style.visibility = "hidden";
  }else  {
    mainIcon1.style.display = "none";
    mainIcon2.style.display = "none";
    mainIcon3.style.display = "none";
    mainIcon4.style.display = "none";
    mainIcon5.style.display = "none";
    mainIcon6.style.display = "none";
    mainIcon7.style.display = "none";
    mainIcon8.style.display = "none";
    smallLogo.style.visibility = "visible";
  }
}

function mobileSearch(e) {

  if(mainIcon8.style.display == "inline-block") {
    searchInput.style.display = "inline-block";
    mainIcon1.style.display = "none";
    mainIcon2.style.display = "none";
    mainIcon3.style.display = "none";
    mainIcon4.style.display = "none";
    mainIcon5.style.display = "none";
    mainIcon6.style.display = "none";
    mainIcon7.style.display = "none";
    mainIcon8.style.display = "none";
  }
}
