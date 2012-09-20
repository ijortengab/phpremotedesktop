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

function check_running(){
	// file belum lengkap, 
	// bila file tidak 
	// tambahkan, bila selama 10 detik file masih running, maka ada error. set ke 0
	$file = ".is_server_running";
	$max_idle = "10"; // seconds
	$idle = time() - filemtime($file);
	// echo filemtime($file);
	// echo "\r\n<br>";
	// echo time();echo "\r\n<br>";
	
	if(!file_exists($file)) create_file(getcwd() . '/' . $file, "w", "1");
	
	elseif($idle > $max_idle){ // if found error di tengah jalan sehingga tidak sempat diset kembali sebagai 0
		echo "found error 004";
		create_file(getcwd() . '/'. $file, "w", "0");
		exit;
	}
	
	else{
	
		if(file_get_contents($file)) exit;
		
		else create_file(getcwd() . '/' . $file, "w", "1");
	
	}
	
	
}

function check_requirement(){
//cek kebutuhan akan curl dan exec, juga kebutuhan akan autohotkey
}

function end_running(){
	$file = ".is_server_running";
	create_file(getcwd() . '/' . $file, "w", "0");
}

function set_messages($config, $msg){
	if($config['server']['messages']){
		create_file($config['server']['file']['server_log'], "a" , $msg);
	}
}

/**
 * file_get_contents with automated check proxy
 * 
 */
function file_get_contents_advanced($url, $form = NULL, $proxy = NULL){

	$aContext = array();
	$aContext['http']['timeout'] = 3;
	$aContext['http']['request_fulluri'] = true;
	$aContext['http']['header'] = "";
	
	if(!is_null($form)){		
		if($form['method'] == "get"){
			//rapihkan url
			if(preg_match("/\&$/",$url)) $url = substr($url, 0, -1);
			if(preg_match("/\?/",$url)) $variable = "&"; else $variable = "?";
			$http_build_query = count($form['data']) ? http_build_query($form['data']) : "";
			$url = $url.$variable.$http_build_query ;
		}
		elseif($form['method'] == "post"){
			// http://www.php.net/manual/es/function.file-get-contents.php#108309
			// http://www.php.net/manual/es/function.file-get-contents.php#102575
			$aContext['http']['method'] = "POST";
			$data_url = http_build_query($form['data']);
			$data_len = strlen ($data_url);
			$aContext['http']['header'] .= "Connection: close\r\n";
			$aContext['http']['header'] .= "Content-Length: $data_len\r\n";
			$aContext['http']['content'] = $data_url;
		}
	}
	
	
	if(!is_null($proxy)){
		if(isset($proxy['host'])) {
			$uri = 'tcp://'. $proxy['host'];
			if(isset($proxy['port'])) $uri = $uri . ':' . $proxy['port'];
			
			$aContext['http']['proxy'] = $uri;
			
			if(isset($proxy['username'])) $auth = $proxy['username'];
			if(isset($proxy['password'])) $auth = $auth . ":" . $proxy['password'];
			if(isset($auth)) {
			
				$aContext['http']['header'] .= "Proxy-Authorization: Basic ". base64_encode($auth) . "\r\n";				
				
			}
			
			
		}
	}
	// print_r($aContext);exit;
	
	$result =  file_get_contents($url, False, stream_context_create($aContext));
	
	if (!$result) return file_get_contents($url); else return $result;

}

/**
 * file_exists for string and array
 * 
 */
 
function file_exists_advanced($files){
	
	if(is_string($files)) $result = file_exists($files);
	elseif(is_array($files)){
		$result = true;//all file exists
		foreach($files as $file){
			if(!file_exists($file)){$result = false; /* echo "gak ada"; */ break;}
		}
	}
	else $result = false;
	return $result;
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

/**
 * curl_set()
 * sending curl, get content the web
 */
 
function curl_set($url, $form = NULL, $proxy = NULL){

// $url="string";
// $method="string";
// $data=array();
// $proxy=array();
// $proxy['host']="string";
// $proxy['port']="string";
// $proxy['username']="string";
// $proxy['password']="string";


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT,3);
	
	if(!is_null($form)){		
		if($form['method'] == "get"){
			//rapihkan url
			if(preg_match("/\&$/",$url)) $url = substr($url, 0, -1);
			if(preg_match("/\?/",$url)) $variable = "&"; else $variable = "?";
			$http_build_query = count($form['data']) ? http_build_query($form['data']) : "";
			$url = $url.$variable.$http_build_query ;
		}
		elseif($form['method'] == "post"){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $form['data']);
		}
	}
	if(!is_null($proxy)){
		if(isset($proxy['host'])) {
			curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
			if(isset($proxy['port'])) curl_setopt($ch, CURLOPT_PROXYPORT, 8080);			
			if(isset($proxy['username'])) $auth = $proxy['username'];
			if(isset($proxy['password'])) $auth = $auth . ":" . $proxy['password'];
			if(isset($auth)) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); // http://stackoverflow.com/questions/6244578/curl-post-file-behind-a-proxy-returns-error
		}		
	}	
	curl_setopt($ch, CURLOPT_URL, $url);	
	$result = curl_exec($ch);
	curl_close($ch);	
	return $result;
}

function curl_post($config){

	//konfigurasi
	$url = $config['command']['url'];
	$form = array();
	$form['method'] = "post";
	
	//input value
	if(isset($config['command']['variable'])){
		foreach($config['command']['variable'] as $id => $value){
			$form['data'][$id] = $value;
		}
	}
	
	//input file
	if(isset($config['command']['file'])){
		foreach($config['command']['file'] as $id => $value){
			$form['data'][$id] = '@'. $value;
		}
	}

	// $form['data']['custom'] = "custom";	
	
	if($config['server']['proxy']['host'] != ''){
		$proxy=array();
		$proxy['host']=$config['server']['proxy']['host'];
		if($config['server']['proxy']['port'] != '') $proxy['port']=$config['server']['proxy']['port'];
		if($config['server']['proxy']['username'] != '') $proxy['username']=$config['server']['proxy']['username'];
		if($config['server']['proxy']['password'] != '') $proxy['password']=$config['server']['proxy']['password'];
	}
	
	return curl_set($url, $form, $proxy);
}

function curl_upload($config){
	// fungsi belum lengkap, tidak mendukung server behind proxy
	$url = $config['client']['url'];
	$file = $config['server']['file']['tmp'];
	$data = array();
	$data['file_image_screenshot'] = '@'. $file;
	
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT,30);
	$data = curl_exec($ch);
	curl_close($ch);	
}


?>
