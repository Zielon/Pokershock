<?php
/*
 * Script to delete users which is logging out and 
 * clean log file in case if no user left
 */
// Headers
    include('dbConnector.php');
    
    //delete user from every table where user is playing 
    function deleteFromTables($instance, $user){
         $temp = $_SESSION["activTables"];
         $tables = explode("&", $temp);
         for($i = 0; $i < count($tables); $i++){
            $sql = "DELETE FROM `".$tables[$i]."` WHERE `USER` = '".$user."';";
            $retval = $instance->sendRequest($sql);
        }  
    }
    
    $path = '../MainMenu/logs.txt';
    
    session_start();
    
    $login = $_SESSION['user'];
    $userID = $_SESSION['ID'];
     
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();
    
    $countUsers = -1;
    
    if($conn){
        $sql = "DELETE FROM `ActiveUsers` WHERE `ActiveUsers`.`ID` = $userID;";
        $retval = $instance->sendRequest($sql);
        
        $sql = "SELECT count(*) AS Total FROM `ActiveUsers`;";
        $retval = $instance->sendRequest($sql);

        $data = mysql_fetch_assoc($retval);
        $countUsers = $data['Total'];  
        deleteFromTables($instance, $login);
    }
    

    if($countUsers == 0){
        $fh = fopen($path, 'w');
        fclose($fh);
        
        $foldersToDelete = array();
        $dirPath = "../Tables/";
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $dir) {
           if (strpos($dir,'TableCreatedBy') !== false) {
               $foldersToDelete[] = $dir;
            }
        }

        foreach ($foldersToDelete as $folder) {
            $files = glob($folder . "*", GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    self::deleteDir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($folder);
            $name = explode("/", $folder);
            $sql = "DROP TABLE ".$name[2].";";
            $retval = $instance->sendRequest($sql);
        } 
    }
    session_destroy();
    session_unset();
    header('Location: ../index.html'); 
?>
