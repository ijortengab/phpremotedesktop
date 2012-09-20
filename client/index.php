<?php

foreach ($_GET as $key => $val) $$key=$val;
foreach ($_POST as $key => $val) $$key=$val;
foreach ($_COOKIE as $key => $val) $$key=$val;
foreach ($_FILES as $key => $val) $$key=$val;

// cek login and logout
if(isset($client_action_logout)) {
	setcookie("cookie_session", "");
	unset($cookie_session);
}
if(!isset($cookie_session)) {
	echo '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><script type="text/javascript">function alerto(){if(document.getElementById(\'tempe\')){document.getElementById(\'tempe\').disabled = \'disabled\';document.getElementById(\'tempe\').value = \'Wait for 60 seconds\'} }</script></head><body>';
	echo '<form action="client.php" method="post" name="formpassword" id="formpassword" onsubmit="alerto()">Password: <input type="password" name="client_action_password_value"><input type="submit" id="tempe" value="Login"></form>';
	echo '<br/><a id="show" style="cursor:pointer;" onclick="javascript:document.formpassword.client_action_password_value.type=\'text\';javascript:document.getElementById(\'hide\').style.display=\'inline\';javascript:this.style.display=\'none\';">[Show Password]</a>';
	echo '<a id="hide" style="cursor:pointer;display:none;" onclick="javascript:document.formpassword.client_action_password_value.type=\'password\';javascript:document.getElementById(\'show\').style.display=\'inline\';javascript:this.style.display=\'none\';">[Hide Password]</a>';
	echo '</body></html>';
	exit;
}

// cek cookie above post dan get
if(isset($cookie_screenshot_autoload)){
	$formsettingscreenshot_client_setting_screenshot_autoload_value = $cookie_screenshot_autoload ? 0: 1;
	$formsettingscreenshot_submit_value = $cookie_screenshot_autoload ? 1: 0;
}
else{
	setcookie("cookie_screenshot_autoload", 1, time()+ (7*24*60*60));  /* expire in 1 week */	
	$formsettingscreenshot_client_setting_screenshot_autoload_value = 0;
	$formsettingscreenshot_submit_value = 1;
}
if(isset($cookie_screenshot_autoload_delay)){
	$formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value = $cookie_screenshot_autoload_delay;
}
else{
	setcookie("cookie_screenshot_autoload_delay", 2000, time()+ (7*24*60*60));  /* expire in 1 week */
	$formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value = 2000;
}
if(isset($cookie_mouse_left)){
	$formsettingmouse_client_setting_mouse_left_value = $cookie_mouse_left;
}
else{
	setcookie("cookie_mouse_left", "left", time()+ (7*24*60*60));  /* expire in 1 week */
	$formsettingmouse_client_setting_mouse_left_value = "left";
}
if(isset($cookie_mouse_right)){
	$formsettingmouse_client_setting_mouse_right_value = $cookie_mouse_right;
}
else{
	setcookie("cookie_mouse_right", "cmenu", time()+ (7*24*60*60));  /* expire in 1 week */
	$formsettingmouse_client_setting_mouse_right_value = "cmenu";
}

// POST coming
if(isset($client_setting_screenshot_autoload)) {
	setcookie("cookie_screenshot_autoload", $client_setting_screenshot_autoload, time()+ (7*24*60*60));  /* expire in 1 week */	
	$formsettingscreenshot_client_setting_screenshot_autoload_value = $client_setting_screenshot_autoload ? 0: 1;
	$formsettingscreenshot_submit_value = $client_setting_screenshot_autoload ? 1: 0;
} // 
if(isset($client_setting_screenshot_autoload_delay)) {
	setcookie("cookie_screenshot_autoload_delay", $client_setting_screenshot_autoload_delay, time()+ (7*24*60*60));  /* expire in 1 week */	
	$formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value = $client_setting_screenshot_autoload_delay;	
} // 
if(isset($client_setting_mouse_left)) {
	setcookie("cookie_mouse_left", $client_setting_mouse_left, time()+ (7*24*60*60));  /* expire in 1 week */
	$formsettingmouse_client_setting_mouse_left_value = $client_setting_mouse_left;
} // 
if(isset($client_setting_mouse_right)) {
	setcookie("cookie_mouse_right", $client_setting_mouse_right, time()+ (7*24*60*60));  /* expire in 1 week */
	$formsettingmouse_client_setting_mouse_right_value = $client_setting_mouse_right;
} // 

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style type="text/css">
		body{
			margin:0;
			padding:0;
			background:#F1F1F1;
		}
		.navigation-wrapper{
			width:250px;
			float:left;
			background:#ccc;
			height:600px;
		}
		.navigation{
			background:#fff;
			padding:5px;
			margin-right:5px;
		}
		.content-wrapper{
			float:left;
			width:750px;
		}
		.content{
		}
		.iframeresult{
			background:#fff;
			width:750px;
			border:none;
			height:600px;
			margin:0;
			padding:0;
		}
	</style>
	

