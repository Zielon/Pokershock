<!DOCTYPE html>
<html>
    <head>
        <title>Menu</title>
        <link rel="stylesheet" type="text/css" href="../CSS/menu.css">        
        <link id="themes" rel="stylesheet" type="text/css" href="../CSS/theme0.css">      
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script type="text/javascript" src="../JavaScript/menuJSwrapper.js"></script>
        <script type="text/javascript" src="../JavaScript/chat_mainMenu.js"></script>
        <script type="text/javascript" src="../JavaScript/ajax.js"></script>
    </head>

    <body onload="changeLang()">
        <h1 id="menu">Main Menu</h1>
        <div id="wrap"></div>
        <script type="text/javascript">
            start();
            setInterval('updateChat()', 500);
        </script>
    </body>
</html>