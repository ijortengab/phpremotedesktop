<?php

$config['client']['root'] = str_replace("\\", "/", getcwd());
$config['client']['pathfile']['screenshot'] = $config['client']['root'] . "/" . "screenshot.jpg";
$config['client']['pathfile']['command'] = $config['client']['root'] . "/" . ".command";
$config['client']['pathfile']['confirm'] = $config['client']['root'] . "/" . ".confirm";

// Available generate variable :
// $command['client']['login']['password']
// $command['client']['login']['browser']
// $command['client']['login']['time']
// $command['client']['login']['ip']
// $command['client']['auth']['session']
// $command['client']['auth']['ip']
// $command['action']['click']['mode']
// $command['action']['click']['button']
// $command['action']['click']['count']
// $command['action']['click']['x']
// $command['action']['click']['y']
// $command['action']['keystroke']['value']
// $command['action']['screenshot']
// $command['action']['screenshot_delay']
foreach ($_GET as $key => $val) $$key=$val;
foreach ($_POST as $key => $val) $$key=$val;
foreach ($_COOKIE as $key => $val) $$key=$val;
foreach ($_FILES as $key => $val) $$key=$val;

include("functions.php");


// VERIFICATION & CREATE COMMAND 

// cek session
if(!isset($cookie_session)) {
	if(isset($client_action_password_value)) {			
		$command['client']['login']['password']=$client_action_password_value;
		$command['client']['login']['browser']=$_SERVER['HTTP_USER_AGENT'];
		$command['client']['login']['time']=$_SERVER['REQUEST_TIME'];
		$command['client']['login']['ip']=$_SERVER['REMOTE_ADDR'];	
	}
	else {
		header("Location: " . create_url("index.php"));
		exit;
	}
}
else{
	$command['client']['auth']['session']=$cookie_session;
	$command['client']['auth']['ip']=$_SERVER['REMOTE_ADDR'];
}

if($client_action_click == "click"){
	
	$command['action']['click']['mode'] = ($client_action_click_mode != "") ? $client_action_click_mode : "Screen"; //[Screen|Relative]
	$command['action']['click']['button'] = ($client_action_click_button != "") ? $client_action_click_button : "left"; //left|right|middle
	$command['action']['click']['count'] = ($client_action_click_count != "") ? $client_action_click_count : 1; //single or double click	
	$command['action']['click']['x']=$client_action_click_x;
	$command['action']['click']['y']=$client_action_click_y;		
}

if($client_action_keystroke == "keystroke"){
	
	if($client_action_keystroke_value =="") die('error 005, send value harus ada karakter ');
		
	$command['action']['keystroke']['value']=$client_action_keystroke_value;		
}

if($client_action_screenshot == "screenshot"){
	
	backup_file_if_exists($config['client']['pathfile']['screenshot']);
	
	$command['action']['screenshot']=true;
	$command['action']['screenshot_delay']=$client_action_screenshot_delay;
}

// CREATE FILE COMMAND
create_file($config['client']['pathfile']['command'],'w',serialize($command));

// WAITING FOR OUTPUT

if(isset($command['client']['login']) || isset($command['action'])){
	if(!file_exists_loop($config['client']['pathfile']['confirm'],60)) 
		die("error 007. file confirm tidak ditemukan dalam waktu 60 detik");
	else {
		$server_data = unserialize(html_entity_decode(file_get_contents($config['client']['pathfile']['confirm'])));
		unlink($config['client']['pathfile']['confirm']);	
		
		// request job
		if(isset($server_data['request_job'])){
			
			if(isset($server_data['request_job']['create_cookie'])){
				setcookie($server_data['variable']['cookie_name'], $server_data['variable']['cookie_value'], time()+ (7*24*60*60));  /* expire while browser close */
			}		
			if(isset($server_data['request_job']['print'])){
				echo $server_data['variable']['print_value'];
				// exit;
			}		
			if(isset($server_data['request_job']['redirect'])){
				header("Location: " . create_url($server_data['variable']['redirect_url']));
				exit;
			}		
		}
		
		// result
		if($server_data['result'] == 'failed'){
			die($server_data['message']);
		}
		
		if(isset($client_action_screenshot)){
			if(!file_exists_loop($config['client']['pathfile']['screenshot'],60)) die("error 006. file screenshot tidak ditemukan dalam waktu 60 detik");
			else chmod($config['client']['pathfile']['screenshot'],0644);// change
		}
	}
}


