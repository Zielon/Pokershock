<?php
/*
 * 
 * 
 */
// Headers
    include('dbConnector.php');
    session_start();
    
    $target_dir = "../UsersAvatars/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

	if(isset($_POST["submit"])){
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false){
                    $uploadOk = 1;
            } else{
                    $uploadOk = 0;
            }
	}
	
	// Check if file already exists
	if (file_exists($target_file)) {
		
            $uploadOk = 0;
	}

	if ($_FILES["fileToUpload"]["size"] > 500000) {
		
            $uploadOk = 0;
	}
	
	// Allow certain file formats
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {

            $uploadOk = 0;
	}
	
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk != 0){
		 
            foreach(glob("../UsersAvatars/" . $_SESSION['user'] . ".*") as $file){ 
                    if (file_exists($file)){
                            unlink($file);
                    }
            }

            move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

            $userFile = $target_dir . $_SESSION['user']. "." . $imageFileType;

            rename($target_file, $userFile);

            header('Location: /MainMenu/menu.php');
		
	}else{
		echo "Sorry, there was an error uploading your file. Try again";
	}
	
?>