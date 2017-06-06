<?php
    function generateXML(){
        $lines = file('../MainMenu/logs.txt');
        $text = "<?xml version=\"1.0\"?>\n<ChatHistory>\n";
        foreach ($lines as $line) {
            $text .= "\t<SingleMsg>\n";
            $userLine = str_replace("\n", "", strip_tags($line));        
            
            $tab = explode("@", $userLine);
            $newtab = explode(":", $tab[1]); 
            $text .= "\t\t<time>" .  str_replace(" ", "", $tab[0]) . "</time>\n";
            $text .= "\t\t<user>" . $newtab[0] . "</user>\n";
            $text .= "\t\t<msg>" . $newtab[1] . "</msg>\n";
            $text .= "\t</SingleMsg>\n";
        } 
        $text .= "</ChatHistory>";
        $myfile  = fopen("ChatXMLFile.xml", "w");
        fwrite($myfile, $text);
        fclose($myfile);
    }

    function generateJSON(){
        $lines = file('../MainMenu/logs.txt');
        $text = "{\"ChatHistory\":[\n";
        foreach ($lines as $line) {

            $userLine = str_replace("\n", "", strip_tags($line));
            $tab = explode("@", $userLine);
            $newtab = explode(":", $tab[1]);
            $time = str_replace(" ", "", $tab[0]);

            $text .= "\t\t{\"time\":\"$time\", \"user\":\"$newtab[0]\", \"msg\":\"$newtab[1]\"},\n";
        }
        $text = substr_replace($text, "", -2);
        $text .= "\n]}";
        $myfile  = fopen("ChatJSONFile.json", "w");
        fwrite($myfile, $text);
        fclose($myfile);
    }

    function generateTXT(){
        $lines = file('../MainMenu/logs.txt');
        $text = "";
        foreach ($lines as $line) {
            $text .= str_replace("\n", "", strip_tags($line)) . "\n";
        } 
        $myfile  = fopen("ChatTextFile.txt", "w");
        fwrite($myfile, $text);
        fclose($myfile);
    }
    
    function generateHTML(){
        $lines = file('../MainMenu/logs.txt');
               
        $text = "<!DOCTYPE html>\n<html>\n<head>\n<title>Generated File</title>\n</head>\n" . 
                "<body>\n<h3>File was generated on " 
                .date("D M j G:i:s T Y") . 
                "</h3>\n\t<div>Chat history: </div><br>\n";
        
        foreach ($lines as $line) {
            $text .= "\t" . $line;
        } 
        $text .= "</body></html>";
        $myfile  = fopen("ChatHTMLFile.html", "w");
        fwrite($myfile, $text);
        fclose($myfile);
    }

    function DownloadFile($file){
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
        }
    }
    
    $format = $_GET["format"];

    switch ($format) {
        case "XML":
            generateXML();
            DownloadFile("ChatXMLFile.xml");
            break;
         case "TEXT":
            generateTXT();
            DownloadFile("ChatTextFile.txt");
            break;
        case "HTML":
            generateHTML();
            DownloadFile("ChatHTMLFile.html");
            break;
        case "JSON":
            generateJSON();
            DownloadFile("ChatJSONFile.json");
            break;
        default:
            break;
    }
?>