<?php
	/*ini_set("display_errors", 0);
	error_reporting(0);*/

	$base_path		= "http://192.168.1.41/markaz/web/identification/";
	$db_host		= "localhost";
	$db_name		= "markaz_abidjan";
	$db_user		= "phpmyadmin";
	$db_pass		= "root";
	// $base_path		= "http://backoffice.drdiallo.net/markaz/web/identification/";
	// $db_host		= "localhost";
	// $db_name		= "markaz";
	// $db_user		= "isi-manager";
	// $db_pass		= "isi-manager";
	$time_limit_reg = "15";
	$time_limit_ver = "10";
	//$link = mysqli_connect("hostname", "username", "password", "database");
	$conn = mysqli_connect($db_host, $db_user, $db_pass);
	if (!$conn) die("Connection for user $db_user refused!");
	mysqli_select_db($conn, $db_name) or die("Can not connect to database!");
?>