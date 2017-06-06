<?php

/*
 * 
 * 
 */
// Headers
require_once ('dbConnector.php');


//Base class 
class Player{
    
    //Members 
    public $player;
    public $hand;
    public $cards = array();
    public $handValue;
    public $cardsValue;
    public $move;
    
    //Constructor
    public function __construct($p, $h, $c, $hV) {
        $this->player = $p;
        $this->hand = $h;
        $this->cards = $c;
        $this->handValue = $hV;     
        
        // cards score before multiplier in case of only cards
        // from table will be selected as player hand cards
        $v = 0;
        foreach($c as $singleCard){
            if($singleCard === "J"){ $v += 12;}
            else if($singleCard === "Q"){ $v += 13;}
            else if($singleCard === "K"){ $v += 14;}
            else if($singleCard === "A"){ $v += 15;}
            else{ $v += (int)$singleCard;}
        }
        $this->cardsValue = $v + (int)$this->handValue;
    }
    
    //Public methods
    public function getPlayer(){ return $player;}
    public function getHand(){return $hand;}
    public function getCards(){return $cards;}
    public function getHandValue(){return $handValue;}
    public function getCardsValue(){return $cardsValue;}
    
    //Comapre method used as a functional object in sort method
    public function cmp($a, $b)
    {
        if((int)$a->cardsValue < (int)$b->cardsValue){
            return true;
        }else{
            return false;
        }
    }
    
    public function printArray(){
        $str = "";
        foreach($this->cards as $c){
            $str .= $c;
        }
        return $str;
    }
}

//Function which is called from pokerTable.php file
function chcekTheWinner($dom, $owner){
    $instance = DbConnector::getInstance();
    $playersList = handEvaluator();
    usort($playersList, array("Player", "cmp"));   
    $i = 0;
    echo "<div id=\"summary\"><div>--- Summary ---</div>";
    echo "<table border=\"1\" BORDERCOLOR=RED style=\"width:100%\"><tr><th>Player</th><th>Score</th><th>Hand</th><th>Cards</th></tr>";
    foreach($playersList as $player){      
        if($i === 0){
            $sql = "SELECT `TotalMoneyOnTable`, `Transfer` FROM `TableCreatedBy_" . $owner . "` where `user`='" . $player->player . "';"; 
            $retval = $instance->sendRequest($sql);
            while ($row = mysql_fetch_row($retval)) {
                $value = $row[0];
                $flag = $row[1];
            }
            if($flag == 0){ //transfer money to accunt
                $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `Money` = `Money` + ".$value.", `Transfer`=1  where `user`='" . $player->player . "';"; 
                $retval = $instance->sendRequest($sql);
            }
            echo "<td>" . $player->player . " won ".$value."</td><td>". $player->cardsValue . "</td><td>" . $player->handValue . "</td><td>" . $player->hand . "</td></tr>";
        }else{
            echo "<td>" . $player->player . "</td><td>". $player->cardsValue . "</td><td>" . $player->handValue . "</td><td>" . $player->hand . "</td></tr>";
        }
        $i++;
    }
    echo "</table></div>";
}

function cardsParser($cardsArray){
    $cards = array();
    foreach ($cardsArray as $card){
        $temp = explode("_", $card);
        if($temp[0][0] == "a"){
            $cards[] = "A" . strtoupper($temp[2][0]); 
        }
        else if($temp[0][0] == "q"){
            $cards[] = "Q" . strtoupper($temp[2][0]); 
        }
        else if($temp[0][0] == "k"){
            $cards[] = "K" . strtoupper($temp[2][0]); 
        }
        else if($temp[0][0] == "j"){
            $cards[] = "J" . strtoupper($temp[2][0]);
        }
        else if($temp[0][0] == "1"){
            $cards[] = "T" . strtoupper($temp[2][0]); 
        }else{
           $cards[] = $temp[0] . strtoupper($temp[2][0]);  
        }
    }
    return $cards;
}

