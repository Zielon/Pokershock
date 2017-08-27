
//Waiting room funcitons
//
//***************************************************************

function deleteTable(){
    var ajax = new AjaxRequests();
    var params = "function=deleteTable" + "&owner=" + getOwner();
    ajax.sendRequest(params, checkResponse, "POST", "roomHandler.php");
    function checkResponse(res){}
}

function deleteUser(){
    var ajax = new AjaxRequests();
    var params = "function=delete" + "&owner=" + getOwner();
    ajax.sendRequest(params, checkResponse, "POST", "roomHandler.php", false);
    function checkResponse(res){
        var response = res.responseText;
        window.close(); 
    };
}

function playerIsReady(){
    var user = sessionStorage.getItem("poker_Player");
    if (sessionStorage.getItem(user)) {
        var money = document.getElementById("input").value;
        var bank = sessionStorage.getItem(user);
        if(money){
            if(parseInt(bank) > parseInt(money)){
               var transfer = money;
            }
        }else{
            alert("Error");
            return;
        }
    }
    var ajax = new AjaxRequests();
    var params = "function=ready" + "&owner=" + getOwner() + "&money=" + document.getElementById("input").value;
    ajax.sendRequest(params, checkResponse, "POST", "roomHandler.php");
    function checkResponse(res){
        document.getElementById("input").value = "Transfer is done";
    }
}

function WaitingRoom(){
    var ajax = new AjaxRequests();
    var params = "function=update" + "&owner=" + getOwner();
    ajax.sendRequest(params, checkResponse, "POST", "roomHandler.php");

    function checkResponse(res){  
        var response = res.responseText;
        var output = document.getElementById("playersWrapper");   
        output.innerHTML = response;  
    }
}

function bankHandler(){
    var user = sessionStorage.getItem("poker_Player");
    if (sessionStorage.getItem(user)) {
        var money = document.getElementById("input").value;
        var bank = sessionStorage.getItem(user);
        if(money){    
            var isnum = /^\d+$/.test(money);
            if(parseInt(bank) <= parseInt(money)){
                document.getElementById("bank").textContent = "You do not have enough money !"
            }else if(isnum){
                document.getElementById("bank").textContent = parseInt(bank) - parseInt(money);
            }else if(money.indexOf("Transfer is done") > -1){
                document.getElementById("bank").textContent = "Waiting for others"
            }else{
                document.getElementById("bank").textContent = "Wrong input !";
            }
        }else{
            document.getElementById("bank").textContent = parseInt(bank);
        }
    }
}

function roomUpdater() {
            
    var fileref=document.getElementById("themes");  //set saved theme    
    var theme = localStorage.getItem("theme");
    var themePath = "../CSS/theme" + theme + ".css";                      
    fileref.setAttribute("href", themePath);     
                    
    setInterval(function () {        
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                var response = xhttp.responseText;        
                if(response === "Ready"){
                    var url = "/Tables/TableCreatedBy_" + getOwner() + "/GameTable.html";
                    window.location.href = url; 
                }else{
                    var tab = response.split(":");
                    if(tab[0] < 15){
                        document.getElementById('time').textContent = tab[0] + ":" + tab[1];
                    }else{
                        deleteTable();
                        alert("End of time or you are last player on table");
                        document.getElementById('time').textContent = "END OF TIME TO CREATE GAME";
                        window.close(); 
                    }
                }
                WaitingRoom();
                bankHandler();
            }   
        };   
        var params = "owner=" + getOwner() +"&function=verify";
        xhttp.open("POST", "timer.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(params); 
        
    }, 1000);   
}


function getOwner(){
    var owner = document.getElementById('owner').innerHTML;
    var tab =  owner.split(" ");
    return tab[3];
}

function themes(){
     if(localStorage.getItem("theme") == "0"){
        localStorage.setItem("theme", "1");    
    }else{
        localStorage.setItem("theme", "0");
    }
    changeTheme();
}
