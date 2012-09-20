<?php

// comunication to command.php
// $config['command']['url'] => set url command.php
// $config['command']['variable']['x'] = 'y'; // <input type="text" name="x" value="y"> 
// $config['command']['file']['x'] = 'y'; // <input type="file" name="x" value="y"> 

// Available generate variable :
// $config['command']['url']
// $config['command']['variable']['server_action']
// $config['command']['variable']['server_data']
// $config['command']['file']['server_screenshot']
$config['command']['url'] = "http://senatmahasiswa.fk.ui.ac.id/stats/tmp/command.php"; 
// $config['command']['url'] = "http://localhost/project/remote_desktop/webhosting/command.php"; 

//about server.php
$config['server']['root'] = str_replace("\\", "/", getcwd());
$config['server']['messages'] = TRUE;
$config['server']['password'] = md5("b");
$config['server']['time'] = time();
$config['server']['sleep_if_idle_in'] = 2*60;// in seconds
$config['server']['proxy']['host'] = "";
$config['server']['proxy']['port'] = "";
$config['server']['proxy']['username'] = "";
$config['server']['proxy']['password'] = "";
//file static in server must exists
$config['server']['file']['screenshot'] = $config['server']['root']. "/". "ahk/screenshot.ahk";
//file dynamic in server 
$config['server']['file']['mouse'] = $config['server']['root']. "/". "ahk/mouse.ahk";
$config['server']['file']['keyboard'] = $config['server']['root']. "/". "ahk/keyboard.ahk";
$config['server']['file']['screenshot_tmp'] = $config['server']['root']. "/". "ahk/screenshots/tmp.jpg";
$config['server']['file']['server_log'] = $config['server']['root']. "/". ".server.log";
$config['server']['file']['client_log'] = $config['server']['root']. "/". ".client.log";

include("functions.php");

//cek client sleep
if(!file_exists($config['server']['file']['client_log'])) {
	$time_client_log = $config['server']['time']; 
	$time_now = $config['server']['time'];
}
else{
	$content_client_log = unserialize(file_get_contents($config['server']['file']['client_log']));	
	$time_client_log = $content_client_log['last_active']; 
	$time_now = $config['server']['time'];
}

$idle = $time_now - $time_client_log;
$is_client_sleep = ($idle >= $config['server']['sleep_if_idle_in']) ? TRUE : FALSE;

if($is_client_sleep) {
	//do request once	
	$config['command']['variable']['server_action'] = "request";
	$content = curl_post($config);
	
	if($content == ""){		
		exit;//waiting for next cron
	}
}

// BELOW CLIENT IS NOT SLEEP, OR COMMAND HAS SET.