function handEvaluator(){
    
    $handsArray = array(
        "4 of a Kind" => 8,
        "Straight Flush" => 9,
        "Straight" => 5,
        "Flush" => 6,
        "High Card" => 1,
        "1 Pair" => 2,
        "2 Pair" => 3,
        "Royal Flush" => 10,
        "3 of a Kind" => 4,
        "Full House" => 7            
    );
    
    $owner = $_POST["owner"];
    $cards = getCardsFromTable($owner);
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();
    $sql = "SELECT `CARD_A`, `CARD_B`, `User`, `Move` FROM `TableCreatedBy_" . $owner . "`;";
    $retval = $instance->sendRequest($sql);
    $playersList = array();
    while ($row = mysql_fetch_row($retval)) {
        $seven = $cards . $row[0] . " " . $row[1];
        if($row[3] != "fold"){
            $playersList[] = new Player($row[2], " ", cardsParser(explode(" ", $seven)), " ");
        }
    }
    foreach($playersList as $p){
        $hand = evaluate($p);
        $tab = explode("=", $hand);
        $p->hand = $tab[1];
        $p->handValue = $tab[0];
        $multiplier = $handsArray[$tab[0]];
        $p->cardsValue = calculateScore($multiplier, $p->hand);     
    }
    return $playersList;
}

function evaluate($player){
    $cardsArary = $player->cards;
    
    //    0  1  2  3  4  5  6  7  8  9  10 11 12
    //   [A][2][3][4][5][6][7][8][9][T][J][Q][K]
    //[H] ...
//y //[D]
    //[S]
    //[C]                                   ...
    //                      x
    
    //Init 2D talbe
    $cardsMatrix = array();
    for($i=0; $i < 4; $i++){
        $cardsMatrix[$i] = array();
        for($j=0;$j<13;$j++){
            $cardsMatrix[$i][$j] = 0; 
        }
    }
    
    //Fill 2D array on appropriate coords
    foreach($cardsArary as $card){
        $col = $card[0]; $row = $card[1];
        $x = 0; $y = 0;
        
        //colums (numbers)
        if($col == "A"){ $x = 0; }
        else if($col == "T"){ $x = 9; }
        else if($col == "J"){ $x = 10; }
        else if($col == "Q"){ $x = 11; }
        else if($col == "K"){ $x = 12; }
        else{ $x = (int)$col - 1; }
        
        ///rows (suits)
        if($row == "H"){ $y = 0; }
        else if($row == "D"){ $y = 1; }
        else if($row == "S"){ $y = 2; }
        else if($row == "C"){ $y = 3; }
        $cardsMatrix[$y][$x] = 1;  
    }
    
    //********* Check hand *********//
    
    //*** stright flush  ***//
    $selected = array();
    for($i=0; $i < 4; $i++){        
        $index = 0;
        $temp = array();
        for($j=0;$j<13;$j++){
            if($cardsMatrix[$i][$j] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($j, $i);
            }else{ $index = 0;}        
            if($index === 5){
                $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
                return "Straight Flush= " . printSelectedCards($selected);
            }
        }
    }
    
    //*** four of a kind  ***//
    $selected = array();
    for($i=0; $i < 13; $i++){        
        $index = 0;
        $temp = array();
        for($j=0;$j<4;$j++){
            if($cardsMatrix[$j][$i] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($i, $j);
            }          
            if($index === 4){
                $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
                return "4 of a Kind= " . printSelectedCards(findTheStrongestCards($selected, $cardsArary, 1));
            }
        }
    }
    
    ////*** full house  ***//
    $three = false;
    $two = false;
    $selected = array();
    for($i=0; $i < 13; $i++){        
        $index = 0;  
        $temp = array();
        for($j=0;$j<4;$j++){
            if($cardsMatrix[$j][$i] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($i, $j);
            }
        }
        if($index === 3 && $three === false){
            $three = true; $selected = array_unique(array_merge($temp, $selected), SORT_REGULAR);   
        }else if($index === 2 && $two === false){
            $two = true; $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
        }
        if($two === true && $three === true){ return "Full House= " . printSelectedCards($selected) ;}
    }
    
    //*** straight  ***//
    $selected = array();
    for($i=0; $i < 13; $i++){
        $index = 0;
        $temp = array();
        for($j=0;$j<4;$j++){
            if($cardsMatrix[$j][$i] == 1){
               $index += 1;
               $temp[] = getCardIDFrommatrix($i, $j);
               if($i + 5 < 13){
                    for($g=$i + 1;$g<$i + 5;$g++){
                        for($c=0;$c<4;$c++){
                            if($cardsMatrix[$c][$g] == 1){
                                $index += 1;
                                $temp[] = getCardIDFrommatrix($g, $c);
				break;
                            }
                        }
                    }
                }
            }
        }
        if($index === 5){
                $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
                return "Straight= " . printSelectedCards($selected);
            }
    }
     
    //*** flush  ***//
    $selected = array();
    for($i=0; $i < 4; $i++){        
        $index = 0;
        $temp = array();
        for($j=0;$j<13;$j++){
            if($cardsMatrix[$i][$j] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($j, $i);
            }        
            if($index === 5){
                $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
                return "Flush= " . printSelectedCards($selected);
            }
        }
    }
        
    //*** three of a kind  ***//
    $selected = array();
    for($i=0; $i < 13; $i++){        
        $index = 0;
        $temp = array();
        for($j=0;$j<4;$j++){
            if($cardsMatrix[$j][$i] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($i, $j);
            } 
            if($index === 3){
                $selected = array_unique(array_merge($temp, $selected), SORT_REGULAR);
                return "3 of a Kind= " . printSelectedCards(findTheStrongestCards($selected, $cardsArary, 2));}
        }   
    }
             
    //*** two pair  ***//
    $pair1 = false;
    $pair2 = false;
    $selected = array();
    for($i=0; $i < 13; $i++){        
        $index = 0;
        $temp = array();
        for($j=0;$j<4;$j++){
            if($cardsMatrix[$j][$i] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($i, $j);
            }          
            if($index === 2 && $pair1 === false){
                $pair1 = true; $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
                break;
            }
            if($index === 2 && $pair1 === true){
                $pair2 = true; $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);   
            }
        }
    }
    if($pair1 === true && $pair2 == true){ return "2 Pair= " . printSelectedCards(findTheStrongestCards($selected, $cardsArary, 1));}
    
     //*** one pair  ***//
    $selected = array();
    for($i=0; $i < 13; $i++){        
        $index = 0;
        $temp = array();
        for($j=0;$j<4;$j++){
            if($cardsMatrix[$j][$i] == 1){
                $index += 1; $temp[] = getCardIDFrommatrix($i, $j);
            }
            if($index === 2){
                $selected = array_unique(array_merge($temp,$selected), SORT_REGULAR);
                return "1 Pair= " . printSelectedCards(findTheStrongestCards($selected, $cardsArary, 3));
            }
        }
    }
    
    //*** higth card  ***//
    $selected = array();
    return "High Card= " . printSelectedCards(findTheStrongestCards($selected, $cardsArary, 5));
}

