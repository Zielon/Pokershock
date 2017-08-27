//General purpose functions
//
//***************************************************************


//Create waiting room and start to count down time
function createTable(){  
    var ajax = new AjaxRequests();   
    ajax.sendRequest("", checkResponse, "POST", "tableHandler.php");
    function checkResponse(res){
        var r = res.responseText;
    }
}

function themes(){   
    if(localStorage.getItem("theme") === "0"){
        localStorage.setItem("theme", "1");    
    }else{
        localStorage.setItem("theme", "0");
    }
    changeTheme();
}

function langs(){
     if(localStorage.getItem("lang") === "en"){
        localStorage.setItem("lang", "pl");    
    }else{
        localStorage.setItem("lang", "en");
    }
    changeLang();
}

//Fucntion from Main Menu which is used in tableRoomJumper()
function arePlayersReadyInWaitingRoom(tableID){
    //Check if players are in ready state
    var ajax = new AjaxRequests();
    var params = "tableID=" + tableID + "&function=verify";
    ajax.sendRequest(params, checkResponse, "POST", "arePlayersReadyInWaitingRoom.php", false);

    function checkResponse(res){
        var r = res.responseText;
        var tab = r.split(":");
        if(tab[0] === "True"){
            //Every player checked raeady in waiting room
            //Redirect and check if player is playing on this table
            // user  bank
            var url = "http://pokershock.pl/Tables/TableCreatedBy_" + tableID + "/GameTable.html";
            window.open(url, "WaitingRoom", "menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes");
        }
        if(tab[0] === "GameOn"){      
            //Player is no in the database table and game has bagun
            alert("Game on ! You are no member of this table !");
            
        }else if(tab[0] === "False"){
            //Redirect to waiting room
            sessionStorage.setItem(tab[1], tab[2]);
            addUser(tableID);
        }
    };
}

function addUser(tableID){
    var ajax = new AjaxRequests();
    var params = "tableID=" + tableID + "&function=add";
    ajax.sendRequest(params, link, "POST", "addPlayerToRoomTable.php");
    function link(res){
        var r = res.responseText;    
        if(r === "True"){
            var url = "http://pokershock.pl/Tables/TableCreatedBy_" + tableID + "/Room.html";
            window.open(url, "WaitingRoom", "menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes");
        }else if (r === "False"){
            alert("Max 6 palyers !");
        } 
    } 
}

//Function linked with onclick div room's in Main Menu 
function tableRoomJumper(user){
    //Onclick from rooms div in main menu
    var tab = user.split("-");
    arePlayersReadyInWaitingRoom(tab[1]);
    
}

function logout(){document.location = "../ScriptWrapper/logout.php";};

function fileBrowser() {
    
    document.getElementById("fileBrowser").disabled = true;
    
    var element = document.getElementById("userMenu");
    
    var form = document.createElement("form");
    
    form.setAttribute("action", "../ScriptWrapper/fileUploader.php");
    form.setAttribute("method", "post");
    form.setAttribute("id", "formFile");
    form.setAttribute("enctype", "multipart/form-data");
    
    var input = document.createElement("INPUT");
    
    input.setAttribute("type", "file");
    input.setAttribute("id", "fileToUpload");
    input.setAttribute("name", "fileToUpload");
    input.setAttribute("style", "display: block; text-align: center; margin: 20px auto; font-size: 1.1em;");
    
    var submit = document.createElement("INPUT");
    submit.setAttribute("type", "submit");
    submit.setAttribute("name", "submit");
    submit.setAttribute("style", "display: block; text-align: center; margin: 20px auto; font-size: 1.1em;");
    
    form.appendChild(input);
    form.appendChild(submit);
    
    element.appendChild(form);
};

function changeLang(){
    var lang = localStorage.getItem("lang");
    var button = document.getElementById("createTable");
    if(lang === "en"){    
        button.innerHTML  = "CREATE TABLE";
        button = document.getElementById("logout");
        button.innerHTML  = "LOG OUT";
        button = document.getElementById("rankbutton");
        button.innerHTML  = "RANKING";
        button = document.getElementById("fileBrowser");
        button.innerHTML  = "CHANGE AVATER";
		button = document.getElementById("chatHistory");
        button.innerHTML  = "CHAT HISTORY";
        document.getElementById("menu").innerHTML = "Main Menu";
		var user = document.getElementById("userid").innerHTML;
		var tab = user.split(":");
		tab[0] = "User: ";
		document.getElementById("userid").innerHTML = tab[0] + tab[1];
		document.getElementById("chatInfo").innerHTML = "Chat with other users:";
        document.getElementById("tandl").innerHTML = "Lang Theme";
    }
    
    if(lang === "pl"){
        button.innerHTML  = "STWÓRZ STÓŁ";
        button = document.getElementById("logout");
        button.innerHTML  = "WYLOGUJ";
        button = document.getElementById("rankbutton");
        button.innerHTML  = "RANKING";
        button = document.getElementById("fileBrowser");
        button.innerHTML  = "ZMIEŃ AVATARA";
		button = document.getElementById("chatHistory");
        button.innerHTML  = "HISTORIA CZATU";
        document.getElementById("menu").innerHTML = "Menu Główne";
        var user = document.getElementById("userid").innerHTML;
		var tab = user.split(":");
		tab[0] = "Użytkownik: ";
		document.getElementById("userid").innerHTML = tab[0] + tab[1];
		document.getElementById("chatInfo").innerHTML = "Czat z innymi graczami:";
        document.getElementById("tandl").innerHTML = "Język i motyw";

    }
}

function getChatHistory(){
    var e = document.getElementById("choice");
    var choice = e.options[e.selectedIndex].text;  
    window.location="../ScriptWrapper/filesGenerator.php?format=" + choice;
}