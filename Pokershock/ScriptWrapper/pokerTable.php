<?php
/* Main function to play a game
 * 
 * 
 */

// Headers
include 'roomHandler.php';
include 'pokerTableFunctions.php';
session_start();

class XMLTool{
    public static function getTagValue($dom, $tagName) {
        $value = $dom->getElementsByTagName($tagName);
        return $value[0]->nodeValue;
    }
    public static function setTagValue($dom, $tagName, $tagNewValue){
        $value = $dom->getElementsByTagName($tagName);
        $value[0]->nodeValue = $tagNewValue;
    }   
}

function updateTotalMoney($value, $instance, $owner){
    if($value == 0){
        $sql = "UPDATE `TableCreatedBy_".$owner."` SET `TotalMoneyOnTable`=0, `Transfer`=0;";
    }else{
        $sql = "UPDATE `TableCreatedBy_".$owner."` SET `TotalMoneyOnTable`=`TotalMoneyOnTable` + ".$value.";";
    }
    $retval = $instance->sendRequest($sql); 
}

//Finding next player to set blind
function findNext($sqlData, $dom, $blindName) {
    mysql_data_seek($sqlData, 0);
    $blind = $dom->getElementsByTagName($blindName);
    $numResults = mysql_num_rows($sqlData);
    $counter = 1;
    $flag = false;
    while ($row = mysql_fetch_row($sqlData)) {
        if ($counter == $numResults && $flag === false) {        //if is is last user in table 
            mysql_data_seek($sqlData, 0);                       //go to the begining of table
            while ($row = mysql_fetch_row($sqlData)) {
                $blind[0]->nodeValue = $row[0];
                return;
            }
        }
        if ($flag === true) {
            $blind[0]->nodeValue = $row[0];
            break;
        }
        if ($row[0] === $blind[0]->nodeValue) {
            $flag = true;
        }
        $counter++;
    }
}




//Modify xml file with currentMove tag 
function selectNextPlayerInRound($dom, $owner, $instance) {

    $sql = "SELECT `USER`, `Move` FROM `TableCreatedBy_" . $owner . "`;";
    $retval = $instance->sendRequest($sql);
    $file = "../Tables/TableCreatedBy_" . $owner . "/tableDataPoker.xml";

    $player = $dom->getElementsByTagName('currentMove');
    $currentPlayer = $player[0]->nodeValue;

    $numResults = mysql_num_rows($retval);
    $counter = 0;
    $flag = false;

    $arrayOfPlayers = array(); 
    while ($row = mysql_fetch_row($retval)) {
        $arrayOfPlayers[$row[0]] = $row[1];
    }
    
    $index = false;
    while($flag === false){ 
        foreach ($arrayOfPlayers as $k => $v) {
            if($index && $v !== "fold" && $k !== $currentPlayer){
                $player[0]->nodeValue = $k;
                $time = $dom->getElementsByTagName('time');
                $time[0]->nodeValue = date('H:i:s', time());
                $dom->save($file);
                $flag = true;
                return;
            }
            
            if($k == $currentPlayer){
               $index = true;
            }
        }
    }
}

//Select next player and set small and big blinds
function blindsHandler($instance, $dom, $owner, $sqlData) {

    $bigBlind = $dom->getElementsByTagName('bigBlind');
    $smallBlind = $dom->getElementsByTagName('smallBlind');
    $player = $dom->getElementsByTagName('currentMove');

    $flag = false;
    if ($bigBlind[0]->nodeValue === "none" && $smallBlind[0]->nodeValue === "none") { //beginning of game only once
        $sql = "SELECT `USER` FROM `TableCreatedBy_" . $owner . "` Limit 2;";
        $retval = $instance->sendRequest($sql);
        while ($row = mysql_fetch_row($retval)) {
            if ($flag === false) {
                $smallBlind[0]->nodeValue = $row[0];
                $flag = true;
            } else if ($flag === true) {
                $bigBlind[0]->nodeValue = $row[0];
            }
        }
    } else {
        findNext($sqlData, $dom, "bigBlind");
        findNext($sqlData, $dom, "smallBlind");
        $bigBlind = $dom->getElementsByTagName('bigBlind');
        $smallBlind = $dom->getElementsByTagName('smallBlind');
        
    }

    $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `MoneyOnTable` = '50' where `user`='" . $smallBlind[0]->nodeValue . "';";
    $retval = $instance->sendRequest($sql);
    $player[0]->nodeValue = $smallBlind[0]->nodeValue;
    
    $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `MoneyOnTable` = '100' where `user`='" . $bigBlind[0]->nodeValue . "';";
    $retval = $instance->sendRequest($sql);
    
    updateTotalMoney("150", $instance, $owner);
}

