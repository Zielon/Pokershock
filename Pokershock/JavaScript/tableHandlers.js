//Table handler
//
//***************************************************************


function callPrintFunction(move) {
    var ajax = new AjaxRequests();
    if (move === "in") {
        var params = "owner=" + getOwner() + "&function=call";
        ajax.sendRequest(params, checkResponse, "POST", "pokerTableFunctions.php", false);
        function checkResponse(res) {
            var response = res.responseText;
            document.getElementById('input').value = response;
        }
    }
    if (move === "out") {
        document.getElementById('input').value = "";
    }
}

function sendRise() {
    var ajax = new AjaxRequests();
    var money = document.getElementById('input').value;
    var params = "owner=" + getOwner() + "&function=rise&money=" + String(money);
    ajax.sendRequest(params, check, "POST", "buttonsPassFoldFunctions.php");
    function check(res) {
        var response = res.responseText;
        var tab = response.split(":");
        if (tab[0] === "True") {
            alert("You have rised !");
            document.getElementById('input').value = "";
        } else if (tab[0] === "False") {
            alert(tab[1]);
            document.getElementById('input').value = "";
        }
    }
}

function riseFunction() {
    var ajax = new AjaxRequests();
    var money = document.getElementById('input').value;
    var params = "owner=" + getOwner() + "&function=call";
    ajax.sendRequest(params, checkResponse, "POST", "pokerTableFunctions.php", false);
    function checkResponse(res) {
        var response = res.responseText;
        if (money) {
            if (parseInt(money) > parseInt(response)) {
                sendRise();
            } else {
                alert("Not enough !");
            }
        } else {
            alert("Empty");
        }
    }
}

function callFunction() {
    var ajax = new AjaxRequests();
    var params = "owner=" + getOwner() + "&function=call";
    ajax.sendRequest(params, checkResponse, "POST", "buttonsPassFoldFunctions.php", false);
    function checkResponse(res) {
        var response = res.responseText;
        var tab = response.split(":");
        if (tab[0] === "True") {
            alert(tab[1]);
        }
        if (tab[0] === "False") {
            alert(tab[1]);
        }
    }
}

function foldFunction() {
    var ajax = new AjaxRequests();
    var params = "owner=" + getOwner() + "&function=fold";
    ajax.sendRequest(params, checkResponse, "POST", "buttonsPassFoldFunctions.php", false);
    function checkResponse(res) {
        var response = res.responseText;
        alert(response);
    }
}

function checkFunction() {
    var ajax = new AjaxRequests();
    var params = "owner=" + getOwner() + "&function=check";
    ajax.sendRequest(params, checkResponse, "POST", "buttonsPassFoldFunctions.php");
    function checkResponse(res) {
        var response = res.responseText;
        alert(response);
    }
}

//Main function to update page content
function tableUpdater() {
    setInterval(function () {
        startGame();
       // poker();
    }, 1000);
}

//Init objects
function startGame() {
    
    var fileref=document.getElementById("themes");  //set saved theme    
    var theme = localStorage.getItem("theme");
    var themePath = "../CSS/theme" + theme + ".css";                      
    fileref.setAttribute("href", themePath);     
      
    var ajax = new AjaxRequests();
    var params = "owner=" + getOwner() + "&function=start";
    ajax.sendRequest(params, checkResponse, "POST", "pokerTable.php");
    function checkResponse(res) {
        var response = res.responseText;
        var output = document.getElementById("Table");
        output.innerHTML = response;
        var objDiv = document.getElementById("chatWrapper");
        objDiv.scrollTop = objDiv.scrollHeight;  
    }
}

function getOwner() {
    var owner = document.getElementById('owner').innerHTML;
    var tab = owner.split(" ");
    return tab[2];
}


function sendChat(msg) {
    var ajax = new AjaxRequests();
    var params = "owner=" + getOwner() + "&function=chat&chatMSG=" + msg;
    ajax.sendRequest(params, checkResponse, "POST", "pokerTable.php");
    function checkResponse(res) {
        var response = res.responseText;
    }
}