<script type="text/javascript">	
	function get_browser_window_size(mode) {
		// sumber: http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
		var myWidth = 0, myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) {
			//Non-IE
			myWidth = window.innerWidth;
			myHeight = window.innerHeight;
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
			myHeight = document.documentElement.clientHeight;
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
			//IE 4 compatible
			myWidth = document.body.clientWidth;
			myHeight = document.body.clientHeight;
		}
		if(mode == "w") return myWidth;
		if(mode == "h") return myHeight;
	}
	var padding = 20;
	var tinggilayarbrowser = get_browser_window_size('h') - padding;
	var lebarlayarnavigasi = 250 + padding;
	var lebarlayariframeresult = get_browser_window_size('w') - lebarlayarnavigasi;
	document.write('<style>');
	document.write('.navigation-wrapper{height:'+ tinggilayarbrowser +'px;}');
	document.write('.content-wrapper{width:'+ lebarlayariframeresult +'px;}');
	document.write('.iframeresult{height:'+ tinggilayarbrowser +'px;}');
	document.write('.iframeresult{width:'+ lebarlayariframeresult +'px;}');
	document.write('</style>');
</script>

</head>
<body>

<div class="navigation-wrapper"><div class="navigation">
<form name="formlogout" method="post" action="index.php">
<input type="submit" name ="client_action_logout" value="LOGOUT">
</form>	

<h3>Action to Server</h3>
	<fieldset><legend>ScreenShot:</legend>
	<form name="formscreenshot" method="get" action="client.php" target="frame_content">
	<input type="hidden" name ="client_action_screenshot" value="screenshot">
	<input type="hidden" name ="client_action_screenshot_delay" value="<?php echo $formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value; ?>">
	<input type="submit" name ="submit" value="Reload">
	</form>
	</fieldset>
	
	<fieldset><legend>Keystroke:</legend>
	<form name="formkeystroke" method="get" action="client.php" target="frame_content">	
	<input type="text" style="width:90%;" size="20" value="" name="client_action_keystroke_value"><br>
	<input type="hidden" name ="client_action_keystroke" value="keystroke">	
	<?php	
	if($formsettingscreenshot_submit_value) {
		echo "<input type=\"hidden\" name=\"client_action_screenshot\" value=\"screenshot\">";
		echo "<input type=\"hidden\" name=\"client_action_screenshot_delay\" value=\"$formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value\">";
	}
	?>
	<input type="submit" name ="submit" value="Send">
	</form>
	</fieldset>	
	
	<fieldset><legend>Mouse:</legend>
	<form name="formclick" method="get" action="client.php" target="frame_content">	
	X = <input type="text" size="3" value="200" name="client_action_click_x">
	Y = <input type="text" size="3" value="200" name="client_action_click_y"><br>
	<select name="client_action_click_count">
	<option value="1" selected>Single</option>
	<option value="2">Double</option>
	</select>	
	<select name="client_action_click_button">
	<option value="left" selected>Left</option>
	<option value="right">Right</option>
	</select>
	<input type="hidden" name ="client_action_click" value="click">
	<?php	
	if($formsettingscreenshot_submit_value) {
		echo "<input type=\"hidden\" name=\"client_action_screenshot\" value=\"screenshot\">";
		echo "<input type=\"hidden\" name=\"client_action_screenshot_delay\" value=\"$formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value\">";
	}
	?>
	<input type="submit" name ="submit" value="Click">
	</form>
	</fieldset>
