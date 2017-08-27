<!DOCTYPE html>
<html>
    <head>
        <title>Ranking</title>
        <link rel="stylesheet" type="text/css" href="../CSS/menu.css">       
        <link id="themes" rel="stylesheet" type="text/css" href="../CSS/theme0.css">       
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script type="text/javascript" src="../JavaScript/menuJSwrapper.js"></script>
        <script type="text/javascript" src="../JavaScript/chat_mainMenu.js"></script>
        <script type="text/javascript" src="../JavaScript/ajax.js"></script>
        <script type="text/javascript" src="../JavaScript/chartHandler.js"></script> 
        <script type="text/javascript">
            var data = new Array();
            var dataPie = new Array();
            var players = getPlayersObjectsList();
            players.forEach( function (arrayItem)
            {
                data.push(arrayItem.user + ", " + arrayItem.bank);
                dataPie.push(arrayItem.user);
            });
            var canvas;
            var context;
            // chart properties
            var cWidth, cHeight, cMargin, cSpace;
            var cMarginSpace, cMarginHeight;
            // bar properties
            var bWidth, bMargin, totalBars, maxDataValue;
            var bWidthMargin;
            // bar animation
            var ctr, numctr, speed;
            // axis property
            var totLabelsOnYAxis;            
        </script>
    </head>
    <body onload="fullfillRanking()">
        <div id="themesWrapper">
           <div>Lang Theme</div>
           <img id="langImg" alt="Lang" src="../Themes/theme1.png" onclick="langs()" />
           <img id="themeImg" alt="Theme" src="../Themes/theme0.png" onclick="themes()"/>
        </div>  
        
        <div id="ranking">  
            <b>Ranking</b>
            <table id="players" style="width:100%">
            <tr>
              <th>Pos</th>
              <th>Player</th> 
              <th>In bank</th>
              <th>Game</th>
            </tr>
            </table> 
        </div>
        <canvas id="chart" width="900" height="600"></canvas>
        <select id="type" onclick="checkType()">
             <option value="bar">Bar Chart</option>
             <option value="pie">Pie Chart</option>
         </select>
    </body>
</html>