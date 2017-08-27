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
    
    $func = test_input($_POST["func"]);
    
    if($conn)
    {
        if($func === "online" ){
            $sql = "SELECT `ID`, `User` FROM `ActiveUsers`;";
            $retval = $instance->sendRequest($sql);
            $num_rows = mysql_num_rows($retval);
            echo $num_rows;
            return;
        }
        
        $sql = "SELECT `ID`, `User` FROM `ActiveUsers` WHERE `Table` = 1;";
        $retval = $instance->sendRequest($sql);
        $output;

        while($row = mysql_fetch_row($retval)) {
            $user = $row[1];
            $tableID = "TableCreatedBy_" . $user;
            $sql = "SELECT * FROM `".$tableID."`";
            $r = $instance->sendRequest($sql);
            if($r){
                $num_rows = mysql_num_rows($r);
                $output .= "<div class=\"tableLinks\" id=\"tableBy-" . $user. 
                        "\" onclick=\"tableRoomJumper(this.id)\"> Table created by " . $user . " -------> Users: ".$num_rows."</div>_"; 
            }
        }
        echo $output;
    }
?>