<h3>Settings:</h3>
	<fieldset><legend>ScreenShot:</legend>
	<form name="formsettingscreenshot" method="post" action="index.php">
	<input type="hidden" name ="client_setting_screenshot_autoload" value="<?php echo $formsettingscreenshot_client_setting_screenshot_autoload_value; ?>">
	Autoload Screenshot <abbr style="cursor:help;" onclick="alert(this.title)" title="Reload a new Screenshot after click or send keystroke">(?)</abbr>: <input type="submit" title="click to change" name ="submit" value="<?php echo $formsettingscreenshot_submit_value ? " on " : " off "; ?>">
	</form>	
	
	<?php	
	if($formsettingscreenshot_submit_value) {
	echo "<form name=\"formsettingscreenshotdelay\" method=\"post\" action=\"index.php\">
	After <select name=\"client_setting_screenshot_autoload_delay\" onchange=\"submit()\">";
	echo "<option value=\"250\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 250) ? "selected" : ""; echo ">1/4</option> ";
	echo "<option value=\"500\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 500) ? "selected" : ""; echo ">1/2</option> ";
	echo "<option value=\"1000\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 1000) ? "selected" : ""; echo ">1</option> ";
	echo "<option value=\"2000\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 2000) ? "selected" : ""; echo ">2</option> ";
	echo "<option value=\"3000\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 3000) ? "selected" : ""; echo ">3</option> ";
	echo "<option value=\"4000\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 4000) ? "selected" : ""; echo ">4</option> ";
	echo "<option value=\"5000\" "; echo ($formsettingscreenshotdelay_client_setting_screenshot_autoload_delay_value == 5000) ? "selected" : ""; echo ">5</option> ";
	echo "<select> seconds. ";
	echo "<script type=\"text/javascript\">document.write('<div style=\"display:none;\">');</script>";
	echo "<input type=\"submit\" value=\"Save\">";
	echo "<script type=\"text/javascript\">document.write('</div>');</script>";
	echo "</form> ";
	}
	?>
	</fieldset>
	<fieldset><legend>Mouse:</legend>
	<form name="formsettingmouse" method="post" action="index.php">
	Click: 
	<select name="client_setting_mouse_left" onchange="submit()">
	<optgroup label="Action to Server">
	<option value="left" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "left") ? "selected" : ""; ?>>Click</option>
	<option value="right" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "right") ? "selected" : ""; ?>>Right Click</option>
	<option value="double" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "double") ? "selected" : ""; ?>>Double Click</option>
	<option value="ckey" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "ckey") ? "selected" : ""; ?>>Click then Send Keystroke</option>
	</optgroup>
	<option value="cmenu" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "cmenu") ? "selected" : ""; ?>>Context Menu</option>
	<option value="showxy" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "showxy") ? "selected" : ""; ?>>Show Coordinat</option>	
	<option value="off" <?php echo ($formsettingmouse_client_setting_mouse_left_value == "off") ? "selected" : ""; ?>>Default</option>
	</select>
	
	Right Click: 
	<select name="client_setting_mouse_right" onchange=submit()>
	<optgroup label="Action to Server">
	<option value="left" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "left") ? "selected" : ""; ?>>Click</option>
	<option value="right" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "right") ? "selected" : ""; ?>>Right Click</option>
	<option value="double" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "double") ? "selected" : ""; ?>>Double Click</option>
	<option value="ckey" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "ckey") ? "selected" : ""; ?>>Click then Send Keystroke</option>
	</optgroup>
	<option value="cmenu" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "cmenu") ? "selected" : ""; ?>>Context Menu</option>
	<option value="showxy" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "showxy") ? "selected" : ""; ?>>Show Coordinat</option>	
	<option value="off" <?php echo ($formsettingmouse_client_setting_mouse_right_value == "off") ? "selected" : ""; ?>>Default</option>
	</select>
	
	<script type="text/javascript">document.write('<div style="display:none;">');</script>
	<input type="submit" value="Save">
	<script type="text/javascript">document.write('</div>');</script>	
	
	</form>
	
	</fieldset>
	
	
	
	
	
</div></div>
<div class="content-wrapper"><div class="content">
<iframe class="iframeresult" src="client.php" name="frame_content"></iframe>
</div></div>
</body>

</html>