// start print html
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// play cookie
if(isset($cookie_screenshot_autoload)) 	$client_action_screenshot = $cookie_screenshot_autoload ? "screenshot" : "";
$mouse_left = isset($cookie_mouse_left) ? $cookie_mouse_left : "";
$mouse_right = isset($cookie_mouse_right) ? $cookie_mouse_right : "";
$client_action_screenshot_delay = isset($cookie_screenshot_autoload_delay) ? $cookie_screenshot_autoload_delay : "";

// if($cookie_mouse_left) { echo "ada cookie";exit;}
// if($cookie_mouse_right) { echo "ada cookie";exit;}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body, form{
	margin:0;
	padding:0;
}
.navigation-wrapper{
	width:300px;
	float:left;
	background:red;
	overflow:scroll;
}
.navigation{
	background:#ccc;
}
.content-wrapper{
	float:left;
}
.content{
	background:red;
}
.result{
	width:100%;
}
.iframeresult{
	background:#fff;
	width:100%;
	border:none;
	min-height:500px;
	margin:0;
	padding:0;
}
</style>

<style type="text/css">

body, form { margin: 0px; padding: 0px; }
#contextMenu {
    background-color:threedface;
    border:1px solid #999999;
    position:absolute;
    display:none;
    width:216px;
}
table{
	width:100%;
	border:0;	
}
td{
	padding:2px;
	border:0;	
}
td a {
    border:0 none;
    color:#000000;
    cursor:default;
    display:block;
    font-family:Tahoma;
    font-size:13px;
    padding:3px 0 3px 16px;
    text-decoration:none;
	height:19px;
}
td a:hover {
    background:none repeat scroll 0 0 #EEF2F6;
    border:1px solid #AECFF7;
    border-radius:2px 2px 2px 2px;
    padding:2px 0 2px 15px;
}
hr{
    border:1px inset;
    height:2px;
    margin:0 5px;
    padding:0;
}
</style>


<script type="text/javascript">
// var about="http://4umi.com/web/javascript/contextmenu.php";
// var rmenu = new Array(
// "Right Click","gocontextmenu('right')",
// "Double Click","gocontextmenu('double')",
// "Confirm","if(confirm('Are you sure?'))alert('Good.');",
// "Prompt","q=prompt('What would you like to see?');"+
           // "alert(q.toUpperCase())",
// "hr",
// "About","na=window.open('','','width=400,height=160,"+
          // "left=200,top=120');"+
          // "na.document.open();na.document.write(about);"+
          // "na.document.close();"
// );


//jika ingin ganti line, pisahkan dengan tanda plus
var rmenu = new Array(
"Right Click","gocontextmenu('right')",
"Double Click","gocontextmenu('double')",
"Click then Send Keystroke","gocontextmenu('ckey')",
"Click","gocontextmenu('left')",
"hr",
"Show Coordinat","gocontextmenu('showxy')"
);
//tinggi semua menu diatas = 155px
function gocontextmenu(option){
	if (option == "left") {
		document.name_client_form.client_action_click_button.value = option;
		document.name_client_form.submit();
	}
	else if (option == "right") {
		document.name_client_form.client_action_click_button.value = option;
		document.name_client_form.submit();
	}
	else if (option == "double") {
		document.name_client_form.client_action_click_count.value = 2;
		document.name_client_form.submit();
	}
	else if (option == "showxy") {
		alert('x,y = ' + document.name_client_form.client_action_click_x.value + ',' + document.name_client_form.client_action_click_y.value)
	}
	else if (option == "ckey") {
		q=prompt('Type Keystroke:');
		if (q != null && q != '') {
			document.name_client_form.client_action_keystroke.value = 'keystroke';
			document.name_client_form.client_action_keystroke_value.value = q;
			document.name_client_form.submit();
		}
	}
	else return false;
}

function writemenu(){
var txt = "<div id=\"contextMenu\">"+
            "<table border=\"0\" cellpadding=\"0\" "+
            "cellspacing=\"0\">";
for(var i=0;i<rmenu.length; i+=2){
if(rmenu[i]=="hr") {
      txt+="<tr><td><hr width='95%'><\/td><\/tr>";
      i--;
} else {
      txt+="<tr>"+
            "<td>"+
             "<a href=\"#\" "+
                "onclick=\"" + rmenu[i+1] + ";return false\" "+
                "class=\"menoff\" "+
                "onmouseover=\""+
                 "this.className='menon';"+
       //"window.status=this.onclick.toString().substring(21);"+
                 "return true\" "+
                "onmouseout=\""+
                 "this.className='menoff';"+
       //"status='';"+
                 "return true\">"+
              rmenu[i]+
              "<\/a>"+
             "<\/td>"+
            "<\/tr>";
}
} document.write(txt+"<\/table><\/div>");
}