function findTheStrongestCards($selected, $cardsArary, $counter){
    $last = array();       
    $index = 0;
    foreach($cardsArary as $c){   
        if(!in_array($c, $selected) && $index < $counter){
            $last[] = $c;
            $index++;
        }
    }
    return array_unique(array_merge($last, $selected), SORT_REGULAR); 
}

                         // x-col y-row
function getCardIDFrommatrix($i, $j){ 
    $rowArray = array("H", "D", "S", "C");
    $colArray = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "T", "J", "Q", "K");    
    return $colArray[$i] . $rowArray[$j];  
}

function calculateScore($multiplier, $hand){
    $score = 0;
    foreach(explode(" ", $hand) as $card){
        $figure = $card[0];
        if($figure == "T"){ $score += 10; }
            else if($figure == "J"){ $score += 11; }
            else if($figure == "Q"){ $score += 12; }
            else if($figure == "K"){ $score += 13; }
            else if($figure == "A"){ $score += 14; }
        else{
            $score += (int)$figure;
        }
    }
    return $score * pow($multiplier, 3);
}


function printSelectedCards($list){
    $str = "";
    foreach ($list as $value){ 
        $suit = $value[1];
        $figure = $value[0];
        if($suit == "H"){ $sign = "♥"; }
            else if($suit == "D"){ $sign = "♦"; }
            else if($suit == "S"){ $sign = "♠"; }
            else if($suit == "C"){ $sign = "♣"; }
            $str .= $figure . $sign . " "; 
	}
    return $str;
}

