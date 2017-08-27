<?php
    include('dbConnector.php');
    
    $sql = "SELECT TAB.LOGIN, TAB.BANK, ACTIV.TABLE FROM "
            . "( SELECT LOGIN, BANK FROM `Users` ORDER BY BANK DESC ) as TAB "
            . "LEFT OUTER JOIN `ActiveUsers` as ACTIV on TAB.LOGIN=ACTIV.USER;";

    $instance = DbConnector::getInstance();
    $conn = $instance->getConnection();
    $retval = $instance->sendRequest($sql);

    echo "<?xml version=\"1.0\"?>";
    echo "<players>";  
    while($row = mysql_fetch_row($retval)) {
        $login = $row[0];
        $bank = $row[1];
        $table = $row[2];
        echo "<player>";
        echo "<user>" . $login . "</user>";
        echo "<bank>" . $bank . "</bank>";
        echo "<online>" . $table . "</online>";
        echo "</player>";
    }
    echo "</players>";
?>