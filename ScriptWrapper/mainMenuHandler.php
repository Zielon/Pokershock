<?php
/*
 * 
 * 
 */
// Headers
    include('dbConnector.php');
    
    session_start();

    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();

    $countUsers = -1;

    if($conn){

        $sql = "SELECT count(*) AS Total FROM `ActiveUsers`;";
        $retval = $instance->sendRequest($sql);
        $data = mysql_fetch_assoc($retval);
        $countUsers = $data['Total'];  
    }

    $filePath = $_SESSION['user'];
    $list = glob("../UsersAvatars/" . $filePath. ".*");

    $lang = test_input($_POST["lang"]);

    if($lang === "pl"){
        $u = "Użytkownik: ";
        $c = "Czat z innymi graczami:";
    }
    if($lang === "en"){
        $u = "User:" ;
        $c = "Chat with other users:";
    }

    echo "
    <div id=\"themesWrapper\">
           <div id=\"tandl\">Lang Theme</div>
           <img id=\"langImg\" alt=\"Lang\" src=\"../Themes/theme1.png\" onclick=\"langs()\" />
           <img id=\"themeImg\" alt=\"Theme\" src=\"../Themes/theme0.png\" onclick=\"themes()\"/>
    </div>

    <div id=\"leftSide\">
         <p id=\"userid\">$u". $_SESSION['user'] . " <p>
         <img id=\"avatar\" src=\"../UsersAvatars/" . $list[0] . "\">
         
         <div id=\"activeTables\">
            <div id=\"tablesWraper\"></div>
        </div>
    </div>

    <div id=\"userMenu\">
        <button id=\"createTable\" onclick=\"createTable()\" >Create Table</button>
        <button id=\"fileBrowser\" onclick=\"fileBrowser()\" >Change Avatar</button>
        <button id=\"rankbutton\" onclick=\"location.href='ranking.php';\">Ranking</button>
        <button id=\"logout\" onclick=\"logout()\">Log Out</button>
        <button id=\"chatHistory\" onclick=\"getChatHistory()\">Chat history</button>
        <select id=\"choice\">
            <option value=\"xml\">XML</option>
            <option value=\"txt\">TEXT</option>
            <option value=\"html\">HTML</option>
            <option value=\"json\">JSON</option>
        </select>
    </div>

    <div id=\"chat\">
        <p id=\"online\"></p>
        <div id=\"chatBox\"></div>
        <p id=\"chatInfo\">$c</p>
        <textarea id=\"posttext\" maxlength=40 ></textarea>
    </div>"

?>