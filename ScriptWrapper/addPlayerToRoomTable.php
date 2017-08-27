<?php
/*
 * 
 * 
 */
// Headers
    include('dbConnector.php');
    session_start();
    
    function drawCards($owner){
        
        $cardsFile = file('../Tables/cards_names.txt');
        $count = count($cardsFile);
        $cardsArray = array();
        
        $myFile = "../Tables/TableCreatedBy_" . $owner . "/cardsHistory.xml";
        if (file_exists($myFile)) {
                  
            $dom = new DOMDocument();
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;
            $dom->load($myFile);
            
            $i = 1;
            while($i < 3){
                $int = rand(0, 999999) % $count;
                $id = $cardsFile[$int];
                $cardID = explode(".", $id);
                
                $flag = true;
                $root = $dom->getElementsByTagName('card');
                foreach($root as $card) {
                    if($card->nodeValue === $cardID[0]){
                        $flag = false;
                    }
                }
                
                foreach($cardsArray as $selectedCard){
                    if($selectedCard === $cardID[0]){
                        $flag = false;
                    }
                }
                
                if($flag === true){
                    $cardsArray[] = $cardID[0];
                    $node = $dom->createElement("card", $cardID[0]);
                    $root->appendChild($node);
                    $i++;
                }
            }
        }
        
        $dom->save($myFile);
        return $cardsArray;
    }
    
    $post = test_input($_POST["tableID"]);
    $function = test_input($_POST["function"]);
    
    $tableID = "TableCreatedBy_" . $post;
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();
    
    if($conn)
    {
        if( $function == "add"){
            
            $sql = "SELECT * FROM `".$tableID."`";
            $retval = $instance->sendRequest($sql);
            $num_rows = mysql_num_rows($retval);
  
            if($num_rows < 6){   
                $sql = "INSERT INTO `".$tableID."` (`ID`, `USER`, `MONEY`, `CARD_A`, `CARD_B`, `IsReady`) "
                    . "VALUES (NULL, '".$_SESSION['user']."', '0', 'null', 'null', 'False');";   
                $retval = $instance->sendRequest($sql);
                echo "True";  
            }else{
                echo "False";
            }
        } 
    }
?>