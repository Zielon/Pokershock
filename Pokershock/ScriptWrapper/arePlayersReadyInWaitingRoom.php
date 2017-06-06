<?php
/*
 * 
 * 
 */
// Headers
    include('dbConnector.php');
    session_start();
    
    $tableID = test_input($_POST["tableID"]);
    $function = test_input($_POST["function"]);
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();

    if($conn)
    {
        if($function == "verify"){
            $sql = "SELECT `User`, `IsReady` FROM `TableCreatedBy_".$tableID."`;";
            $retval = $instance->sendRequest($sql);
            $counter = 0;
            $tableMember = false; 
            while($row = mysql_fetch_row($retval)) {
                
                $user = $row[0];
                $flag = $row[1];

                if($flag == 1){     
                    $counter++;
                }
                if($user === $_SESSION["user"]){
                    $tableMember = true;
                }
            }
            
            $num_rows = mysql_num_rows($retval);            
            
            $sql = "SELECT `Bank` FROM `Users` WHERE `login`='".$_SESSION["user"]."'";
            $retval = $instance->sendRequest($sql);
            while($row = mysql_fetch_row($retval)) {
                $money = $row[0];
            }
            
            if($num_rows > 0){
                if($num_rows == $counter && $tableMember === false){
                    echo "GameOn";   
                    return;
                }else if($num_rows == $counter || $tableMember === true){
                    echo "True";  
                    return;
                }
            } 
            echo "False:".$_SESSION["user"].":".$money;
        }
    }

?>