//Send msg to chat 
function chat() {
   
    $(document).ready(function () {
        $("#posttext").keydown(function (event) {
            var key = event.which;
            if (key >= 33) {
                var maxLength = $(this).attr("maxlength");
                var length = this.value.length;
                if (length >= maxLength) {
                    event.preventDefault();
                }
            }
        });
        $('#posttext').keyup(function (e) {
            if (e.keyCode == 13) {
                var text = $(this).val();
                var maxLength = $(this).attr("maxlength");
                var length = text.length;
                if (length <= maxLength + 1) {
                    $(this).val("");
                    sendChat(text);
                } else {
                    $(this).val(text.substring(0, maxLength));
                }
            }
        });
    });
}

function selectChar(str) {

    if (str.length > 2) {
        if (str.indexOf("king") > -1) {
            return "K";
        }
        if (str.indexOf("queen") > -1) {
            return "Q";
        }
        if (str.indexOf("jack") > -1) {
            return "J";
        }
        if (str.indexOf("ace") > -1) {
            return "A";
        }
    } else
        return str;
}

function parseOutput(tab) {
    var output = "";
    var len = tab.length;
    for (var i = 0; i < len - 1; i++) {
        if (tab[i] !== "") {
            var temp = tab[i].split("_");
            if (temp[2].indexOf("clubs") > -1) {
                output += selectChar(temp[0]) + "♣";
            }
            if (temp[2].indexOf("diamonds") > -1) {
                output += selectChar(temp[0]) + "♦";
            }
            if (temp[2].indexOf("hearts") > -1) {
                output += selectChar(temp[0]) + "♥";
            }
            if (temp[2].indexOf("spades") > -1) {
                output += selectChar(temp[0]) + "♠";
            }
        }
    }
    return output;
}

function sendDict(list){
	var msg = "<?xml version='1.0'?>\n";
	msg += "<data>\n"
	for (var i = 0; i < list.length; ++i) {
		msg += list[i];
	}
	msg += "</data>\n"
	var ajax = new AjaxRequests();
    var params = "owner=" + getOwner() + "&function=end&chatMSG=" + msg;
    ajax.sendRequest(params, checkResponse, "POST", "pokerTable.php");
    function checkResponse(res) {
        var response = res.responseText;
    }
}

function poker() {

    var round = document.getElementById("round").innerHTML;
    var tab = round.split(" ");
    if (tab[1] === "5") {
        var ajax = new AjaxRequests();
        var params = "owner=" + getOwner() + "&function=poker";
        ajax.sendRequest(params, checkResponse, "POST", "pokerTableFunctions.php");
        function checkResponse(res) {
            var response = res.responseText;
            var cards = response.split("&");
            var dictList = [];
            var len = cards.length;
            for (var i = 0; i < len - 1; i++) {
                var temp = cards[i].split("=>");
				var hand = rankHand(parseOutput(temp[0].split(" ")))
                var msg = "<player name=\"" + temp[1].trim() + "\">\n" + hand + "</player>\n";
                dictList.push(msg);				
            }
			sendDict(dictList);
        }
    }
}

// Poker Hand Evaluator by Pat Wilson ©2012 (Chrome|IE8|IE9)

hands=["4 of a Kind", "Straight Flush", "Straight", "Flush", "High Card",
       "1 Pair", "2 Pair", "Royal Flush", "3 of a Kind", "Full House", "-Invalid-" ];
handRanks = [8,9,5,6,1,2,3,10,4,7,0];

function calcIndex(cs,ss) {
  var v,i,o,s; for (i=-1, v=o=0; i<5; i++, o=Math.pow(2,cs[i]*4)) {v += o*((v/o&15)+1);}
  if ((v%=15)!=5) {return v-1;} else {s = 1<<cs[0]|1<<cs[1]|1<<cs[2]|1<<cs[3]|1<<cs[4];}
  v -= ((s/(s&-s) == 31) || (s == 0x403c) ? 3 : 1);
  return v - (ss[0] == (ss[0]|ss[1]|ss[2]|ss[3]|ss[4])) * ((s == 0x7c00) ? -5 : 1);
}