//Returns boolean type
function isEndOfRound($instance, $owner) {
    $flag = true;
    $sql = "SELECT MAX(`MoneyOnTable`) FROM `TableCreatedBy_" . $owner . "`;";
    $retval = $instance->sendRequest($sql);
    while ($row = mysql_fetch_row($retval)) {
        $max = $row[0];
    }

    $counter = 0;
    
    if ($max != null) {
        $sql = "SELECT `USER`, `Move`, `MoneyOnTable` FROM `TableCreatedBy_" . $owner . "` where `Move`!='fold';";
        $retval = $instance->sendRequest($sql);
        $numResults = mysql_num_rows($retval);
        $counter = 0;
        while ($row = mysql_fetch_row($retval)) {
            if ($row[2] === $max) {
                $counter++;
            }
        }
        if ($numResults === $counter) {
            $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `Move`='none', `MoneyOnTable`=NULL  WHERE `MoneyOnTable`=" . $max . ";";
            $retval = $instance->sendRequest($sql);
            return true;
        }else{
            return false;
        }
       
    } else {
        return false;
    }
}

//Function to manage rounds steps 
function setValuesInXmlFile($instance, $owner, $doc, $dom, $retvalData) {
    $startFlag = $dom->getElementsByTagName('start');
    //flag equalas true when round is started after BlindSet
    $flag = $startFlag[0]->nodeValue;               
    $file = "../Tables/TableCreatedBy_" . $owner . "/tableDataPoker.xml";
    $round = $dom->getElementsByTagName('roundCounter');
    
    //chcek if there is last player
    theLastPlayer($instance, $dom, $file, $owner);
    
    if ($round[0]->nodeValue !== "Summary") {
        //Draw 3 cards and set it in xml file with data
        if ($flag === "BlindSet") {
            // change blinds
            blindsHandler($instance, $dom, $owner, $retvalData); 
            $startFlag[0]->nodeValue = "true";
            $cards = drawCards($owner, 3);
            $card = $dom->getElementsByTagName('cardA');
            $card[0]->nodeValue = $cards[0];
            $card = $dom->getElementsByTagName('cardB');
            $card[0]->nodeValue = $cards[1];
            $card = $dom->getElementsByTagName('cardC');
            $card[0]->nodeValue = $cards[2];
            $dom->save($file);
        }
        //Beginning of the round
        if ($flag == "true") {    
            $startFlag[0]->nodeValue = "none";
            $sql = "SELECT `USER`, `Move` FROM `TableCreatedBy_" . $owner . "` where `Move`!='fold' Limit 1";
            $retval = $instance->sendRequest($sql);

            $time = $dom->getElementsByTagName('time');
            $time[0]->nodeValue = date('H:i:s', time());
            $dom->save($file);
            
          //Every other move is consider as none
        } else if ($flag == "none") {
            //Check if end of round is met and set next cards
            if (isEndOfRound($instance, $owner)) {
                $nextCard = drawCards($owner, 1);
                $counter = $dom->getElementsByTagName('roundCounter');
                $value = $counter[0]->nodeValue;
                $roundNumber = $value + 1;
                //Set value in xml file for the next drawed card
                if ($roundNumber == 3) {
                    $card = $dom->getElementsByTagName('cardD');
                    $card[0]->nodeValue = $nextCard[0];
                }
                if ($roundNumber == 4) {
                    $card = $dom->getElementsByTagName('cardE');
                    $card[0]->nodeValue = $nextCard[0];
                }
                $counter[0]->nodeValue = $roundNumber;
                $startFlag = $dom->getElementsByTagName('start');
                if ($roundNumber == 5) {        
                    $r = $dom->getElementsByTagName('roundCounter');
                    $r[0]->nodeValue = "Summary";
                }else{
                   $startFlag[0]->nodeValue = "true";
                }
                
                $smallBlind = $dom->getElementsByTagName('smallBlind');
                $player = $dom->getElementsByTagName('currentMove');
                $player[0]->nodeValue = $smallBlind[0]->nodeValue;
                
                $dom->save($file);
                return;
            }
            //Else contiune round and increase round counter
            $roundCounter = $dom->getElementsByTagName('roundCounter');
            if ($roundCounter[0]->nodeValue > 1) {
                $total = 0;
                while ($row = mysql_fetch_row($retvalData)) {
                    $total += $row[2];
                }
                $totalMoney = $dom->getElementsByTagName('totalMoney');
                $value = $totalMoney[0]->nodeValue;
                $totalMoney[0]->nodeValue = $total + (int) $value;
                mysql_data_seek($retvalData, 0);
            }

            $actualTime = date('H:i:s', time());
            $time = $dom->getElementsByTagName('time');

            $time1 = new DateTime($time[0]->nodeValue);
            $time2 = new DateTime($actualTime);
            $interwal = $time2->diff($time1);

            //*********************************************************************

            $nodes = $doc->getElementById('stats');
            $round = $dom->getElementsByTagName('roundCounter');
            $player = $dom->getElementsByTagName('currentMove');
            $currentPlayer = $player[0]->nodeValue;
            $element = $doc->createElement('div', "Round " . $round[0]->nodeValue);
            $element->setAttribute("id", 'round');
            $nodes->appendChild($element);
            $element = $doc->createElement('time', "Current Player: " . $currentPlayer . " | Time: " . $interwal->format('%i:%s'));
            $nodes->appendChild($element);

            //*********************************************************************

            $sql = "SELECT `USER`, `Move` FROM `TableCreatedBy_" . $owner . "` where `user`='" . $currentPlayer . "';";
            $retval = $instance->sendRequest($sql);

            //check what kind of move player did
            while ($row = mysql_fetch_row($retval)) {$state = $row[1]; break;}
            
            if ($state !== "none") {
                selectNextPlayerInRound($dom, $owner, $instance);
            }
            //Time out, fold player for this play
            $timeList = explode(":", $interwal->format('%i:%s'));
            if ($timeList[0] >= 50) {
                $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `Move` = 'fold' where `user`='" . $_SESSION["user"] . "';";
                $retval = $instance->sendRequest($sql);
                selectNextPlayerInRound($dom, $owner, $instance);
            }
        }
    } else {
        $flag = endOfRound($doc, $owner, $dom, $file);
        if ($flag === true) {
            cleanDataDataFiles($dom, $owner, $instance);
            $dom->save($file);
            return;
        }
    }
}

