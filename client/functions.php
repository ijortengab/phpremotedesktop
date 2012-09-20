<?php

function create_file($path,$mode,$content){
	$fopen = fopen($path, $mode);
	$fwrite = fwrite($fopen, $content);
	fclose($fopen);
	if($fopen == false || $fwrite == false) return false; else return true;
}

function backup_file_if_exists($path){
	
	$bak = dirname($path) . "/" . pathinfo($path, PATHINFO_FILENAME) . "~." . pathinfo($path, PATHINFO_EXTENSION);
	
	if(file_exists($path)) {
		if(file_exists($bak)) unlink($bak);
		rename($path,$bak);
	}
}

function file_exists_loop($file, $time){
	//$time in second, but we use usleep in 1/4 second
	$time *= 4;
	$found = false;
	for($i=0; $i<$time; $i++){	 
		if(file_exists($file)){
			$found = true;
			break;
		}
		usleep(250000);// 1/4 second
	}
	return $found;
}

function create_url($pathfilename){
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');	
	$extra = basename($pathfilename);
	$ahref = "http://$host$uri/$extra";
	return $ahref;
}

?>