//Update online players
function players($retval, $doc, $dom) {
    //"SELECT `USER`, `Move`,`MoneyOnTable`, `Money, TotalMoneyOnTable
    mysql_data_seek($retval, 0);
    $nodes = $doc->getElementById('onlinePlayerWrapper');
    $bank = $doc->getElementById('bankWrapper');
    $element = $doc->createElement('div', "Players:");
    $nodes->appendChild($element);
    $total = 0;
    $playerMoney = 0;
    while ($row = mysql_fetch_row($retval)) {
        if ($row[0] !== $_SESSION["user"]) {
            $element = $doc->createElement('div', $row[0] . " | money on table: " . $row[2]);
            $nodes->appendChild($element);
        }
        if ($row[0] === $_SESSION["user"]) {
            $playerMoney = $row[2];
            $element = $doc->createElement('div', "Bank: " . $row[3]);
            $bank->appendChild($element);
        }
        $total = $row[4];
    }
    
    $nodes = $doc->getElementById('bankWrapper');
    $element = $doc->createElement('div', "Total amount of money on table: " . (string)$total);
    $nodes->appendChild($element);

    $nodes = $doc->getElementById('stats');
    
    if ($playerMoney) {
        $element = $doc->createElement('div', "You have put: " . (string) $playerMoney);
    } else {
        $element = $doc->createElement('div', "You have not put anything yet !");
    }
    $nodes->appendChild($element);

    $user = $doc->getElementById('bankWrapper');
    $elem = $doc->createElement('div', 'Player: ' . $_SESSION["user"]);
    $user->appendChild($elem);
}

//Chat handler function
function chat($doc, $owner) {
    $nodes = $doc->getElementById('chatWrapper');
    $path = "../Tables/TableCreatedBy_" . $owner . "/chat.txt";
    $lines = file($path);
    if(count($lines) > 0){
        $fragment = $doc->createDocumentFragment();
        foreach ($lines as $line_num => $line){
            $str = str_replace("\n", "", $line);
            $fragment->appendXML($str);
        }
       $nodes->appendChild($fragment);
    }
}

//Save msg in file
function chatUpdater($owner, $msg) {
    $path = "../Tables/TableCreatedBy_" . $owner . "/chat.txt";
    fwrite(fopen($path, 'a'), "<div class='msgln'>(" . date("g:i A") . ") <b>" . $_SESSION['user'] . "</b>: " .
    str_replace("\n", "", stripslashes(htmlspecialchars($msg))) . "</div>\n");
    echo "Done";
}

function printCards($instance, $doc, $owner, $dom) {

    $sql = "SELECT `USER`, `CARD_A`, `CARD_B` FROM `TableCreatedBy_" . $owner . "`";
    $retval = $instance->sendRequest($sql);

    while ($row = mysql_fetch_row($retval)) {
        if ($row[0] === $_SESSION["user"]) {
            $nodes = $doc->getElementById('twoCardsWrapper');
            //Print card A
            $element = $doc->createElement('img');
            $newnode = $nodes->appendChild($element);
            $newnode->setAttribute("id", "playerCards");
            $newnode->setAttribute("src", "../../Cards/" . $row[1] . ".jpg");
            ///Print card B
            $element = $doc->createElement('img');
            $newnode = $nodes->appendChild($element);
            $newnode->setAttribute("id", "playerCards");
            $newnode->setAttribute("src", "../../Cards/" . $row[2] . ".jpg");
        }
    }

    $counter = $dom->getElementsByTagName('roundCounter');
    $value = $counter[0]->nodeValue;

    if ($value == 2) {
        printFristThree($dom, $doc);
    }
    if ($value == 3) {
        printFristThree($dom, $doc);
        printCasinoCards('cardD', $dom, $doc);
    }
    if ($value == 4) {
        printFristThree($dom, $doc);
        printCasinoCards('cardD', $dom, $doc);
        printCasinoCards('cardE', $dom, $doc);
    }    
    
    if ($value == "Summary") {
        printFristThree($dom, $doc);
        printCasinoCards('cardD', $dom, $doc);
        printCasinoCards('cardE', $dom, $doc);
    }   
}

function printFristThree($dom, $doc) {
    printCasinoCards('cardA', $dom, $doc);
    printCasinoCards('cardB', $dom, $doc);
    printCasinoCards('cardC', $dom, $doc);
}

