<?php
    include('dbConnector.php');
    session_start();

    $user = test_input($_POST["user"]);
    $msg = test_input($_POST["msg"]);
    $function = test_input($_POST["function"]);
    $state = test_input($_POST["state"]);
    $log = array();
    $path = '../MainMenu/logs.txt';

    switch($function) {

            case('getState'):
                    if (file_exists($path)){
                            $lines = file($path);
                    }
                    $log['state'] = count($lines);
                    break;

            case('send'):
                    fwrite(fopen($path, 'a'), "<div>(".date("H:i:s"). ")<b> @".$_SESSION['user']."</b>:".stripslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $msg))))."<br></div>\n");
                    break;

            case('updateChat'):
                    if (file_exists($path)){
                        $lines = file($path);
                    }
                    $count =  count($lines);
                    $text = array();
                    foreach ($lines as $line_num => $line) {
                        //  if ($line_num >= $count - 14){
                            $text[] = $line = str_replace("\n", "", $line);
                         // }
                    }
                    $log['text'] = $text; 			
                    break;
    }
    echo json_encode($log);
?>