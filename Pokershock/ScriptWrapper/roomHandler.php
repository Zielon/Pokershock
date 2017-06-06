<?php

/*
 * 
 * 
 */
// Headers
require_once ('dbConnector.php');
session_start();

function sessionHandler($case, $owner, $instance) {
    switch ($case) {
        //add table
        case 0:
            $temp = $_SESSION["activTables"];
            $temp .= "TableCreatedBy_" . $owner . "&";
            $_SESSION["activTables"] = $temp;
            break;
        //delete table file
        case 1:
            $temp = $_SESSION["activTables"];
            $tables = explode("&", $temp);
            $pattern = "TableCreatedBy_" . $owner;
            for ($i = 0; $i < count($tables); $i++) {
                if ($tables[$i] == $pattern) {
                    unset($tables[$i]);
                }
            }
            $_SESSION["activTables"] = implode("&", $tables);
            //deleteFromCardHistory($owner, $instance);
            break;
        default:
            break;
    }
}

function drawCards($owner, $amount) {
    $cardsFile = file('../Tables/cards_names.txt');
    $count = count($cardsFile);
    $cardsArray = array();
    $stop = (int) $amount + 1;
    $myFile = "../Tables/TableCreatedBy_" . $owner . "/cardsHistory.txt";
    if (file_exists($myFile)) {
        $selected = array();
        $history = file($myFile);
        
        foreach($history as $seleced){
            $line = trim($seleced, "\n");
            $selected[] = $line;
        }  
        
        $i = 1;
        while ($i < $stop) {
            $int = rand(0, 9999999999) % $count;
            $id = $cardsFile[$int];
            $cardList = explode(".", $id);
            $cardID = $cardList[0];
            $flag = true;
            foreach ($selected as $card) {
                if (strpos($card, $cardID) !== false) {
                    $flag = false;
                }
            }
            foreach ($cardsArray as $selectedCard) {
                if (strpos($selectedCard, $cardID) !== false) {
                    $flag = false;
                }
            }
            if ($flag === true) {
                $cardsArray[] = $cardID;
                $i++;
            }
        }
    }
    $f = fopen($myFile, "a+");    
    foreach($cardsArray as $selectedCards){
        fwrite($f, $selectedCards . "\n");
    }
    fclose($f);
    return $cardsArray;
}

$func =  test_input($_POST["function"]);
$owner =  test_input($_POST["owner"]);

$instance = DbConnector::getInstance();
$conn = $instance->getConnection();

$html = "";

if ($conn) {
    if ($func == "update") {
        $sql = "SELECT `USER`, `IsReady` FROM `TableCreatedBy_" . $owner . "`;";
        $retval = $instance->sendRequest($sql);
        echo "<h3>Players</h3>";
        while ($row = mysql_fetch_row($retval)) {

            foreach (glob("../UsersAvatars/" . $row[0] . ".*") as $file) {
                $file_parts = pathinfo($file);
                $avatar = $row[0] . "." . $file_parts['extension'];
                if ($row[1] == "0") {
                    echo "<div class=\"avatarClass\">"
                    . "<img id=\"avatar\" src=\"../../UsersAvatars/" . $avatar . "\">"
                    . "<p id=\"user\">" . $row[0] . "</p>"
                    . "</div>";
                } else if ($row[1] == "1") {
                    echo "<div class=\"avatarClass\">"
                    . "<img id=\"avatar\" style=\"padding:3px; border:3px solid red;\" src=\"../../UsersAvatars/" . $avatar . "\">"
                    . "<p id=\"user\" style=\"color :red;\">" . $row[0] . "</p>"
                    . "</div>";
                }
            }
        }
    } else if ($func == "delete") {
        $sql = "DELETE FROM `TableCreatedBy_" . $owner . "` WHERE `USER`='" . $_SESSION['user'] . "'";
        sessionHandler(1, $owner, $instance);
        $retval = $instance->sendRequest($sql);
    } else if ($func == "ready") {

        $money = $_POST["money"];
        $sql = "SELECT `BANK` FROM `Users` where `LOGIN`='" . $_SESSION["user"] . "';";
        $retval = $instance->sendRequest($sql);
        while ($row = mysql_fetch_row($retval)) {
            $temp = (int) $row[0] - (int) $money;
            $sql = "UPDATE `Users` SET `BANK`=" . (string) $temp . " where `LOGIN`='" . $_SESSION["user"] . "'";
            $retval = $instance->sendRequest($sql);
            break;
        }

        $cards = drawCards($owner, 2);
        echo "F: " . $cards[0] . " S: " . $cards[1];
        $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `IsReady`=1, `Money`=" . $money .
                ", `CARD_A`='" . $cards[0] . "',`CARD_B`='" . $cards[1] . "'  WHERE `USER`='" . $_SESSION['user'] . "';";

        sessionHandler(0, $owner, $instance);
        $instance->sendRequest($sql);
    } else if ($func == "deleteTable") {

        $sql = "UPDATE `ActiveUsers` SET `Table`=0 WHERE `USER`='" . $owner . "';";
        $retval = $instance->sendRequest($sql);

        $dirPath = "../Tables/TableCreatedBy_" . $owner . "/";
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);

        $sql = "DROP TABLE TableCreatedBy_" . $owner . ";";
        $retval = $instance->sendRequest($sql);
    } else if ($func == "bank") {
        echo $_SESSION["user"];
    }
}
?>