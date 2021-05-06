function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}

//toggle sidebar on mobile
function showSidebar() {
  var x = document.getElementById("sidebar");
  //check for none and empty string
  if (!'none'.indexOf(x.style.display)) {
    x.style.display = "block";
  }else{
    x.style.display = "none";
  }
}

// get viewport height and multiple by 1% to get a vh unit, save it to --vh in css root
let vh = window.innerHeight * 0.01;
document.documentElement.style.setProperty('--vh', `${vh}px`);
// execute above again if window resized
window.addEventListener('resize', () => {
  let vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`);
});
