
// Class to handle ajax requests 
// get and post
//************************************************************

function AjaxRequests(){
    this.sendRequest = sendRequest;
}

function sendRequest(msg, callback, postData, phpFunction, flag){
    
    flag = typeof flag !== 'undefined' ? flag : true;
    
    switch(postData){
        case "POST":
            postRequest(msg, callback, phpFunction, flag);
            break;
         case "GET":
            getRequest(msg, callback, phpFunction);
            break;
    }
}

function postRequest(msg, callback, phpFunction, flag){
    var xhttp = new XMLHttpRequest();
    var path = "/../ScriptWrapper/" + phpFunction;
    xhttp.open("POST", path, flag);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.onreadystatechange = function(){
       if (xhttp.readyState == 4 && xhttp.status == 200) {
             callback(xhttp);
       }
   }
   xhttp.send(msg); 
}

function getRequest(msg, callback, phpFunction){
    var path = "../ScriptWrapper/" + phpFunction;
    var request = path + "?" + msg;
    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", request, true);
    xhttp.onreadystatechange = function() {
       if (xhttp.readyState == 4 && xhttp.status == 200) {
            callback(xhttp);
       }
    }
    xhttp.send();
}

function changeTheme(){
    var theme = localStorage.getItem("theme");
    var themePath = "../CSS/theme" + theme + ".css";
    var fileref=document.getElementById("themes");
    fileref.setAttribute("href", themePath);
}