function getCombinations(k,n) {
    console.log('called getcombinations' + ' ' + k + ' ' + n);
    var result = [], comb = [];
        function next_comb(comb, k, n ,i) {
            if (comb.length === 0) {for (i = 0; i < k; ++i) {comb[i] = i;} return true;}
            i = k - 1; ++comb[i];
            while ((i > 0) && (comb[i] >= n - k + 1 + i)) { --i; ++comb[i];}
            if (comb[0] > n - k) {return false;} // No more combinations can be generated
            for (i = i + 1; i < k; ++i) {comb[i] = comb[i-1] + 1;}
            return true;
        }
    while (next_comb(comb, k, n)) { result.push(comb.slice());}
    return result;
}

function getPokerScore(cs) {
    console.log('called getpokerscore ' + cs);
    var a = cs.slice(), d={}, i;
    for (i=0; i<5; i++) {d[a[i]] = (d[a[i]] >= 1) ? d[a[i]] + 1 : 1;}
    a.sort(function(a,b){return (d[a] < d[b]) ? +1 : (d[a] > d[b]) ? -1 : (b - a);});
    return a[0]<<16|a[1]<<12|a[2]<<8|a[3]<<4|a[4];
}    
    
function rankHand(str) {
    var index = 10, winCardIndexes, i ,e;
    
    if (str.match(/((?:\s*)(10|[2-9]|[J|Q|K|A])[♠|♣|♥|♦](?:\s*)){5,7}/g) !== null) {
        var cardStr = str.replace(/A/g,"14").replace(/K/g,"13").replace(/Q/g,"12")
            .replace(/J/g,"11").replace(/♠|♣|♥|♦/g,",");
        var cards = cardStr.replace(/\s/g, '').slice(0, -1).split(",");
        var suits = str.match(/♠|♣|♥|♦/g);
        if (cards !== null && suits !== null) {
            if (cards.length == suits.length) {
                var o = {}, keyCount = 0, j; 
                for (i = 0; i < cards.length; i++) { e = cards[i]+suits[i]; o[e] = 1;}
                for (j in o) { if (o.hasOwnProperty(j)) { keyCount++;}}
                               
                if (cards.length >=5) {
                 if (cards.length == suits.length && cards.length == keyCount) {
                    for (i=0;i<cards.length;i++) { cards[i]-=0; }
                    for (i=0;i<suits.length;i++) 
                        { suits[i] = Math.pow(2, (suits[i].charCodeAt(0)%9824)); }
                    var c = getCombinations(5, cards.length);
                    var maxRank = 0, winIndex = 10;
                    for (i=0; i < c.length; i++) {
                         var cs = [cards[c[i][0]], cards[c[i][1]], cards[c[i][2]], 
                                   cards[c[i][3]], cards[c[i][4]]];
                         var ss = [suits[c[i][0]], suits[c[i][1]], suits[c[i][2]], 
                                   suits[c[i][3]], suits[c[i][4]]];
                         index = calcIndex(cs,ss);
                             
                         if (handRanks[index] > maxRank) {
                             maxRank = handRanks[index];
                             winIndex = index; 
                             wci = c[i].slice();
                         } else if (handRanks[index] == maxRank) {
                             //If by chance we have a tie, find the best one
                             var score1 = getPokerScore(cs);
                             var score2 = getPokerScore([cards[wci[0]],cards[wci[1]],cards[wci[2]],
                                                         cards[wci[3]],cards[wci[4]]]);
                             if (score1 > score2) { wci= c[i].slice(); }
                         }
                    } 
                    index = winIndex; 
                 }                     
               }
			   
				//Show the best cards if cs.length is less than 7 cards.
                var card = [];
                if (cards.length <= 7) {
                    for (i=0; i<7; i++) {
                        if (wci.indexOf(i) == -1) {
                            //Not in the solution                           
                        } else {
						   card.push(i); 								
                        }
                    }
                }
				var output = ""
				var iter = 0;
				for( var obj in o){					
					for (i=0; i<5; i++){
						if( card[i] == iter){							
							if(obj.charAt(1) === "2"){obj = obj.replace("12", "J")}
							if(obj.charAt(1) === "3"){obj = obj.replace("13", "Q")}
							if(obj.charAt(1) === "4"){obj = obj.replace("14", "K")}
							if(obj.charAt(1) === "5"){obj = obj.replace("15", "A")}
							output += "<card>" + obj + "</card>" + "\n";
							break;
						}
					}
					iter +=1;
				}
            }
        }
    }
	
   return "<hand>" + hands[index] + "</hand>\n" + output;
} 






