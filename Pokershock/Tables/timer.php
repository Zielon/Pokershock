<?php
    //Simple timer
    //    
    // Headers
    include('../../ScriptWrapper/dbConnector.php');
    session_start();
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();

    $tableID = $_POST["owner"];
    $function = $_POST["function"];
    
    
    if($conn)
    {
        if($function === "verify"){
            $sql = "SELECT `IsReady` FROM `TableCreatedBy_".$tableID."`;";
            $retval = $instance->sendRequest($sql);
            $counter = 0;
            
            if(isset($retval)){
                while($row = mysql_fetch_row($retval)) {

                    $flag = $row[0];
                    if($flag == 1){     
                        $counter++;
                    }
                }

                $num_rows = mysql_num_rows($retval);            
                if($num_rows == $counter && $counter > 1){ 
                    echo "Ready";   
                    return;
                }   
            }
        }
        
        $startTime = 0;
        $actualTime = date('H:i:s', time());

        $time1 = new DateTime($startTime);
        $time2 = new DateTime($actualTime);

        $interwal = $time2->diff($time1);
        echo $interwal->format('%i:%s');
    }
?>  