function alertCoord(e) {

  if( !e ) {
    if( window.event ) {
      //Internet Explorer 8-
      e = window.event;
    } else {
      //total failure, we have no way of referencing the event
      return;
    }
  }
  
  if( typeof( e.pageX ) == 'number' ) {
    //most browsers
    var xcoord = e.pageX;
    var ycoord = e.pageY;
  } else if( typeof( e.clientX ) == 'number' ) {
    //Internet Explorer 8- and older browsers
    //other browsers provide this, but follow the pageX/Y branch
    var xcoord = e.clientX;
    var ycoord = e.clientY;
    var badOldBrowser = ( window.navigator.userAgent.indexOf( 'Opera' ) + 1 ) ||
     ( window.ScriptEngine && ScriptEngine().indexOf( 'InScript' ) + 1 ) ||
     ( navigator.vendor == 'KDE' );
    if( !badOldBrowser ) {
      if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //IE 4, 5 & 6 (in non-standards compliant mode)
        xcoord += document.body.scrollLeft;
        ycoord += document.body.scrollTop;
      } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE 6 (in standards compliant mode)
        xcoord += document.documentElement.scrollLeft;
        ycoord += document.documentElement.scrollTop;
      }
    }
  } else {
    //total failure, we have no way of obtaining the mouse coordinates
    return;
  }
  
  xcoord -= 5;
  ycoord += 5;
  
  if(e.which == 1)   {
	document.name_client_form.client_action_click_x.value = xcoord;
	document.name_client_form.client_action_click_y.value = ycoord;
	<?php 
		if($mouse_left == "left") echo "gocontextmenu('left')";
		if($mouse_left == "right") echo "gocontextmenu('right')";
		if($mouse_left == "double") echo "gocontextmenu('double')";
		if($mouse_left == "showxy") echo "gocontextmenu('showxy')";
		if($mouse_left == "ckey") echo "gocontextmenu('ckey')";	
		if($mouse_left == "cmenu") echo "
			document.getElementById('contextMenu').style.left = xcoord - 5;	
			document.getElementById('contextMenu').style.top = ycoord + 5;
			document.getElementById('contextMenu').style.display = 'block';
		";	
	?>
  }
  else if(e.which == 3) {
	document.name_client_form.client_action_click_x.value = xcoord;
	document.name_client_form.client_action_click_y.value = ycoord;
	<?php 
		if($mouse_right == "left") echo "gocontextmenu('left')";
		if($mouse_right == "right") echo "gocontextmenu('right')";
		if($mouse_right == "double") echo "gocontextmenu('double')";
		if($mouse_right == "showxy") echo "gocontextmenu('showxy')";
		if($mouse_right == "ckey") echo "gocontextmenu('ckey')";	
		if($mouse_right == "cmenu") echo "
			document.getElementById('contextMenu').style.left = xcoord + 'px';
			document.getElementById('contextMenu').style.top = ycoord + 'px';
			document.getElementById('contextMenu').style.display = 'block';
		";	
	?>
	
	
  }
}

</script>
</head>
<body>
<form name="name_client_form" method="get" id="id_client_form" action="client.php">
<input type="hidden" name="client_action_click" value="click">
<input type="hidden" name="client_action_click_x" value="">
<input type="hidden" name="client_action_click_y" value="">
<input type="hidden" name="client_action_click_mode" value="">
<input type="hidden" name="client_action_click_button" value="">
<input type="hidden" name="client_action_click_count" value="">
<input type="hidden" name="client_action_keystroke" value="">
<input type="hidden" name="client_action_keystroke_value" value="">
<input type="hidden" name="client_action_screenshot" value="<?php echo $client_action_screenshot;?>">
<input type="hidden" name="client_action_screenshot_delay" value="<?php echo $client_action_screenshot_delay;?>">
</form>
<img <?php if($mouse_right != "off") echo "oncontextmenu=\"return false;\""?> onmouseup="alertCoord(arguments[0]);" id="id_client_screenshot" name="name_client_screenshot" src="screenshot.php?time=<?php echo time();?>">
<script type="text/javascript">
writemenu();
</script>
</body>
</html>	
