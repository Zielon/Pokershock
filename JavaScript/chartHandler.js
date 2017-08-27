//Ranking functions

function getPlayersObjectsList(){
    var ajax = new AjaxRequests();
    ajax.sendRequest(null, checkResponse, "POST", "getPlayersXML.php", false);
    
    var objectList = [];
     
    var xml;
    function checkResponse(res){
        xml = res.responseText;       
    };    
    var i = 1;
    $(xml).find("player").each(function(){
        var user = $(this).find("user").text();
        var bank = $(this).find("bank").text();
        var online = $(this).find("online").text();      
        if(online !== ""){online = "online";}else{online = "offline";};
        var player = { position:i, user:user, bank:bank, online:online};   
        i++;
        objectList.push(player);
    });
    
    return objectList;
}

function checkType(){
    $("#type").change(function(){
        generateChart();
    });
}

function fullfillRanking(){
       
    var playersContainer = document.getElementById("players");  
    players.forEach( function (arrayItem)
    {
        var player = document.createElement("tr");
        for (var property in arrayItem) {
            var cell = document.createElement("td");
            var text;
            text = document.createTextNode(arrayItem[property]);
            cell.appendChild(text);  
            player.appendChild(cell); 
        }
        playersContainer.appendChild(player);
    });  
    
    generateChart();
}

function generateChart(){
        
    var e = document.getElementById("type");
    var type = e.options[e.selectedIndex].text;
    
    if(type === "Pie Chart"){ 
        var context;
        var canvas = document.getElementById("chart");
        if (canvas && canvas.getContext) {
            context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
        }
        var lastend = 0;
        var money = []; 
        var color = [];
        players.forEach( function (arrayItem)
        {
            money.push(arrayItem.bank);
            color.push(getRandomColor());
        });
        var myTotal = 0; 
        for (var e = 0; e < money.length; e++) {
            myTotal += parseInt(money[e]);
        }
        
        var radius = canvas.height / 2;
        var midX = canvas.width / 2;
        var wedge;
        for (var i = 0; i < money.length; i++) {
            context.fillStyle = color[i];
            context.beginPath();
            context.moveTo(midX, radius);
            wedge =  Math.PI * 2 * (money[i] / myTotal);
            context.arc(midX, radius, radius, lastend,lastend + wedge, false);
            context.lineTo(midX, radius);
            context.fill();
            var labAngle = lastend + wedge / 2;
            var labX = midX + Math.cos(labAngle) * radius * 0.95;
            var labY = radius + Math.sin(labAngle) * radius * 0.95;
            context.save();
            context.shadowColor = "r";
            context.shadowOffsetX = 1;
            context.shadowOffsetY = -1;
            context.fillStyle = "#FFF";
            context.font = "10pt Consolas";
            context.fillText(dataPie[i] + " $" + money[i], labX, labY);
            context.restore();
            lastend += Math.PI * 2 * (money[i] / myTotal);  
        }   
        
    }else{
        barChart();
    }
}

function barChart() {
    canvas = document.getElementById('chart');
    if (canvas && canvas.getContext) {
        context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);
    }
    chartSettings();
    drawAxisLabelMarkers();
    drawChartWithAnimation();
}
// initialize the chart and bar values
function chartSettings() {
    // chart properties
    cMargin = 25;
    cSpace = 60;
    cHeight = canvas.height - 2 * cMargin - cSpace;
    cWidth = canvas.width - 2 * cMargin - cSpace;
    cMarginSpace = cMargin + cSpace;
    cMarginHeight = cMargin + cHeight;
    // bar properties
    bMargin = 15;
    totalBars = data.length;
    bWidth = (cWidth / totalBars) - bMargin;
    // find maximum value to plot on chart
    maxDataValue = 0;
    for (var i = 0; i < totalBars; i++) {
        var arrVal = data[i].split(",");
        var barVal = parseInt(arrVal[1]);
        if (parseInt(barVal) > parseInt(maxDataValue))
            maxDataValue = barVal;
    }
    totLabelsOnYAxis = 10;
    context.font = "8pt Consolas";
    // initialize Animation variables
    ctr = 0;
    numctr = 100;
    speed = 10;
}
// draw chart axis, labels and markers
function drawAxisLabelMarkers() {
    context.lineWidth = "2.0";
    // draw y axis
    drawAxis(cMarginSpace, cMarginHeight, cMarginSpace, cMargin);
    // draw x axis
    drawAxis(cMarginSpace, cMarginHeight, cMarginSpace + cWidth, cMarginHeight);
    context.lineWidth = "1.0";
    drawMarkers();
}
// draw X and Y axis
function drawAxis(x, y, X, Y) {
    context.beginPath();
    context.moveTo(x, y);
    context.lineTo(X, Y);
    context.closePath();
    context.strokeStyle = '#FFF';
    context.stroke();
}
// draw chart markers on X and Y Axis
function drawMarkers() {
    var numMarkers = parseInt(maxDataValue / totLabelsOnYAxis);
    context.textAlign = "right";
    context.fillStyle = "#FFF";
    // Y Axis
    for (var i = 0; i <= totLabelsOnYAxis; i++) {
        markerVal = i * numMarkers;
        markerValHt = i * numMarkers * cHeight;
        var xMarkers = cMarginSpace - 5;
        var yMarkers = cMarginHeight - (markerValHt / maxDataValue);
        context.fillText(markerVal, xMarkers, yMarkers, cSpace);
    }
    // X Axis
    context.textAlign = 'center';
    for (var i = 0; i < totalBars; i++) {
        arrval = data[i].split(",");
        name = arrval[0];
        markerXPos = cMarginSpace + bMargin + (i * (bWidth + bMargin)) + (bWidth / 2);
        markerYPos = cMarginHeight + 10;
        context.fillText(name, markerXPos, markerYPos, bWidth);
    }
    context.save();
    // Add Y Axis title
    context.translate(cMargin + 10, cHeight / 2);
    context.rotate(Math.PI * -90 / 180);
    context.fillText('Money in bank', 0, 0);
    context.restore();
    // Add X Axis Title
    context.fillText('Players', cMarginSpace + (cWidth / 2), cMarginHeight + 30);
}
function drawChartWithAnimation() {
    // Loop through the total bars and draw
    for (var i = 0; i < totalBars; i++) {
        var arrVal = data[i].split(",");
        bVal = parseInt(arrVal[1]);
        bHt = (bVal * cHeight / maxDataValue) / numctr * ctr;
        bX = cMarginSpace + (i * (bWidth + bMargin)) + bMargin;
        bY = cMarginHeight - bHt - 2;
        drawRectangle(bX, bY, bWidth, bHt, true);
    }
    // timeout runs and checks if bars have reached the desired height
    // if not, keep growing
    if (ctr < numctr) {
        ctr = ctr + 1;
        setTimeout(arguments.callee, speed);
    }
}
function drawRectangle(x, y, w, h, fill) {
    context.beginPath();
    context.rect(x, y, w, h);
    context.closePath();
    context.stroke();
    if (fill) {
        var gradient = context.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'green');
        gradient.addColorStop(1, 'rgba(67,203,36,.15)');
        context.fillStyle = gradient;
        context.strokeStyle = gradient;
        context.fill();
    }
}

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}