function ativar_menu() {
  var navItems = document.getElementById("barra").getElementsByTagName("li");
  for (var i=0; i< navItems.length; i++) {
    if((navItems[i].className.match("menuvertical")) || (navItems[i].className.match("submenu"))) {
      if(navItems[i].getElementsByTagName('ul')[0] != null) {
        navItems[i].onmouseover=function() {
          this.getElementsByTagName('ul')[0].style.display="block";
          this.style.backgroundColor = "#f5f5f5";
        }
        
        navItems[i].onmouseout=function() {
          this.getElementsByTagName('ul')[0].style.display="none";
          this.style.backgroundColor = "#fff";
        }
      }
    }
  }
}

window.onload = ativar_menu;