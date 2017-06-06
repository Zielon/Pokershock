<?php
/*
 * 
 * 
 */
// Headers
    include('dbConnector.php');
    session_start();
    
    function updateTotalMoney($value, $instance, $owner){
        $sql = "UPDATE `TableCreatedBy_".$owner."` SET `TotalMoneyOnTable`=`TotalMoneyOnTable` + ".$value.";";
        $retval = $instance->sendRequest($sql);
    }
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();
    
    $func = test_input($_POST["function"]);
    $owner = test_input($_POST["owner"]);
    
    if($conn)
    {
        $sql = "SELECT `Money`, `MoneyOnTable` FROM `TableCreatedBy_".$owner."` where `User`='". $_SESSION["user"] . "';";
        $retval = $instance->sendRequest($sql);
        while ($row = mysql_fetch_row($retval)) {
            if($row[0] != null){
                $userMoney = $row[0];
                $putedMoney = $row[1];
            }
        }
        
        if($func === "rise"){
            $money = $_POST["money"];
            
            $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `Move` = 'none' WHERE `Move`!='fold';";  
            $retval = $instance->sendRequest($sql);
            
            if((int)$userMoney - (int)$money >= 0){
                
                $score = (int)$userMoney - ((int)$money - (int)$putedMoney);
                
                $sql = "UPDATE `TableCreatedBy_".$owner."` SET `Move`='rise', `MoneyOnTable`=".$money.", `Money`=".$score." where `User`='". $_SESSION["user"] . "';";
                $retval = $instance->sendRequest($sql);
                updateTotalMoney((int)$money - (int)$putedMoney, $instance, $owner);
                echo "True";
            }else{
                echo "False: You do not have enough money !";
            }
        } 
        
        if($func === "call"){
            
            $sql = "SELECT MAX(`MoneyOnTable`) FROM `TableCreatedBy_".$owner."`;";
            $retval = $instance->sendRequest($sql);
            while ($row = mysql_fetch_row($retval)) {
                if($row[0] != null){
                    $max = $row[0];
                    if((int)$userMoney - (int)$max >= 0){
                        $score = (int)$userMoney - ((int)$max - (int)$putedMoney);
                        $sql = "UPDATE `TableCreatedBy_" . $owner . "` SET `Move`='none' WHERE `Move`='rise' or `Move`='call' or `Move`='check';";  
                        $retval = $instance->sendRequest($sql);

                        $sql = "UPDATE `TableCreatedBy_".$owner."` SET `Move`='call', `MoneyOnTable`=".$max.", `Money`=".$score." where `User`='". $_SESSION["user"] . "';";
                        $retval = $instance->sendRequest($sql);
                        updateTotalMoney((int)$max - (int)$putedMoney, $instance, $owner);
                    }else{
                        echo "False: You do not have enough money !";
                    }  
                }else{
                    echo "No one bet ! You can not call !";
                }  
            }
        }
        
        if($func === "check"){
            
            $sql = "SELECT `USER`, `Move` FROM `TableCreatedBy_" . $owner . "`";
            $retval = $instance->sendRequest($sql);
            $flag = true;
            while ($row = mysql_fetch_row($retval)) {
                if($row[1] === "rise" || $row[1] === "call"){
                    $flag = false;
                }
            }
            if($flag === true){
                $sql = "UPDATE `TableCreatedBy_".$owner."` SET `Move`='check' where `user`='". $_SESSION["user"] . "';";
                $retval = $instance->sendRequest($sql);
                echo "Check !";
            }else{
                echo "You cannot check !";
            }
        }
        if($func === "fold"){
            
            $sql = "UPDATE `TableCreatedBy_".$owner."` SET `Move`='fold' where `user`='". $_SESSION["user"] . "';";
            $r = $instance->sendRequest($sql);
            
            $sql = "UPDATE `TableCreatedBy_".$owner."` SET `Move`='none' WHERE `MOVE`!='fold';";
            $r = $instance->sendRequest($sql);
            echo "Fold !";
        }
    }
?>