//Prepare xml file for the next round sets values
function cleanDataDataFiles($dom, $owner, $instance) {
    $elem = $dom->getElementsByTagName('time');
    $elem[0]->nodeValue = date('H:i:s', time());

    $elem = $dom->getElementsByTagName('start');
    $elem[0]->nodeValue = "BlindSet";

    $elem = $dom->getElementsByTagName('roundCounter');
    $elem[0]->nodeValue = "1";

    $root = $dom->documentElement;
    foreach($root->childNodes as $e){
       if (strpos($e->nodeName,'card') !== false) {
           $e->nodeValue = "question";
       }
       
    }
    
    $myFile = "../Tables/TableCreatedBy_" . $owner . "/cardsHistory.txt";
    if (file_exists($myFile)) {
        $f = fopen($myFile, "w+");
        fwrite($f, $cardsArray[0] . "\n");
        fclose($f);
    }
    
    $sql = "SELECT `USER` FROM `TableCreatedBy_" . $owner . "`;";
    $retval = $instance->sendRequest($sql);
    while ($row = mysql_fetch_row($retval)) {  
        $cards = drawCards($owner, 2);
        $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `CARD_A`='" . $cards[0] . "',`CARD_B`='" . $cards[1] . "', `Move`='none' WHERE `USER`='" . $row[0] . "';";
        $val = $instance->sendRequest($sql); 
    }
    updateTotalMoney(0, $instance, $owner);
}


function theLastPlayer($instance, $dom, $file, $owner){
    $sql = "SELECT `USER`, `Move` FROM `TableCreatedBy_" . $owner . "`;";
    $retval = $instance->sendRequest($sql);
    $counter = 0;
    $numResults = mysql_num_rows($retval);
    while ($row = mysql_fetch_row($retval)) {
       if($row[1] === "fold"){
           $counter++;
       }
    }
    $r = $dom->getElementsByTagName('roundCounter');
    if($counter > 0 && $numResults - $counter == 1 && $r[0]->nodeValue !== "Summary"){
        $r[0]->nodeValue = "Summary";
        $dom->save($file);
    }
}

//Prints summray file after end of round
function endOfRound($doc, $owner, $dom, $file) {

    $actualTime = date('H:i:s', time());

    $time = $dom->getElementsByTagName('time');
    $time1 = new DateTime($time[0]->nodeValue);
    $time2 = new DateTime($actualTime);
    $interwal = $time2->diff($time1);
    $timeList = explode(":", $interwal->format('%i:%s'));

    //Set time for dispaly round result
    if ($timeList[1] >= 40) {
        return true;
    }
    
    $nodes = $doc->getElementById('stats');
    $str = chcekTheWinner($dom, $owner);
    $ele = $doc->createElement('div',$str);
    $nodes->appendChild($ele);
}

// *****************************************************************************
// Main body
//
$instance = DbConnector::getInstance();
$conn = $instance->getConnection();

$func = test_input($_POST["function"]);
$owner = test_input($_POST["owner"]);
$msg = test_input($_POST["chatMSG"]);

if ($func == "chat") {
    chatUpdater($owner, $msg);
}

if ($conn) {

    $file = "../Tables/TableCreatedBy_" . $owner . "/tableDataPoker.xml";
    $dom = new DOMDocument();
    $dom->formatOutput = true;
    $dom->preserveWhiteSpace = false;
    $dom->load($file);

    $doc = new DOMDocument();
    $doc->loadHTMLFile("../Tables/tableSketch.html");

    if ($func === "start") {

        $sql = "SELECT `USER`, `Move`,`MoneyOnTable`, `Money`, `TotalMoneyOnTable` FROM `TableCreatedBy_" . $owner . "`;";
        $retval = $instance->sendRequest($sql);

        //Start functions
        setValuesInXmlFile($instance, $owner, $doc, $dom, $retval);
        players($retval, $doc, $dom);
        chat($doc, $owner);
        buttons($instance, $owner, $doc, $dom);
        printCards($instance, $doc, $owner, $dom);
        print $doc->saveXML();
    }
}
?>