<?php
//absolute no cache use php plus variable ? time()
header('Content-type: image/jpg');
if(file_exists('screenshot.jpg')) print file_get_contents('screenshot.jpg');
elseif(file_exists('screenshot~.jpg')) print file_get_contents('screenshot~.jpg');
// pe er buat image dgn php bertuliskan sesuatu =>else die('error file screenshot tidak ada sama sekali');