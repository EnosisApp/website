<?php

session_start();

require_once 'vendor/autoload.php';

use Libs\App;

if(!isset($_SESSION['user'])) {
	Libs\App::Login();
} else {
	if(isset($_GET['p']) && $_GET['p'] == "logout")
		App::Logout();

	echo App::Form();
}