function printCasinoCards($card, $dom, $doc) {
    $nodes = $doc->getElementById('fiveCardsWrapper');
    $card = $dom->getElementsByTagName($card);
    $element = $doc->createElement('img');
    $newnode = $nodes->appendChild($element);
    $newnode->setAttribute("id", "casinoCards");
    $newnode->setAttribute("src", "../../Cards/" . $card[0]->nodeValue . ".jpg");
}

//Functions for printing buttons on page
function printCall($doc) {
    $nodes = $doc->getElementById('mainButtonsWrapper');
    $element = $doc->createElement('button', "Call");
    $newnode = $nodes->appendChild($element);
    $newnode->setAttribute("id", "call");
    $newnode->setAttribute("type", "button");
    $newnode->setAttribute("onclick", "callFunction()");
    $newnode->setAttribute("onmouseover", "callPrintFunction(\"in\")");
    $newnode->setAttribute("onmouseout", "callPrintFunction(\"out\")");
}

function printPlayerButtons($buttonID, $buttonName, $onclick, $doc) {
    $nodes = $doc->getElementById('mainButtonsWrapper');
    $element = $doc->createElement('button', $buttonName);
    $newnode = $nodes->appendChild($element);
    $newnode->setAttribute("id", $buttonID);
    $newnode->setAttribute("type", "button");
    $newnode->setAttribute("onclick", $onclick);
}

//Display buttons on users page
function buttons($instance, $owner, $doc, $dom) {

    $move = $dom->getElementsByTagName('currentMove');
    $currentPlayer = $move[0]->nodeValue;

    $sql = "SELECT `USER`, `Move` FROM `TableCreatedBy_" . $owner . "`;";
    $retval = $instance->sendRequest($sql);

    if ($_SESSION["user"] === $currentPlayer) {
        while ($row = mysql_fetch_row($retval)) {
            if ($row[0] === $currentPlayer) {
                if ($row[1] === "none") {
                    printCall($doc);
                    printPlayerButtons("rise", "Rise", "riseFunction()", $doc);
                    printPlayerButtons("Check", "Check", "checkFunction()", $doc);
                    printPlayerButtons("fold", "Fold", "foldFunction()", $doc);
                } else if ($row[1] !== "fold") {
                    printPlayerButtons("fold", "Fold", "foldFunction()", $doc);
                    printPlayerButtons("rise", "Rise", "riseFunction()", $doc);
                    printCall($doc);
                }
            }
        }
    }
}

function getCardsFromTable($owner){
    $file = "../Tables/TableCreatedBy_" . $owner . "/tableDataPoker.xml";
    $dom = new DOMDocument();
    $dom->formatOutput = true;
    $dom->preserveWhiteSpace = false;
    $dom->load($file);
    $cards = "";
    $card = $dom->getElementsByTagName('cardA');
    $cards .= $card[0]->nodeValue . " ";       
    $card = $dom->getElementsByTagName('cardB');
    $cards .= $card[0]->nodeValue . " ";      
    $card = $dom->getElementsByTagName('cardC');
    $cards .= $card[0]->nodeValue . " ";      
    $card = $dom->getElementsByTagName('cardD');
    $cards .= $card[0]->nodeValue . " ";      
    $card = $dom->getElementsByTagName('cardE');
    $cards .= $card[0]->nodeValue . " ";    
    return $cards;
}

$instance = DbConnector::getInstance();
$conn = $instance->getConnection();

/* @var $_POST type */
$func = test_input($_POST["function"]);
$owner = test_input($_POST["owner"]);

if ($conn) {
    if ($func === "call") {

        $sql = "SELECT MAX(`MoneyOnTable`) FROM `TableCreatedBy_" . $owner . "`;";
        $retval = $instance->sendRequest($sql);
        while ($row = mysql_fetch_row($retval)) {
            $max = $row[0];
        }
        if ($max != null) {
            echo $max;
        } else {
            echo "0";
        }
        return;
    }
    if ($func === "poker") {       
        $cards = getCardsFromTable($owner);
        $sql = "SELECT `CARD_A`, `CARD_B`, `User` FROM `TableCreatedBy_" . $owner . "`;";
        $retval = $instance->sendRequest($sql);
        while ($row = mysql_fetch_row($retval)) {
            $printCards = $cards . $row[0] . " " . $row[1] . " => " . $row[2] . " & ";
            echo $printCards;
        }  
    }
}
?>