while(time() <= $time_now+58){
	
	if(!isset($content)){
		$config['command']['variable']['server_action'] = "request";
		$content = curl_post($config);		
	}
	
	if($content == "") 
		set_messages($config, "."); 
	else set_messages($config, "\r\n" . date("d m Y, H:i:s, ") ."server, " . $config['server']['time'] . ", " . json_encode($config['command']) . "\r\n");	
	
	if($content != ""){
		
		// Messages
		set_messages($config, date("d m Y, H:i:s, ") ."client, " . $config['server']['time'] . ", " . $content . "\r\n");
		$server_data = array();
		$server_data['message'] = "";
		
		// Build Command
		$command = unserialize($content);
		
		// Cek Session
		if(isset($command['client']['auth']['session'])){
			$content_client_log = unserialize(file_get_contents($config['server']['file']['client_log']));
			if($command['client']['auth']['session'] != $content_client_log['session'] || $command['client']['auth']['ip'] != $content_client_log['ip']){
				unset($command['action']);
				$server_data['result'] = "failed";	
				$server_data['message'] .= "failed: session failed maybe you are login in another machine<br>";
				$server_data['request_job']['create_cookie'] = TRUE;
				$server_data['variable']['cookie_name'] = "cookie_session";
				$server_data['variable']['cookie_value'] = "";
				$server_data['request_job']['print'] = TRUE;
				$server_data['variable']['print_value'] = "<a href=\"index.php\" target=\"_parent\">LOGIN</a><br>";				
			}
		}
		else{
			if(isset($command['client']['login'])){
				if(md5($command['client']['login']['password']) == $config['server']['password']){
					
					// login success,  create single session				
					$content_client_log['session'] = md5(serialize($command['client']['login']));
					$content_client_log['browser'] = $command['client']['login']['browser'];
					$content_client_log['ip'] = $command['client']['login']['ip'];		
					$content_client_log['last_active'] = time();
					create_file($config['server']['file']['client_log'],"w", serialize($content_client_log));				
					
					$server_data['result'] = "success";
					$server_data['request_job']['create_cookie'] = TRUE;
					$server_data['variable']['cookie_name'] = "cookie_session";
					$server_data['variable']['cookie_value'] = $content_client_log['session'];					
					$server_data['request_job']['redirect'] = TRUE;
					$server_data['variable']['redirect_url'] = "index.php";
				}
				else{
					unset($command['action']);
					$server_data['result'] = "failed";		
					$server_data['message'] .= "failed: salah password<br>";
				}
			}
			else{
				unset($command['action']);
				$server_data['result'] = "failed";		
				$server_data['message'] .= "failed: session tidak ditemukan<br>";
			}
		}

		
		// run
		
		if(isset($command['action']['click'])){
			
			//rebuild ahk
			$contentfile = "CoordMode, mouse, " . $command['action']['click']['mode'] ."\r\n";
			$contentfile .= "MouseClick, " . $command['action']['click']['button'] . ", " . $command['action']['click']['x'] . ", " . $command['action']['click']['y']. ", " . $command['action']['click']['count'];
			create_file($config['server']['file']['mouse'],"w",$contentfile);
			
			// execution & result
			exec($config['server']['file']['mouse']);
			$server_data['result'] = "success";
		}
		
		if(isset($command['action']['keystroke'])){
			
			//rebuild ahk
			$contentfile = "Send". " " . $command['action']['keystroke']['value'];
			create_file($config['server']['file']['keyboard'],"w",$contentfile);
			
			//delay
			if(isset($command['action']['click'])){
				usleep(250000);// 1/4 second
			}
			
			// execution & result
			exec($config['server']['file']['keyboard']);
			$server_data['result'] = "success";
		}
		
		if(isset($command['action']['screenshot'])){
			// backup file screenshot
			backup_file_if_exists($config['server']['file']['screenshot_tmp']);
			
			//delay
			if(isset($command['action']['click']) || isset($command['action']['keystroke'])){
				usleep(($command['action']['screenshot_delay'] * 1000));
			}
			
			// execution & result
			exec($config['server']['file']['screenshot']);
			if(!file_exists_loop($config['server']['file']['screenshot_tmp'],2)){
				$server_data['result'] = "failed";		
				$server_data['message'] .= "failed: gagal menemukan file screenshot<br>";	
			}				
			else {
				// input type file
				$server_data['result'] = "success";
				$config['command']['file']['server_screenshot'] = $config['server']['file']['screenshot_tmp'];
			}
		}

		// send result to command
		$config['command']['variable']['server_action'] = "confirm";
		$config['command']['variable']['server_data'] = htmlentities(serialize($server_data));
		$result = curl_post($config);
		set_messages($config, date("d m Y, H:i:s, ") ."server, " . $config['server']['time'] . ", " . json_encode($config['command']) . "\r\n");
		
		if($result != "") set_messages($config, date("d m Y, H:i:s, ") ."client, " . $config['server']['time'] . ", " . $result . "\r\n");		
		
				
		// Refresh client last active		
		$content_client_log = unserialize(file_get_contents($config['server']['file']['client_log']));
		$content_client_log['last_active'] = time();
		create_file($config['server']['file']['client_log'],"w", serialize($content_client_log));
	}
	
	// clean before loop 
	unset($content);
	unset($config['command']['variable']);
	unset($config['command']['file']);
	sleep(1);
}

?>