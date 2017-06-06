<?php

// Headers
    include('dbConnector.php');
    
    $login = test_input($_POST["login"]);
    $userName = test_input($_POST["user"]);
    $pass1 = test_input($_POST["pass1"]);
    $pass2 = test_input($_POST["pass2"]);
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();
    
    $sql = "SELECT LOGIN FROM `Users`;";
    $retval = $instance->sendRequest($sql);

    $num_rows = mysql_num_rows($retval);
    
    if($num_rows < 30){   
        while($row = mysql_fetch_row($retval)) {
            if($row[0] == $login){
                echo "Flase";
                return;
            }
        }

        if($pass1 === $pass2){

            $sql = "INSERT INTO `Users` (`ID`, `USERNAME`, `LOGIN`, `PASSWORD`, `BANK`) VALUES (NULL, ?, ?, ?, '100000');";
            $retval = $instance->sendRequestWithParams($sql, 'sss', array($userName, $login, $pass1));

            $target_dir = "../UsersAvatars/default.jpg";
            if (copy($target_dir, "../UsersAvatars/". $login . ".jpg")) {
                echo "True";
            }else{
                echo "False";
            }
        }  
    }
?>