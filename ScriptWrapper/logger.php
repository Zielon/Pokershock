<?php
/*
 * 
 * 
 */
// Headers
    include('dbConnector.php');
    include("blowfish.class.php");
    session_start();

    $login = $password = "null";
    $login = test_input($_POST["login"]);
    $pass = test_input($_POST["password"]);
    $func = test_input($_POST["function"]);
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnectionMySqli();

    if($func == "check"){
        if(empty($_SESSION['user'])){
            echo "False";
        }else{
            echo "True";
        }
        return;
    }
    
    if($conn)
    {
        if($func == "login"){
            $sql = "SELECT LOGIN, PASSWORD, ID FROM `Users`;";
            $retval = $instance->sendRequest($sql);
            
            while($row = mysql_fetch_row($retval)) {

                $loginDataBase = $row[0];
                $passwordDataBase = $row[1];
                $userID = $row[2];

                $blowfish = new Blowfish("pokershock");
                $decrypted_password = $blowfish->Decrypt($pass);
                $decrypted = str_replace(' ', '', $decrypted_password);
                if( $loginDataBase == $login && $passwordDataBase == $decrypted){

                    $sql = "INSERT INTO `ActiveUsers` (`ID`, `User`, `Table`) VALUES (?, ?, ?);";
                    $retval = $instance->sendRequestWithParams($sql, 'ssi', array($userID, $loginDataBase, 0));

                    $_SESSION['user'] = $login;
                    $_SESSION['ID'] = $userID;

                    echo "TRUE:". $_SESSION['user'];
                    return;
                }
            }
       }
       
    }else{
        echo "ERROR";
    }
?>