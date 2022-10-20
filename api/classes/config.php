<?php
$server = $_SERVER['SERVER_NAME'];
if (($server == 'localhost')) {
	$url_site = 'http://localhost/sistema-jn2/';

	define('DB_HOST', 'localhost');
	define('DB_NAME', 'sistemajn2');
	define('DB_USER', 'root');
	define('DB_PASS', '');
}
