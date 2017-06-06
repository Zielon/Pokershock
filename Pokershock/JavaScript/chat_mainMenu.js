//Functions which are response for handling chat and update page content
//
//***************************************************************

function updateTable(){
    var ajax = new AjaxRequests();
    ajax.sendRequest("", checkResponse, "POST", "tableBoxUpdater.php");
    
    function checkResponse(res){
        var response = res.responseText;
        var tab = response.split("_");
        var output = "";
        for (var i = 0; i < tab.length - 1; i++){
            output += tab[i];
        };
        $("#tablesWraper").html(output);    
    };
}

function updateOnline(){
    var ajax = new AjaxRequests();
    ajax.sendRequest("func=online", checkResponse, "POST", "tableBoxUpdater.php");
    
    function checkResponse(res){
        var response = res.responseText;
        var lang = localStorage.getItem("lang");
        var text;
        if(lang === "en"){
            text = "Online players: ";
        }
        if(lang === "pl"){
            text = "Gracze online: ";
        }
        document.getElementById("online").textContent = text + response;
    };
}

function updateChat(){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            var obj = JSON.parse(response);
            var output = "";
            for (var i = 0; i < obj.text.length; i++){
                output += obj.text[i];
            };
            $("#chatBox").html(output);    
            var objDiv = document.getElementById("chatBox");
            objDiv.scrollTop = objDiv.scrollHeight;  
            updateTable();
            updateOnline();
        }   
    }   
    var params = "function=" + "updateChat";
    xhttp.open("POST", "../ScriptWrapper/chatHandler.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(params); 
}

function sendChat(msg){ 
    var xhttp = new XMLHttpRequest();
    var params = "msg=" + msg + "&function=" + "send";
    xhttp.open("POST", "../ScriptWrapper/chatHandler.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(params);
}

//Update chat send msg and dispaly main menu
function start(){ 
    $.get('../ScriptWrapper/sessionCheck.php', function(data){
        if( data == "Expired" ) {
            document.getElementById("menu").innerText = "You need to by logged in";
            alert("Session expired ! Try again");
        }else if (data == "Active" ){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (xhttp.readyState == 4 && xhttp.status == 200) {
                    var response = xhttp.responseText;
                    var output = document.getElementById("wrap");   
                    output.innerHTML = response;                    //upload page content
             
                    if(localStorage.getItem("theme") === null){
                        localStorage.setItem("theme", "0");        //set local storage as default theme 0     
                    }
                    
                    if(localStorage.getItem("lang") === null){
                        localStorage.setItem("lang", "en");        //set local storage as default theme 0     
                    }
                    
                    var fileref=document.getElementById("themes");  //set saved theme    
                    var theme = localStorage.getItem("theme");
                    var themePath = "../CSS/theme" + theme + ".css";                      
                    fileref.setAttribute("href", themePath);
                    changeLang();
                    // content is generate dynamically that is why ready option is avalible only in response 
                    // for this particular contnet
                    
                    $(document).ready(function(){
                        $("#posttext").keydown(function(event){  
                            var key = event.which;  
                            if (key >= 33) {
                                var maxLength = $(this).attr("maxlength");  
                                var length = this.value.length;  
                                if (length >= maxLength) {  
                                    event.preventDefault();  
                                }  
                            }  
                        });
                        $('#posttext').keyup(function(e){  
                            if (e.keyCode == 13){ 
                                var text = $(this).val();
                                var maxLength = $(this).attr("maxlength");  
                                var length = text.length; 
                                if (length <= maxLength + 1) { 
                                   sendChat(text);			  
                                   $(this).val("");
                                }else{
                                   $(this).val(text.substring(0, maxLength));
                               }  
                            }
                        });
                    });
                }
            };

            xhttp.open("POST", "../ScriptWrapper/mainMenuHandler.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("lang=" + localStorage.getItem("lang"));
        }
    });
}

