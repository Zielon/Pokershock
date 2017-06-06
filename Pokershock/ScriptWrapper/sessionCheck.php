<?php
    session_start();
    if(empty($_SESSION['user'])){
         echo "Expired";
    }else {
         echo "Active";
    };
?>