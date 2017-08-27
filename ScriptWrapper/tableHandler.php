<?php
/* Function which is response for handle table
 * Creates new table in SQL database
 * Move and change particular files 
 */
// Headers
    include('dbConnector.php');
    session_start();
    
    
    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();

    if($conn)
    {
        //Table init SQL commad
        $sql =  "CREATE TABLE `TableCreatedBy_" . $_SESSION['user'] ."` (
                    `ID` INT NOT NULL AUTO_INCREMENT ,
                    `USER` VARCHAR( 30 ) NOT NULL ,
                    `MONEY` INT NOT NULL ,
                    `CARD_A` VARCHAR(25),
                    `CARD_B` VARCHAR(25),
                    `Move` VARCHAR(10) DEFAULT 'none',
                    `MoneyOnTable` INT DEFAULT 0,
                    `TotalMoneyOnTable` INT DEFAULT 0, 
                    `IsReady` BOOLEAN,
                    `Transfer` BOOLEAN,
                PRIMARY KEY ( `id` )
                );";
                
        $retval = $instance->sendRequest($sql);
        
        if (!file_exists("../Tables/TableCreatedBy_" . $_SESSION['user'])) 
        {
            $path = "../Tables/TableCreatedBy_" . $_SESSION['user'];
            mkdir($path, 0777, true);  
            $file = fopen($path . "/chat.txt", "w");
            
            $template = "../Tables/historyTemplate.txt";
            copy($template, $path . "/cardsHistory.txt");
                                 
            $template = "../Tables/tableData.xml";
            copy($template, $path . "/tableDataPoker.xml");
            
            $template = "../Tables/tableTemplate.html";
            copy($template, $path . "/GameTable.html");
            
            $path_to_file = $path . "/GameTable.html";
            $file_contents = file_get_contents($path_to_file);
            $file_contents = str_replace("<h3 id=\"owner\">Table</h3>","<h3 id=\"owner\">Table owner " .$_SESSION['user']. "</h3>",$file_contents);
            file_put_contents($path_to_file, $file_contents);
            
            $template = "../Tables/waitingRoom.html";
            copy($template, $path . "/Room.html");
            
            $path_to_file = $path . "/Room.html";
            $file_contents = file_get_contents($path_to_file);
            $file_contents = str_replace("<h2 id=\"owner\">Onwer</h2>",
                    "<h2 id=\"owner\">Room created by " .$_SESSION['user']. "</h2>",$file_contents);
            file_put_contents($path_to_file, $file_contents);

            $template = "../Tables/timer.php";
            copy($template, $path . "/timer.php");
            
            $time = date('H:i:s', time());
            $tmp = "\$startTime = \"" . $time . "\";";
            $path_to_file = $path . "/timer.php";
            $file_contents = file_get_contents($path_to_file);
            
            $file_contents = str_replace("\$startTime = 0;", $tmp, $file_contents);
            file_put_contents($path_to_file, $file_contents);
           
            $sql = "UPDATE `ActiveUsers` SET `Table`= 1 WHERE `User` = '" . $_SESSION['user'] . "';";          
            $retval = $instance->sendRequest($sql);  
            
            echo "True " . $_SESSION['user'];
        }
    }
?>